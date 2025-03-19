<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Token;
use App\Models\SuperUser;
use App\Models\MgmtStudent;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class MgmtAdmissionController extends Controller
{
    public function checkSelfSchedule($inst_code = null)
    {
        return response()->json([
            'error' =>  false,
            'schedule_status' => config_schedule('SELF_ADMISSION')['status']
        ], 200);
    }

    public function checkManagementSchedule($inst_code = null)
    {
        return response()->json([
            'error' =>  false,
            'schedule_status' => config_schedule('MANAGEMENT_ADMISSION')['status']
        ], 200);
    }

    public function selfAdmissionSubmit(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $validator = Validator::make($request->all(), [
                    'fullname' => ['required'],
                    'phone' => ['required'],
                    'email' => ['required'],
                    'dob' => ['required', 'date'],
                    'gender' => ['required'],
                    'stud_aadhar' => ['required'],
                    'stud_caste' => ['required'],
                    'father_name' => ['required'],
                    'mother_name' => ['required'],
                    'stud_pic' => ['nullable'],
                    'stud_aadhar_doc' => ['nullable'],
                    'stud_x_marksheet' => ['nullable'],
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   => $validator->errors()
                    ], 422);
                } else {
                    $user_id = $token_check->t_user_id;
                    $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                    $inst_code = $user_data->u_inst_code;
                    $appl_num   =   $request->appl_num;
                    $category   =   $request->category;
                    $a_category = "a_{$category}";
                    $trade_code =   'PHARM';

                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');
                        if (in_array('self-admission-save', $url_data)) {
                            try {
                                $enc_aadhaar_num = encryptHEXFormat($request->stud_aadhar);
                                $check_student = MgmtStudent::where('s_aadhar_no', $enc_aadhaar_num)->first();

                                if ($check_student != null) {
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'The student data already exists'
                                    ], 400);
                                }

                                $last_data = MgmtStudent::select('s_id')->orderBy('s_id', 'DESC')->first();
                                $last_id = $last_data ? (int)($last_data->s_id + 1) : 1;

                                $created_student = MgmtStudent::create([
                                    's_id' => $last_id,
                                    's_candidate_name' =>  strtoupper($request->fullname),
                                    's_father_name' => strtoupper($request->father_name),
                                    's_mother_name' => strtoupper($request->mother_name),
                                    's_dob' => $request->dob,
                                    's_aadhar_no' => $enc_aadhaar_num,
                                    's_phone' => $request->phone,
                                    's_email' => strtolower($request->email),
                                    's_gender' => $request->gender,
                                    's_religion' => $request->stud_religion,
                                    's_caste' => $request->stud_caste,
                                    's_gen_rank' => $request->stud_rank,
                                    's_inst_code' => $inst_code,
                                    'created_at' => $now
                                ]);

                                $application_num = generateManagementApplicationNumber($created_student->s_id);

                                $created_student->update([
                                    's_appl_form_num'       =>  $application_num,
                                ]);

                                $seat = DB::table('management_seat_master')
                                    ->select(DB::raw("'$category' as category"))
                                    ->addSelect(DB::raw("$category as seat_count"))
                                    ->where('sm_inst_code', $inst_code)
                                    ->where('sm_trade_code', $trade_code)
                                    ->first();

                                if ($seat->seat_count > 0) {
                                    $affectedRows = DB::table('management_seat_master')
                                        ->where('sm_inst_code', $inst_code)
                                        ->where('sm_trade_code', $trade_code)
                                        ->update([
                                            $category => DB::raw("$category - 1"),
                                            $a_category => DB::raw("$a_category + 1")
                                        ]);

                                    if ($affectedRows) {
                                        MgmtStudent::where('s_appl_form_num', $appl_num)->update([
                                            's_trade_code'      =>  $trade_code,
                                            's_inst_code'       =>  $inst_code,
                                            's_admitted_status' =>  1,
                                            'updated_at'        =>  $now
                                        ]);
                                        return response()->json([
                                            'error'     =>  false,
                                            'message'   =>  'Seat allotted successfully',
                                            'application_num'  =>  $appl_num,
                                            'inst_code'        =>  $inst_code,
                                            'trade_code'       =>  $trade_code,
                                        ], 200);
                                    } else {
                                        return response()->json([
                                            'error'     =>  true,
                                            'message'   =>  'Seat allotment failed'
                                        ], 400);
                                    }
                                } else {
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Seat not available'
                                    ], 400);
                                }
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

    //get list of stream based on college code
    public function getStreamSeat(Request $request, $college_code)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $user_role = $request->role_id;

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_role)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('institute-wise-stream', $url_data)) { //check url has permission or not
                        $results = DB::table('management_seat_master')
                            ->join('trade_master', 'trade_master.t_code', '=', 'management_seat_master.sm_trade_code')
                            ->select(
                                'sm_inst_code as inst_code',
                                'sm_trade_code as trade_code',
                                't_name as trade_name',
                                DB::raw("'orphan' as category"),
                                'orphan as seat_count'
                            )
                            ->where('sm_inst_code', $college_code)
                            ->where('orphan', '<>', 0)
                            ->unionAll(
                                DB::table('management_seat_master')
                                    ->join('trade_master', 'trade_master.t_code', '=', 'management_seat_master.sm_trade_code')
                                    ->select(
                                        'sm_inst_code as inst_code',
                                        'sm_trade_code as trade_code',
                                        't_name as trade_name',
                                        DB::raw("'mgmt' as category"),
                                        'mgmt as seat_count'
                                    )
                                    ->where('sm_inst_code', $college_code)
                                    ->where('mgmt', '<>', 0)
                            )
                            ->get();


                        if (sizeof($results) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Stream found',
                                'count'     =>   sizeof($results),
                                'tradeList'   =>  $results
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No data found'
                            );
                            return response(json_encode($reponse), 200);
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

    //allocate stream to the candidate
    public function allocateSeat(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $today  =   date('Y-m-d');
            $time   =   date('H:i:s');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not

                $validator = Validator::make($request->all(), [
                    'appl_num'      =>  'required',
                    'category'      =>  'required',
                    'inst_code'     =>  'required',
                    'trade_code'    =>  'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  'Validation failed, enter the required information.' //$validator->messages()
                    ], 400);
                } else {
                    $appl_num   =   $request->appl_num;
                    $category   =   $request->category;
                    $a_category =   'a_' . $category;
                    $inst_code  =   $request->inst_code;
                    $trade_code =   $request->trade_code;

                    $user_id = $token_check->t_user_id;
                    $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                    if (sizeof($role_url_access_id) > 0) {
                        $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                        $url_data = array_column($urls, 'url_name');

                        if (in_array('self-admission-save', $url_data)) { //check url has permission
                            DB::beginTransaction();

                            try {
                                //check duplicate
                                $check_student = MgmtStudent::where('s_appl_form_num', $appl_num)->where('s_inst_code', $inst_code)->whereNotNull('s_trade_code')->first();

                                if ($check_student != null) {
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'The student already alloted seat'
                                    ], 400);
                                }

                                $seat = DB::table('management_seat_master')
                                    ->select(DB::raw("'$category' as category"))
                                    ->addSelect(DB::raw("$category as seat_count"))
                                    ->where('sm_inst_code', $inst_code)
                                    ->where('sm_trade_code', $trade_code)
                                    ->first();

                                if ($seat->seat_count > 0) {
                                    //update seat matrix
                                    $affectedRows = DB::table('management_seat_master')
                                        ->where('sm_inst_code', $inst_code)
                                        ->where('sm_trade_code', $trade_code)
                                        ->update([
                                            $category => DB::raw("$category - 1"),
                                            $a_category => DB::raw("$a_category + 1")
                                        ]);

                                    if ($affectedRows) {
                                        //update studenbt master
                                        MgmtStudent::where('s_appl_form_num', $appl_num)->update([
                                            's_trade_code'      =>  $trade_code,
                                            's_inst_code'       =>  $inst_code,
                                            's_admitted_status' =>  1,
                                            'updated_at'        =>  $now
                                        ]);


                                        DB::commit();
                                        return response()->json([
                                            'error'     =>  false,
                                            'message'   =>  'Seat allotted successfully',
                                            'application_num'  =>  $appl_num,
                                            'inst_code'        =>  $inst_code,
                                            'trade_code'       =>  $trade_code,
                                        ], 200);
                                    } else {
                                        DB::rollback();
                                        return response()->json([
                                            'error'     =>  true,
                                            'message'   =>  'Seat allotment failed'
                                        ], 400);
                                    }
                                } else {
                                    DB::rollback();
                                    return response()->json([
                                        'error'     =>  true,
                                        'message'   =>  'Seat allotment failed due to non availability of seat'
                                    ], 400);
                                }
                            } catch (Exception $e) {
                                DB::rollback();
                                return response()->json([
                                    'error'     =>  true,
                                    'message'   =>  'Server error' //$e->getMessage()
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

    //get all self admission getAllSelfAdmission
    public function selfAdmissionList(Request $request, $inst_code)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $today  =   date('Y-m-d');
            $time   =   date('H:i:s');

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
                            $candidates = DB::table('pharmacy_management_register_student')
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
                                    's_pic as picture',
                                    's_gen_rank as general_rank',
                                    's_trade_code as trade_code',
                                    't_name as trade_name',
                                    's_inst_code as institute_code',
                                    'i_name as institute_name',
                                    'i_type as institute_type',
                                    's_admitted_status as admitted_status',
                                    's_remarks as remarks'
                                )
                                ->leftJoin('trade_master', 't_code', '=', 's_trade_code')
                                ->leftJoin('institute_master', 'i_code', '=', 's_inst_code')
                                ->where('s_inst_code', $inst_code)
                                ->get();

                            return response()->json([
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'count'         =>  sizeof($candidates),
                                'candidate'     =>  $candidates
                            ]);
                        } catch (Exception $e) {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  $e->getMessage(),
                                // 'message'   =>  'Server error',
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

    public function selfAdmissionPdf(Request $request, $appl_num)
    {

        try {
            $candidates = DB::table('pharmacy_management_register_student')
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
                    's_pic as picture',
                    's_gen_rank as general_rank',
                    's_trade_code as trade_code',
                    't_name as trade_name',
                    's_inst_code as institute_code',
                    'i_name as institute_name',
                    'i_type as institute_type',
                    's_admitted_status as admitted_status',
                    's_remarks as remarks'
                )
                ->leftJoin('trade_master', 't_code', '=', 's_trade_code')
                ->leftJoin('institute_master', 'i_code', '=', 's_inst_code')
                ->where('s_appl_form_num', $appl_num)
                ->first();

            //return $candidates[0]->institute_name;
            $pdf = Pdf::loadView('exports.selfadmission', [
                'students' => $candidates,
            ]);
            return $pdf->setPaper('a4', 'portrait')
                ->setOption(['defaultFont' => 'sans-serif'])
                ->stream('self-admission-students.pdf');
        } catch (Exception $e) {
            generateLaravelLog($e);
            return response()->json([
                'error'     =>  true,
                'message'   =>   'Server error'
            ], 400);
        }
    }
}
