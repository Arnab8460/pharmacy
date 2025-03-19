<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Token;
use App\Models\SuperUser;
use App\Models\MgmtStudent;
use App\Models\PrivateSeat;
use Illuminate\Http\Request;
use App\Models\RemovedStudent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SelfAdmissionController extends Controller
{
    public function checkSchedule($inst_code)
    {
        $seat = PrivateSeat::where([
            "sm_inst_code" => $inst_code,
        ])->first();

        $maximum = $seat?->m_gen;
        $available = $seat?->gen;
        $alloted = $seat?->a_gen;

        $schedule_status = config_schedule('SELF_ADMISSION')['status'];
        $message = ($available > 0 && ($alloted <= $maximum)) ? 'Data Found' : 'Seat Not Available';

        return response()->json([
            'error' => (bool)($available > 0 && ($alloted <= $maximum)),
            'schedule_status' => $schedule_status,
            'message' => $schedule_status ?  $message :  'Management Admission Time Out',
            'can_admit' => (bool)$available,
            'count' => [
                'maximum' => $maximum,
                'available' => $available,
                'alloted' => $alloted,
            ]
        ], 200);
    }

    public function submit(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $validator = Validator::make($request->all(), [
                    'fullname' => ['required'],
                    'phone' => ['required'],
                    'email' => ['required'],
                    'dob' => ['required', 'date', 'before:today'],
                    'gender' => ['required'],
                    'stud_aadhar' => ['required', 'digits:12', 'unique:pharmacy_management_register_student,original_aadhar'],
                    'stud_caste' => ['required'],
                    'father_name' => ['required'],
                    'mother_name' => ['required'],
                ], [
                    'fullname.required' => 'Full Name is required',
                    'phone.required' => 'Phone is required',
                    'email.required' => 'Email is required',
                    'dob.required' => 'Date of Birth is required',
                    'dob.date' => 'Date of Birth must be a valid date',
                    'dob.before' => 'Date of Birth must be a valid date',
                    'gender.required' => 'Gender is required',
                    'stud_aadhar.required' => 'Aadhaar is required',
                    'stud_aadhar.digits' => 'Aadhaar must be 12 digits',
                    'stud_aadhar.unique' => 'Aadhaar already exists',
                    'stud_caste.required' => 'Caste is required',
                    'father_name.required' => 'Father Name is required',
                    'mother_name.required' => 'Mother Name is required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   => $validator->errors()->first()
                    ], 422);
                } else {
                    $user_id = $token_check->t_user_id;
                    $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                    $inst_code = $user_data->u_inst_code;

                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');
                        if (in_array('self-admission-save', $url_data)) {
                            try {
                                $enc_aadhaar_num = encryptHEXFormat($request->stud_aadhar);

                                $created_student = MgmtStudent::updateOrCreate([
                                    'original_aadhar' => $request->stud_aadhar,
                                    'seat_type' => 'SELF',
                                ], [
                                    's_aadhar_no' => $enc_aadhaar_num,
                                    's_candidate_name' =>  strtoupper($request->fullname),
                                    's_father_name' => strtoupper($request->father_name),
                                    's_mother_name' => strtoupper($request->mother_name),
                                    's_dob' => $request->dob,
                                    's_phone' => $request->phone,
                                    's_email' => strtolower($request->email),
                                    's_gender' => $request->gender,
                                    's_religion' => $request->stud_religion,
                                    's_caste' => $request->stud_caste,
                                    's_gen_rank' => $request->stud_rank,
                                    's_inst_code' => $inst_code,
                                    's_admitted_status' => 'ADMITTED BUT NOT PAID',
                                    'created_at' => $now
                                ]);

                                $application_num = generateManagementApplicationNumber($created_student->s_id);

                                $created_student->update([
                                    's_appl_form_num' => $application_num
                                ]);

                                $seat = PrivateSeat::where([
                                    'sm_inst_code' => $inst_code
                                ])->first();

                                $seat->update([
                                    'gen' => ($seat->m_gen != $seat->a_gen) ? $seat->gen - 1 : $seat->gen,
                                    'a_gen' => ($seat->m_gen != $seat->a_gen) ? $seat->a_gen + 1 : $seat->a_gen
                                ]);

                                auditTrail($user_data->u_id, "Self admission successful for student id: {$created_student->s_id}");

                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  'Student Admission Successful',
                                ], 200);
                            } catch (Exception $e) {
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  $e->getMessage()
                                ], 400);
                            }
                        } else {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>   "Oops! you don't have sufficient permission"
                            ], 401);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }
    }

    public function list(Request $request, $inst_code)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('self-admission-save', $url_data)) { //check url has permission
                        try {
                            $list = MgmtStudent::where([
                                's_inst_code' => $inst_code,
                                'seat_type' => 'SELF',
                            ])->with('institute')
                                ->get()
                                ->map(function ($value) {
                                    return [
                                        'student_id' => $value->s_id,
                                        'application_form_number' => $value->s_appl_form_num,
                                        'candidate_name' => $value->s_candidate_name,
                                        'email' => $value->s_email,
                                        'phone_number' => $value->s_phone,
                                        'gender' => $value->s_gender,
                                        'religion' => $value->s_religion,
                                        'aadhaar' => $value->original_aadhar,
                                        'dob' => $value->s_dob,
                                        'caste' => $value->s_caste,
                                        'institute_code' => $value->s_inst_code,
                                        'institute_name' => optional($value->institute)->i_name,
                                        'institute_type' => optional($value->institute)->i_type,
                                        'admitted_status' => $value->s_admitted_status,
                                        'is_paid' => (bool)$value->is_admission_payment
                                    ];
                                })->sortBy('student_id')
                                ->values();

                            $seat = PrivateSeat::where([
                                "sm_inst_code" => $inst_code,
                            ])->first();

                            if (count($list)) {
                                return response()->json([
                                    'error'         =>  false,
                                    'message'       =>  'Data found',
                                    'candidate'     =>  $list,
                                    'count' => [
                                        'maximum' => $seat?->m_gen,
                                        'available' => $seat?->gen,
                                        'alloted' => $seat?->a_gen,
                                    ]
                                ]);
                            } else {
                                return response()->json([
                                    'error'         =>  false,
                                    'message'       =>  'No Data found',
                                    'count' => [
                                        'maximum' => $seat?->m_gen,
                                        'available' => $seat?->gen,
                                        'alloted' => $seat?->a_gen,
                                    ]
                                ]);
                            }
                        } catch (Exception $e) {
                            return response()->json([
                                'error'     =>  true,
                                // 'message'   =>  $e->getMessage(),
                                'message'   =>  'Try again later',
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }
    }

    public function remove(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $inst_code = $user_data->u_inst_code;

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('self-admission-save', $url_data)) {
                        try {
                            $request->validate([
                                'appl_no' => ['required'],
                                'student_id' => ['required'],
                            ]);

                            $seat = PrivateSeat::where([
                                'sm_inst_code' => $inst_code
                            ])->first();

                            if ($seat) {
                                $seat->update([
                                    'gen' => $seat->gen + 1,
                                    'a_gen' => $seat->a_gen - 1
                                ]);
                            }

                            $deletable_data = MgmtStudent::where([
                                's_id' => $request->student_id,
                                's_inst_code' => $inst_code,
                                's_appl_form_num' => $request->appl_no,
                                'seat_type' => 'SELF',
                            ])->first();

                            RemovedStudent::create($deletable_data->only(
                                's_appl_form_num',
                                's_candidate_name',
                                's_father_name',
                                's_mother_name',
                                's_dob',
                                's_aadhar_no',
                                's_phone',
                                's_email',
                                's_gender',
                                's_religion',
                                's_caste',
                                's_gen_rank',
                                'is_payment',
                                's_inst_code',
                                's_admitted_status',
                                'seat_type',
                                'is_admission_payment',
                                'original_aadhar',
                            ));

                            $deletable_data->delete();

                            auditTrail($user_data->u_id, "Student Removed from Self Admission of student id: {$request->student_id}");

                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  'Removed Successfully',
                            ], 200);
                        } catch (Exception $e) {
                            return response()->json([
                                'error'     =>  true,
                                // 'message'   =>  'Something went wrong, contact admin for details',
                                'message'   =>  $e->getMessage()
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }
    }

    public function update(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $inst_code = $user_data->u_inst_code;

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('self-admission-save', $url_data)) {
                        try {
                            $validator = Validator::make($request->all(), [
                                'appl_no' => ['required'],
                                'student_id' => ['required'],
                                'fullname' => ['required'],
                                'dob' => [
                                    'required',
                                    'date',
                                    'before:today'
                                ],
                                'gender' => ['required'],
                                'aadhaar' => [
                                    'required',
                                    'digits:12',
                                    Rule::unique('pharmacy_management_register_student', 'original_aadhar')->ignore($request->student_id, 's_id')
                                ],
                            ], [
                                'fullname.required' => 'Full Name is required',
                                'dob.required' => 'Date of Birth is required',
                                'dob.date' => 'Date of Birth must be a valid date',
                                'dob.before' => 'Date of Birth must be a valid date',
                                'gender.required' => 'Gender is required',
                                'aadhaar.required' => 'Aadhaar is required',
                                'aadhaar.digits' => 'Aadhaar must be 12 digits',
                                'aadhaar.unique' => 'Aadhaar Number already exists',
                            ]);

                            if ($validator->fails()) {
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   => $validator->errors()->first()
                                ], 422);
                            }

                            MgmtStudent::where([
                                's_appl_form_num' => $request->appl_no,
                                's_id' => $request->student_id,
                                's_inst_code' => $inst_code,
                                'seat_type' => 'SELF',
                            ])->update([
                                's_candidate_name' =>  strtoupper($request->fullname),
                                's_aadhar_no' => encryptHEXFormat($request->aadhaar),
                                's_dob' => $request->dob,
                                's_gender' => $request->gender,
                                'original_aadhar' => $request->aadhaar,
                                'updated_at' => $now
                            ]);

                            auditTrail($user_data->u_id, "Data Updated for student id: {$request->student_id}");

                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  'Updated Successfully',
                            ], 200);
                        } catch (Exception $e) {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  'Something went wrong, contact admin for details',
                                // 'message'   =>  $e->getMessage()
                            ], 500);
                        }
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "Oops! you don't have sufficient permission"
                    ], 401);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'Unable to process your request due to invalid token'
                ], 401);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Unable to process your request due to non availability of token'
            ], 401);
        }
    }

    public function pdf($appl_num)
    {
        try {
            $candidate = DB::table('pharmacy_management_register_student')
                ->select(
                    's_id as student_id',
                    's_appl_form_num as application_form_number',
                    's_candidate_name as candidate_name',
                    's_father_name as father_name',
                    's_mother_name as mother_name',
                    's_dob as date_of_birth',
                    's_aadhar_no as aadhar_number',
                    's_phone as phone_number',
                    's_email as email',
                    's_gender as gender',
                    's_religion as religion',
                    's_caste as caste',
                    's_gen_rank as general_rank',
                    's_inst_code as institute_code',
                    'i_name as institute_name',
                    'i_type as institute_type',
                    's_admitted_status as admitted_status',
                    's_remarks as remarks'
                )
                ->leftJoin('institute_master', 'i_code', '=', 's_inst_code')
                ->where('s_appl_form_num', $appl_num)
                ->where('seat_type', 'SELF')
                ->first();

            $payment = PaymentTransaction::where('pmnt_stud_id', $candidate->student_id)
                ->where('pmnt_pay_type', 'SELFADMISSIONFEES')
                ->where('trans_status', 'SUCCESS')
                ->first();

            if ($payment) {
                $pdf = Pdf::loadView('exports.self-admission', [
                    'students' => $candidate,
                    'payment' => $payment
                ]);
                return $pdf->setPaper('a4', 'portrait')
                    ->setOption(['defaultFont' => 'sans-serif'])
                    ->stream('self-admission-students.pdf');
            } else {
                return response()->json([
                    'error' =>  true,
                    'message' => 'Payment not found'
                ]);
            }
        } catch (Exception $e) {
            generateLaravelLog($e);
            return response()->json([
                'error' =>  true,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
