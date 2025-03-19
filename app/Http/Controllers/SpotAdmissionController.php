<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Otp;
use App\Models\Token;
use App\Models\GovtSeat;
use App\Models\Institute;
use App\Models\SuperUser;
use App\Models\SpotStudent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SpotAllotment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class SpotAdmissionController extends Controller
{
    public function spotAthenticate(Request $request)
    {
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $validated = Validator::make($request->all(), [
            'user_phone' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $spot_admission_sschedule = config_schedule('SPOT_ADMISSION')['status'];

        if (!$spot_admission_sschedule) {
            return response()->json([
                'error' => true,
                'message' => "Spot Admission didn't start yet"
            ]);
        }

        $login_phone = $request->user_phone;

        $student = SpotStudent::updateOrCreate([
            's_phone' => $login_phone,
        ], [
            'is_active' => 1
        ]);

        $student_phone = $student->phone;

        $otp_res = Otp::where('username', $student_phone)->first();

        $otp_code = Config::get('app.env') === 'production' ? rand(1111, 9999) : 1234;
        $student_phone = $student->s_phone;
        $sms_message_user = "{$otp_code} is your One Time Password (OTP). Don't share this with anyone. - WBSCTE&VE&SD";

        if ($otp_res) {
            $last_otp_date = substr(trim($otp_res->otp_created_on), 0, 10);

            if ($last_otp_date == $today) {
                $minutes = getTimeDiffInMinute($now, $otp_res->otp_created_on);

                if ($otp_res->otp_count < 9) {
                    if ($minutes > 2) {
                        send_sms($student_phone, $sms_message_user);

                        $otp_res->update([
                            'username' => $student_phone,
                            'otp' => $otp_code,
                            'otp_created_on' => $now,
                            'otp_count' => intval($otp_res->otp_count) + 1
                        ]);

                        $otp_send = true;
                    } else {
                        return response()->json([
                            'error' => true,
                            'message' => "Your previous OTP was generated in last 2 minutes"
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'error' => true,
                        'message' => "You exceed the OTP generation limit for today. Try again tomorrow."
                    ], 200);
                }
            } else {
                send_sms($student_phone, $sms_message_user);

                $otp_res->update([
                    'username' => $student_phone,
                    'otp' => $otp_code,
                    'otp_created_on' => $now,
                    'otp_count' => 1
                ]);

                $otp_send = true;
            }
        } else {
            send_sms($student_phone, $sms_message_user);

            Otp::updateOrCreate([
                'username' => $student_phone,
            ], [
                'otp' => $otp_code,
                'otp_created_on' => $now,
                'otp_count' => 1
            ]);

            $otp_send = true;
        }

        if ($otp_send) {
            $otp_exp_time  = date('Y-m-d H:i:s', strtotime('+120 seconds', strtotime($now)));

            return response()->json([
                'error' => false,
                'message' => 'Otp sent successfully',
                'otp_expire_time' => formatDate($otp_exp_time, 'Y-m-d H:i:s', 'M j, Y H:i:s'),
            ], 200);
        }
    }

    public function spotOtpVerification(Request $request)
    {
        $now = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'user_phone' => ['required'],
            'security_code' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $login_phone = $request->user_phone;
        $login_otp = $request->security_code;

        $otp = Otp::where([
            'username' => $login_phone,
            'otp' => $login_otp
        ])->first();

        if ($otp) {
            DB::beginTransaction();
            try {
                $student = SpotStudent::where([
                    's_phone' => $login_phone,
                    'is_active' => '1'
                ])->with('spotAllotment')->first();

                if ($student) {
                    $token = md5($now . rand(10000000, 99999999));
                    $expiry = date("Y-m-d H:i:s", strtotime('+4 hours', strtotime($now)));

                    Token::updateOrCreate([
                        't_user_id' => $student->s_id,
                    ], [
                        't_token' => $token,
                        't_generated_on' => $now,
                        't_expired_on' => $expiry,
                    ]);

                    $student_name = $student->s_candidate_name;

                    auditTrail($student->s_id, "{$student_name} has been logged in successfully for spot admission");
                    studentActivite($student->s_id, "{$student_name} has been logged in successfully spot admission");

                    $otp->delete();

                    DB::commit();

                    return response()->json([
                        'error'             =>  false,
                        'token'             =>  $token,
                        'token_expired_on'  =>  $expiry,
                        'user' =>  json_encode($this->userData($student)),
                    ], 200);
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  'OTP is Invalid'
                    ], 200);
                }
            } catch (Exception $e) {
                DB::rollBack();
                generateLaravelLog($e);

                return response()->json([
                    'error' => true,
                    'code' => 'INT_00001',
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            return response()->json([
                'error'     =>  true,
                'message'   =>  'Either Phone number or OTP does not match'
            ], 400);
        }
    }

    public function spotCollegeList()
    {
        $list = Institute::select('i_code', 'i_name', 'is_active', 'i_type')
            ->where([
                'i_type' => 'Government',
                'is_active' => 1
            ])->get()
            ->map(function ($data) {
                return [
                    'value' => $data->i_code,
                    'name' => $data->i_name,
                ];
            });

        if (count($list)) {
            return response()->json([
                'error' => true,
                'message' => 'Institution List Found',
                'list' => $list
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No Data Found',
            ]);
        }
    }

    public function spotInfoUpdate(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'id' => ['required'],
                'phone' => ['required'],
                'first_name' => ['required'],
                'middle_name' => ['nullable'],
                'last_name' => ['required'],
                'father_name' => ['required'],
                'mother_name' => ['required'],
                'dob' => ['required', 'before:today'],
                'gender' => ['required'],
                'email' => ['nullable', 'email'],
                'aadhar_no' => ['required', 'digits:12'],
                'religion' => ['required'],
                'caste' => ['required'],
                'college' => ['required'],
            ], [
                'dob.before' => 'DOB is invalid',
                'email.email' => 'Email is invalid',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validated->errors()->first(),
                ]);
            }

            $old_adhar = SpotStudent::where('original_aadhar', $request->aadhar_no)->whereNot('s_id', $request->id)->first();

            if ($old_adhar) {
                return response()->json([
                    'error' => true,
                    'message' => 'Aadhaar Number Already Exists',
                ]);
            }

            if ($request->college === 'CHN' && $request->gender === 'MALE') {
                return response()->json([
                    'error' => true,
                    'message' => 'Only women candidate is eligible for this college',
                ]);
            }

            $full_name = Str::replace('  ', ' ', "{$request->first_name} {$request->middle_name} {$request->last_name}");

            SpotStudent::updateOrCreate([
                's_id' => $request->id,
                's_phone' => $request->phone,
            ], [
                's_first_name' => $request->first_name,
                's_middle_name' => $request->middle_name,
                's_last_name' => $request->last_name,
                's_candidate_name' => $full_name,
                's_father_name' => $request->father_name,
                's_mother_name' => $request->mother_name,
                's_dob' => $request->dob,
                's_email' => $request->email,
                's_gender' => $request->gender,
                'original_aadhar' => $request->aadhar_no,
                's_religion' => $request->religion,
                's_caste' => $request->caste,
                'spot_inst_code' => $request->college
            ]);

            SpotAllotment::updateOrCreate([
                'stu_id' => $request->id,
                'alloted_trade' => 'PHARMACY'
            ], [
                'inst_code' => $request->college,
                'created_at' => now()
            ]);

            auditTrail($request->id, "{$full_name} updated details for spot admission");
            studentActivite($request->id, "{$full_name} updated details for spot admission");

            return response()->json([
                'error' => false,
                'message' => 'Updated Successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function spotStudentData($id, $phone)
    {
        $student = SpotStudent::where([
            's_id' => $id,
            's_phone' => $phone,
            'is_active' => 1,
        ])->with('spotAllotment')->first();

        if ($student) {
            return response()->json([
                'error' => false,
                'message' => 'Data Found',
                'user' => $this->userData($student)
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Data Not Found'
            ]);
        }
    }

    public function spotAdmissionSubmit(Request $request)
    {
        $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['required'],
            'phone' => ['required'],
            'college' => ['required'],
        ]);

        $id = $request->id;
        $phone = $request->phone;
        $college = $request->college;
        $name = $request->name;

        SpotStudent::updateOrCreate([
            's_id' => $id,
            's_phone' => $phone,
        ], [
            'is_spot_alloted' => 1,
        ]);

        SpotAllotment::updateOrCreate([
            'stu_id' => $id,
            'inst_code' => $college,
        ], [
            'alloted_status' => 1,
            'reg_dt' => '2025-02-18 13:11:12',
        ]);

        auditTrail($id, "{$name} updated details for spot admission");
        studentActivite($id, "{$name} updated details for spot admission");

        return response()->json([
            'error' => false,
            'message' => 'Submitted Successfully'
        ]);
    }

    public function spotAdmissionPdf($id, $phone)
    {
        $data = SpotAllotment::where([
            'stu_id' => $id
        ])->with([
            'spotStudent' => function ($query) use ($phone) {
                $query->where('s_phone', $phone)->where('is_active', 1);
            },
            'payment' => function ($query) {
                $query->where('trans_status', 'SUCCESS')
                    ->where('pmnt_pay_type', 'SPOTADMISSIONFEES')
                    ->orWhere('pmnt_pay_type', 'COUNSELLINGFEES');
            },
            'institute' => function ($query) {
                $query->where('i_type', 'Government')->where('is_active', 1);
            }
        ])->first();

        if ($data) {
            $pdf = Pdf::loadView('exports.spot-admission', [
                'institute_name' => optional($data->institute)->i_name ?: 'N/A',
                'student_name' => optional($data->spotStudent)->s_candedate_name ?: 'N/A',
                'dob' => optional($data->spotStudent)->s_dob ?: 'N/A',
                'father_name' => optional($data->spotStudent)->s_father_name ?: 'N/A',
                'mother_name' => optional($data->spotStudent)->s_mother_name ?: 'N/A',
                'phone' => optional($data->spotStudent)->s_phone ?: 'N/A',
                'email' => optional($data->spotStudent)->s_email ?: 'N/A',
                'caste' => optional($data->spotStudent)->s_caste ?: 'N/A',
                'gender' => optional($data->spotStudent)->s_gender ?: 'N/A',
                'trans_amount' => optional($data->payment)->trans_amount ?: 'N/A',
            ]);
            return $pdf->setPaper('a4', 'portrait')
                ->setOption(['defaultFont' => 'sans-serif'])
                ->stream("spot-admission-{$id}.pdf");
        } else {
            return response()->json([
                'error' =>  true,
                'message' => 'Payment not found'
            ]);
        }
    }

    private function userData($student)
    {
        return [
            'id' => $student->s_id,
            'first_name' => $student->s_first_name,
            'middle_name' => $student->s_middle_name,
            'last_name' => $student->s_last_name,
            'full_name' => $student->s_candidate_name,
            'father_name' => $student->s_father_name,
            'mother_name' => $student->s_mother_name,
            'dob' => $student->s_dob,
            'phone' => $student->s_phone,
            'email' => $student->s_email,
            'gender' => $student->s_gender,
            'aadhar_no' => $student->original_aadhar,
            'religion' => $student->s_religion,
            'caste' => $student->s_caste,
            'is_details_updated' => $student->spotAllotment()->exists(),
            'is_spot_paid' => (bool)$student->is_spot_payment,
            'is_spot_admitted' => (bool)$student->is_spot_alloted,
            'spot_inst_code' => optional($student->spotAllotment)->inst_code,
            'spot_status' => $this->spotStatus($student),
            'role_id' => 2,
            'login_type' => 'SPOT',
            'rank' => $student->s_old_rank,
        ];
    }

    private function spotStatus($student)
    {
        if (!$student->spotAllotment()->exists()) {
            return 'DETAILS NOT SUBMITTED';
        } else if ($student->spotAllotment()->exists() && !(bool)$student->is_spot_payment) {
            return 'NOT PAID';
        } else if ((bool)$student->is_spot_payment && !(bool)$student->is_spot_alloted) {
            return 'PAID BUT NOT ADMITTED';
        } else if ((bool)$student->is_spot_alloted) {
            return 'ADMITTED';
        } else if ((bool)$student->is_spot_inst_paid) {
            return 'ACCEPTED';
        }
    }

    public function checkSchedule()
    {
        return response()->json([
            'error' =>  false,
            'schedule_status' => config_schedule('SPOT_ADMISSION_ADMIN')['status'],
            'message' =>  'Data found',
        ], 200);
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
                            $castes = [
                                'gen' => 'GENERAL',
                                'sc' => 'SC',
                                'st' => 'ST',
                                'obca' => 'OBC-A',
                                'obcb' => 'OBC-B',
                            ];

                            $students = spotStudent::where([
                                'spot_inst_code' => $inst_code,
                                'is_spot_alloted' => 1
                            ])->with('spotAllotment:stu_id,inst_code,created_at,reg_dt')
                                ->get()
                                ->map(function ($value) use ($castes) {
                                    return [
                                        'student_id' => $value->s_id,
                                        'candidate_name' => $value->s_candidate_name,
                                        'email' => $value->s_email,
                                        'phone_number' => $value->s_phone,
                                        'gender' => $value->s_gender,
                                        'religion' => $value->s_religion,
                                        'caste' => $value->s_caste,
                                        'alloted_caste' => !is_null($value->spot_alloted_caste) ? $castes[$value->spot_alloted_caste] : null,
                                        'alloted_at' => optional($value->spotAllotment)->reg_dt,
                                        'old_rank' => $value->s_old_rank,
                                        'created_at' => optional($value->spotAllotment)->created_at,
                                        'is_confirmed' => (bool)$value->is_spot_confirmed,
                                        'is_paid' => (bool)$value->is_spot_inst_paid,
                                    ];
                                })->filter(function ($value) {
                                    return isset($value['alloted_at']) && strpos($value['alloted_at'], "2025-02-18") !== false;
                                })
                                ->values();

                            $studentsWithOldRank = $students->filter(function ($item) {
                                return !is_null($item['old_rank']);
                            })->sortBy(['old_rank', 'created_at']);

                            $studentsWithoutOldRank = $students->filter(function ($item) {
                                return is_null($item['old_rank']);
                            });

                            $list = $studentsWithOldRank->concat($studentsWithoutOldRank)->values();

                            // count
                            $seat = GovtSeat::where([
                                "sm_inst_code" => $inst_code,
                                'is_active' => 1
                            ])->first();

                            $message = !(bool)((int)$seat?->gen + (int)$seat?->sc + (int)$seat?->st + (int)$seat?->obca + (int)$seat?->obcb) ? 'No Seat Available' :  null;

                            if (count($list)) {
                                return response()->json([
                                    'error' => false,
                                    'message' => $message,
                                    'candidate' => $list,
                                    'count' => [
                                        'available' => [
                                            'GEN' => (int)$seat?->gen,
                                            'SC' => (int)$seat?->sc,
                                            'ST' => (int)$seat?->st,
                                            'OBCA' => (int)$seat?->obca,
                                            'OBCB' => (int)$seat?->obcb,
                                        ],

                                        'alloted' => [
                                            'GEN' => (int)$seat?->a_gen,
                                            'SC' => (int)$seat?->a_sc,
                                            'ST' => (int)$seat?->a_st,
                                            'OBCA' => (int)$seat?->a_obca,
                                            'OBCB' => (int)$seat?->a_obcb,
                                        ]
                                    ]
                                ]);
                            } else {
                                return response()->json([
                                    'error'         =>  false,
                                    'message'       =>  'No Data found',
                                ]);
                            }
                        } catch (Exception $e) {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  $e->getMessage(),
                                // 'message'   =>  'Try again later',
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

    public function casteList($inst_code, $stu_caste)
    {
        $seat = GovtSeat::select(
            'sm_inst_code',
            'gen',
            'sc',
            'st',
            'obca',
            'obcb',
        )->where('sm_inst_code', $inst_code)
            ->first();

        $castes = [
            'GENERAL' => 'gen',
            'SC' => 'sc',
            'ST' => 'st',
            'OBC-A' => 'obca',
            'OBC-B' => 'obcb'
        ];

        $student_caste = $castes[$stu_caste];

        $gen_list = collect([
            [
                'name' => (bool)$seat->gen ? 'General' : null,
                'value' => $seat->gen ? 'gen' : null,
            ],
        ]);

        $other_list = collect([
            [
                'name' => (bool)$seat->sc ? 'SC' : null,
                'value' => $seat->sc ? 'sc' : null,
            ],
            [
                'name' => (bool)$seat->st ? 'ST' : null,
                'value' => $seat->st ? 'st' : null,
            ],
            [
                'name' => (bool)$seat->obca ? 'OBC-A' : null,
                'value' => $seat->obca ? 'obca' : null,
            ],
            [
                'name' => (bool)$seat->obcb ? 'OBC-B' : null,
                'value' => $seat->obcb ? 'obcb' : null,
            ]
        ]);

        $gen_list = $gen_list->filter(function ($item) {
            return !is_null($item['name'])
                && !is_null($item['value']);
        })->values();

        $other_list = $other_list->filter(function ($item) use ($student_caste) {
            return !is_null($item['name'])
                && !is_null($item['value'])
                && ($item['value'] === $student_caste);
        })->values();

        $list = collect()->mergeRecursive($gen_list)->mergeRecursive($other_list);

        if (count($list)) {
            return response()->json([
                'error' => false,
                'message' => 'Data Found',
                'list' => $list,
            ]);
        } else {
            return response()->json([
                'error' => false,
                'message' => 'No Data Found'
            ]);
        }
    }

    public function accept(Request $request)
    {
        if ($request->header('token')) {
            $now = date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $request->validate([
                    'stu_id' => ['required'],
                    'inst_code' => ['required'],
                    'inst_name' => ['required'],
                    'caste' => ['required'],
                ]);

                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('self-admission-save', $url_data)) {
                        $caste = $request->caste;

                        try {
                            SpotStudent::where([
                                's_id' => $request->stu_id,
                                'spot_inst_code' => $request->inst_code,
                                'is_spot_alloted' => 1,
                            ])->update([
                                'is_spot_confirmed' => 1,
                                'spot_alloted_caste' => $caste,
                                'spot_confirmed_at' => now()
                            ]);

                            SpotAllotment::where([
                                'stu_id' => $request->stu_id,
                                'inst_code' => $request->inst_code,
                                'alloted_status' => 1
                            ])->update([
                                'is_confirmed' => 1,
                                'confirmed_at' => now()
                            ]);

                            $seat = GovtSeat::where([
                                'sm_inst_code' => $request->inst_code
                            ])->first();

                            $seat->update([
                                "{$caste}" => ($seat["m_{$caste}"] != $seat["a_{$caste}"]) ? $seat["{$caste}"] - 1 : $seat["{$caste}"],
                                "a_{$caste}" => ($seat["m_{$caste}"] != $seat["a_{$caste}"]) ? $seat["a_{$caste}"] + 1 : $seat["a_{$caste}"]
                            ]);

                            auditTrail($user_data->u_id, "Spot admission Accepted in {$caste} seat for student id: {$request->stu_id}");
                            studentActivite($request->stu_id, "{$request->inst_name} Accepted spot admission in {$caste} seat");

                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  'Accepted Successfully',
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
}
