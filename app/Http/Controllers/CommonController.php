<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Token;
use App\Models\Trade;
use App\Models\District;
use App\Models\Institute;
use App\Models\SuperUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StudentChoice;
use App\Models\RegisterStudent;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\TradeResource;
use App\Http\Resources\DistrictResource;
use App\Models\AlotedAdmittedSeatMaster;
use App\Http\Resources\InstAdminResource;
use App\Http\Resources\InstituteResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\AllotmentStudentResource;
use App\Models\Test;
use App\Models\Schedule;
use App\Models\PharmacyRegisterStudent;
use App\Models\PharmacyEligiblity;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\EligibilityResource;
use DateTime;
use App\Models\PharmacyAppl_ElgbExam;
use Illuminate\Support\Facades\Crypt;




class CommonController extends Controller
{
    public function allDistricts(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('district-list', $url_data)) { //check url has permission or not

                        $district_list = District::orderBy('d_sort_order', 'ASC')->get();


                        if (sizeof($district_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'District found',
                                'count'     =>   sizeof($district_list),
                                'districts'  =>  DistrictResource::collection($district_list)
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No district available'
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

    //Institute List
    public function allInstList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $stream    =   $request->stream;
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_role = $request->role_id;
                if (!empty($user_role)) {
                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_role)->pluck('rp_url_id');
                } else {
                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');
                }

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('institute-stream-wise', $url_data)) { //check url has permission or not
                        $inst_res = null;
                        $res = null;

                        if ($user_role == 2) {   //if student
                            $inst_list = DB::table('alloted_admitted_seat_master as sm')
                                ->join('institute_master as im', 'im.i_code', '=', 'sm.sm_inst_code')
                                ->select([
                                    'im.i_id as institute_id',
                                    'sm.sm_inst_code as institute_code',
                                    'im.i_name as institute_name',
                                    'im.i_type as institute_type',
                                ])
                                ->distinct()
                                ->where('im.is_active', 1);
                            //->whereRaw('(sqogen + sqosc + sqost + sqpwd + tfw) > 0');

                            if (!empty($stream)) {
                                $inst_list->whereIn('i_code', $request->inst_codes);
                            }
                            $inst_res = $inst_list->orderBy('i_name', 'ASC')->get();
                            $res = $inst_res;
                        } else {
                            if (!empty($stream)) {
                                $inst_codes = DB::table('seat_master')->where('sm_trade_code', $stream)->pluck('sm_inst_code');
                            }

                            $inst_list = Institute::where('is_active',  1);

                            if (!empty($stream)) {
                                $inst_list->whereIn('i_code', $inst_codes);
                            }
                            $inst_res = $inst_list->orderBy('i_name', 'ASC')->get();
                            $res = InstituteResource::collection($inst_res);
                        }

                        if (sizeof($inst_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Institute found',
                                'count'     =>   sizeof($inst_res),
                                'instituteList'   =>  $res
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

    //Stream List
    public function streamList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('trade-list', $url_data)) { //check url has permission or not
                        $trade_res = null;
                        $trade_list = Trade::where('is_active',  1);
                        $trade_res = $trade_list->orderBy('t_name', 'ASC')->get();


                        if (sizeof($trade_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Stream found',
                                'count'     =>   sizeof($trade_res),
                                'tradeList'   =>  TradeResource::collection($trade_res)
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

    //Stream List Inst wise
    public function streamListinstListWise(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_role = $request->role_id;

                if (!empty($user_role)) {
                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_role)->pluck('rp_url_id');
                } else {
                    $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');
                }

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('institute-wise-stream', $url_data)) { //check url has permission or not

                        $validated = Validator::make($request->all(), [
                            'inst_code' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $inst_code    =   $request->inst_code;
                        $res = null;

                        if ($user_role == 2) {   //if student
                            $trade_res = DB::table('alloted_admitted_seat_master as sm')
                                ->join('trade_master as tm', 'tm.t_code', '=', 'sm.sm_trade_code')
                                ->select([
                                    'tm.t_id as trade_id',
                                    'sm.sm_trade_code as trade_code',
                                    'tm.t_name as trade_name',
                                ])
                                ->where('sm.sm_inst_code', $inst_code)
                                //->whereRaw('(sqogen + sqosc + sqost + sqpwd + tfw) > 0')
                                ->whereRaw('(sqogen + sqosc + sqost + tfw) > 0')
                                ->orderBy('tm.t_name', 'asc')
                                ->get();

                            $res =  $trade_res;
                        } else {
                            $trade_codes = DB::table('seat_master')->where('sm_inst_code', $inst_code)->pluck('sm_trade_code');
                            $trade_res = null;
                            $trade_list = Trade::where('is_active',  1)->whereIn('t_code', $trade_codes);
                            $trade_res = $trade_list->orderBy('t_name', 'ASC')->get();

                            $res = TradeResource::collection($trade_res);
                        }

                        if (sizeof($trade_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Stream found',
                                'count'     =>   sizeof($trade_res),
                                'tradeList'   =>  $res
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

    //Inst or Admin wise alloted students
    public function allAllotedStudents(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('alloted-students', $url_data)) { //check url has permission or not

                        $trade = $request->trade_code;
                        $phone = $request->student_phone;
                        $inst_code = $request->inst_code;
                        $student_name = Str::upper($request->student_name);

                        if ($user_data->u_role_id == 3) { // INST Admin
                            $allotedStudentLists = User::select('s_id', 's_candidate_name', 's_phone', 's_gender', 's_alloted_category', 's_trade_code', 's_inst_code', 's_alloted_round')->with(['institute:i_code,i_name'])->where(['is_alloted' => 1, 's_inst_code' => $user_data->u_inst_code]); //'is_upgrade' => 0,

                            // if (!empty($trade)) {
                            //     $allotedStudentLists->where('s_trade_code', $trade);
                            // }
                            if (!empty($phone)) {
                                $allotedStudentLists->where('s_phone', $phone);
                            }
                            if (!empty($student_name)) {
                                //$allotedStudentLists->where('s_candidate_name', $student_name);
                                $allotedStudentLists->where('s_candidate_name', 'like', '%' . $student_name . '%');
                            }
                            $allotedStudents = $allotedStudentLists->orderBy('s_candidate_name', 'ASC')->get();
                            //return $allotedStudents;
                        } else { // Super Admin
                            $allotedStudentLists = User::select('s_id', 's_candidate_name', 's_phone', 's_gender', 's_alloted_category', 's_trade_code', 's_inst_code', 's_alloted_round')->with(['institute:i_code,i_name'])->where(['is_alloted' => 1]); //'is_upgrade' => 0,
                            if (!empty($inst_code)) {
                                $allotedStudentLists->where('s_inst_code', $inst_code);
                            }
                            // if (!empty($trade)) {
                            //     $allotedStudentLists->where('s_trade_code', $trade);
                            // }
                            if (!empty($phone)) {
                                $allotedStudentLists->where('s_phone', $phone);
                            }
                            if (!empty($student_name)) {
                                //$allotedStudentLists->where('s_candidate_name', $student_name);
                                $allotedStudentLists->where('s_candidate_name', 'like', '%' . $student_name . '%');
                            }
                            $allotedStudents = $allotedStudentLists->orderBy('s_candidate_name', 'ASC')->get();
                            //return $allotedStudents;
                        }

                        if (sizeof($allotedStudents) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Alloted list found',
                                'count'     =>   sizeof($allotedStudents),
                                'allotedList'   =>  AllotmentStudentResource::collection($allotedStudents),
                                'excel_name' => 'alloted_students_lists'
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

    //Student Allotment Details
    public function StudentallotementDetails(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('alloted-students', $url_data)) {

                        $validated = Validator::make($request->all(), [
                            'student_id' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $student_id = $request->student_id;
                        $studentData = User::where('s_id', $student_id)->first();
                        $choiceData = StudentChoice::where('ch_stu_id', $student_id)->where('is_alloted', 1)->first();
                        $institute = Institute::where('i_code', $choiceData->ch_inst_code)->first();
                        $choice = $choiceData->ch_pref_no;

                        $rankArr = array('s_gen_rank', 's_sc_rank', 's_st_rank', 's_obca_rank', 's_obcb_rank', 's_pwd_rank');
                        $rank_data = [];
                        $userRank = $studentData;
                        $schedule_admission = false;
                        $check_admission = config_schedule('ADMISSION');
                        $check_admission_status = $check_admission['status'];
                        if ($check_admission_status == true) {
                            $schedule_admission = true;
                        }
                        foreach ($rankArr as $val) {
                            $userRankData = (int)$userRank[$val];
                            if (!is_null($userRankData) && ($userRankData != 0)) {

                                $cat = explode('_', $val);
                                array_push(
                                    $rank_data,
                                    [
                                        'category' => casteValue(Str::upper($cat[1])),
                                        'rank' => $userRankData
                                    ]
                                );
                            }
                        }

                        $registration_payment = PaymentTransaction::where([
                            'pmnt_stud_id' => $student_id,
                            'trans_status' => 'SUCCESS',
                            'pmnt_pay_type' => 'COUNSELLINGREGISTRATIONFEES',
                        ])->first();

                        if ($studentData->s_admited_status && !$studentData->is_registration_payment) {
                            $fees_status = 'ACCEPTED BUT NOT PAID';
                        } else if ($studentData->s_admited_status && $studentData->is_registration_payment) {
                            $fees_status = 'PAID';
                        } else if ($studentData->is_registration_payment && $studentData->is_registration_verified) {
                            $fees_status = 'VERIFIED';
                        } else if (!$studentData->s_admited_status) {
                            $fees_status = 'NOT ADMITTED TILL NOW';
                        }

                        $user = [
                            'institute_name' =>  $institute->i_name,
                            'allotement_category' =>  !empty($choiceData->ch_alloted_category) ? casteValue(Str::upper($choiceData->ch_alloted_category)) : "N/A",
                            'allotement_round' =>  !empty($choiceData->ch_alloted_round) ? $choiceData->ch_alloted_round : "N/A",
                            'choice_option' =>  $choice,
                            'allotment_accepted' => (bool)$studentData->is_allotment_accept,
                            'admitted_accepted' => ($studentData->s_admited_status == 1) ? true : false,
                            'admission_rejected' => ($studentData->s_admited_status == 2) ? true : false,
                            'index_num' => $studentData->s_index_num,
                            'appl_form_num' => $studentData->s_appl_form_num,
                            'phone' => $studentData->s_phone,
                            'gender' => $studentData->s_gender,
                            'category' => $studentData->s_caste,
                            'full_name' => $studentData->s_candidate_name,
                            'physically_challenged' => $studentData->s_pwd,
                            'rank' => $rank_data,
                            'schedule_admission' => $schedule_admission,
                            'verified' => (bool)$studentData->is_registration_verified,
                            'registration_payment' => [
                                'fees_status' => $fees_status,
                                'status' => $studentData->s_admited_status && $studentData->is_registration_payment,
                                'trans_id' => $registration_payment?->trans_id,
                                'amount' => $registration_payment?->trans_amount,
                                'trans_date' => $registration_payment?->trans_time,
                            ]
                        ];

                        if ($studentData) {
                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  'Student alloted details found',
                                'details'   =>  $user
                            ]);
                        } else {
                            return response()->json([
                                'error'     =>  true,
                                'message'   =>  'No data found'
                            ]);
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

    //Student allotment/take admission accept or reject
    public function studentAdmissionVerification(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('student-admission', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            $validated = Validator::make($request->all(), [
                                'student_id' => ['required'],
                                'status' => ['required'],
                            ]);

                            if ($validated->fails()) {
                                return response()->json([
                                    'error' => true,
                                    'message' => $validated->errors()
                                ]);
                            }
                            $student_id =   $request->student_id;
                            $status     =   $request->status;
                            $remarks    =   isset($request->remarks) ? $request->remarks : null;

                            $student = DB::table('pharmacy_register_student')
                                ->join('institute_master', 'i_code', '=', 's_inst_code')
                                //->join('trade_master', 't_code', '=', 's_trade_code')
                                ->select(
                                    's_candidate_name as candidate_name',
                                    's_inst_code as institute_code',
                                    'i_name as institute_name',
                                    //'s_trade_code as trade_code',
                                    //'t_name as trade_name',
                                    's_alloted_category as allotment_category',
                                    's_admited_status'
                                )
                                ->where('s_id', $student_id)
                                ->first();

                            //return $student;

                            $allotment_inst_code    =   $student->institute_code;
                            $allotment_trade_code   =   '';
                            $allotment_category     =   $student->allotment_category;
                            $candidate_name         =   $student->candidate_name;
                            $institute_name         =   $student->institute_name;
                            $trade_name             =   '';
                            $admited_status         = $student->s_admited_status;

                            //dd($allotment_category);
                            // AlotedAdmittedSeatMaster::where('sm_inst_code', $allotment_inst_code)
                            // ->where('sm_trade_code', $allotment_trade_code)
                            // ->update([
                            //     'a_' . $allotment_category  =>  intval('a_' . $allotment_category) + 1
                            // ]);

                            if ($status == 1) { //Approved , 'sm_trade_code' => $allotment_trade_code
                                $seatData = AlotedAdmittedSeatMaster::where(['sm_inst_code' => $allotment_inst_code])->first();

                                $admitedQuotaIntake = (int)$seatData["a_{$allotment_category}"];
                                $actualSeatIntake = (int)$seatData["m_{$allotment_category}"];

                                User::where('s_id', $student_id)->update([
                                    's_admited_status'  =>  1,
                                    's_remarks'         =>  'Approved',
                                    'updated_at'        =>  now()
                                ]);

                                $seatData->update([
                                    'a_' . $allotment_category  =>  $admitedQuotaIntake + 1,
                                    $allotment_category  =>  $actualSeatIntake - 1,
                                ]);

                                studentActivite($student_id, "{$candidate_name} has been admitted successfully at {$institute_name} on {$trade_name}");

                                auditTrail($user_id, "{$candidate_name} has been admitted successfully at {$institute_name} on {$trade_name}");

                                DB::commit();
                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  "Admission confirmed"
                                ], 200);
                            } else if ($status == 0) { //Reject
                                User::where('s_id', $student_id)->update([
                                    's_admited_status'  =>  2,
                                    's_remarks'         =>  $remarks,
                                    's_rejected_by'     =>  $user_id,
                                    'updated_at'        =>  now()
                                ]);
                                //Seat cancellation
                                if ($admited_status == '1') {
                                    $inst_type = $request->inst_type;
                                    $seatData = AlotedAdmittedSeatMaster::where(['sm_inst_code' => $allotment_inst_code])->first();
                                    $admitedQuotaNormalIntake = (int)$seatData["{$allotment_category}"];
                                    $admitedQuotaAdmitedIntake = (int)$seatData["a_{$allotment_category}"];

                                    $seatData->update([
                                        "{$allotment_category}"  =>  $admitedQuotaNormalIntake + 1,
                                        "a_{$allotment_category}" => $admitedQuotaAdmitedIntake - 1
                                    ]);
                                }
                                /*
                                if($inst_type == 'PVT'){
                                    $seatDataSpot =  DB::table('alloted_admitted_seat_master_pvt')->where(['sm_inst_code' => $allotment_inst_code, 'sm_trade_code' => $allotment_trade_code])->first();

                                    $admitedQuotaRevert = (int)$seatDataSpot["{$allotment_category}"];
                                    $admitedQuotaMasterIntake = (int)$seatDataSpot["m_{$allotment_category}"];

                                    $seatDataSpot->update([
                                        "{$allotment_category}" => $admitedQuotaRevert + 1,
                                        "m_{$allotment_category}" => $admitedQuotaMasterIntake + 1
                                    ]);

                                }else{//GOVT
                                    //Spot seat master add rejected seat
                                    $seatDataSpot =  SpotSeatMaster::where(['sm_inst_code' => $allotment_inst_code, 'sm_trade_code' => $allotment_trade_code])->first();
                                    $swap_category_arr = swapCatArr();

                                    $allotment_category = isset($swap_category_arr[$allotment_category]) ?
                                    $swap_category_arr[$allotment_category] : $allotment_category;

                                    // if(array_key_exists($allotment_category,$swap_category_arr)){
                                    //     $allotment_category = $swap_category_arr["{$allotment_category}"];
                                    // }else{
                                    //     $allotment_category = $allotment_category;
                                    // }

                                    //dd($allotment_category);

                                    $admitedQuotaRevert = (int)$seatDataSpot["{$allotment_category}"];
                                    $admitedQuotaMasterIntake = (int)$seatDataSpot["m_{$allotment_category}"];

                                    $seatDataSpot->update([
                                        "{$allotment_category}" => $admitedQuotaRevert + 1,
                                        "m_{$allotment_category}" => $admitedQuotaMasterIntake + 1
                                    ]);

                                }*/

                                studentActivite($student_id, "{$candidate_name} has been rejected for the choice {$institute_name} and {$trade_name}");

                                auditTrail($user_id, "{$candidate_name} has been rejected for the choice {$institute_name} and {$trade_name}");

                                DB::commit();
                                return response()->json([
                                    'error'     =>  false,
                                    'message'   =>  "Admission rejected"
                                ], 200);
                            }

                            /*
                            $studentData = User::where('s_id', $student_id)->first();
                            $institute = Institute::where('i_code', $studentData->s_inst_code)->first();
                            $branch = Trade::where('t_code', $studentData->s_trade_code)->first();

                            $seatData = AlotedAdmittedSeatMaster::where(['sm_inst_code' => $studentData->s_inst_code, 'sm_trade_code' => $studentData->s_trade_code])->first();
                            //return $seatData;
                            if ($studentData) {
                                //dd($old_pref_no);
                                if ($seatData) {
                                    $masterIntake = (int)$seatData["m_{$studentData->s_alloted_category}"];
                                    $admitedSeat = 1;
                                    $afteradmissionmasterIntake = $masterIntake - 1;

                                    $admitedQuotaIntake = (int)$seatData["a_{$studentData->s_alloted_category}"];

                                    $inst_name = $institute->i_name;
                                    $trade_name = $branch->t_name;

                                    if ($status == 1) { //Approved
                                        //dd('h');
                                        $upDateStatus = 1;
                                        $st = 'Approved';
                                        $remarks = NULL;

                                        $studentData->update([
                                            'updated_at' => now(),
                                            's_admited_status' => $upDateStatus,
                                            's_remarks' => $remarks
                                        ]);

                                        $student_name = $studentData->s_candidate_name;
                                        auditTrail($user_id, "{$student_name} has been admited successfully at {$inst_name} on this trade {$trade_name}");

                                        studentActivite($student_id, "{$student_name} has been admited successfully at {$inst_name} on this trade {$trade_name}");

                                        DB::commit();
                                        return response()->json([
                                            'error'             =>  false,
                                            'message'              =>  "Admited successfully"
                                        ], 200);
                                    } else { //Rejected
                                        // dd('hi');
                                        $upDateStatus = 2;
                                        $st = 'Rejected';
                                        $remarks = $request->remarks;
                                        //dd($remarks);

                                        $studentData->update([
                                            'updated_at' => now(),
                                            's_admited_status' => $upDateStatus,
                                            's_remarks' => $remarks,
                                            's_rejected_by' => $user_id
                                        ]);

                                        $student_name = $studentData->s_candidate_name;
                                        auditTrail($user_id, "{$student_name} allotment has been rejected at {$inst_name} on this trade {$trade_name}");

                                        studentActivite($student_id, "{$student_name} has been rejected at {$inst_name} on this trade {$trade_name} for this {$remarks}");

                                        DB::commit();
                                        return response()->json([
                                            'error'             =>  true,
                                            'message'              =>  "Admission rejected"
                                        ], 200);
                                    }
                                } else {
                                    return response()->json([
                                        'error'             =>  true,
                                        'message'              =>  "No seat avilable"
                                    ], 200);
                                }
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Wrong student"
                                ], 200);
                            } */
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);
                            return response()->json(
                                array(
                                    'error' => true,
                                    'code' =>    'INT_00001',
                                    'message' => $e->getMessage()
                                )
                            );
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

    public function verifyRegistration(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('student-admission', $url_data)) {
                        try {
                            $validated = Validator::make($request->all(), [
                                'student_id' => ['required'],
                                'student_name' => ['required'],
                                'institute_name' => ['required'],
                            ]);

                            if ($validated->fails()) {
                                return response()->json([
                                    'error' => true,
                                    'message' => $validated->errors()
                                ]);
                            }

                            $student = RegisterStudent::where([
                                's_id' => $request->student_id,
                                's_admited_status' => 1,
                            ])->first();

                            if ($student) {
                                $student->update([
                                    'is_registration_verified' => 1,
                                    'registration_verified_at' => now()
                                ]);

                                studentActivite($request->student_id, "Registration of {$request->student_name} has been varified {$request->institute_name}");

                                auditTrail($user_id, "Registration of {$request->student_name} has been varified {$request->institute_name}");

                                return response()->json([
                                    'error' => false,
                                    'message' => 'Verified Successfully'
                                ]);
                            } else {
                                return response()->json([
                                    'error' => true,
                                    'message' => 'Student Not Found'
                                ]);
                            }
                        } catch (Exception $e) {
                            DB::rollBack();
                            generateLaravelLog($e);

                            return response()->json([
                                'error' => true,
                                'message' => $e->getMessage()
                            ]);
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

    //All admitted students lists with filter
    public function allAdmittedStudents(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('admitted-students', $url_data)) { //check url has permission or not

                        $trade = $request->trade_code;
                        $phone = $request->student_phone;
                        $inst_code = $request->inst_code;
                        $student_name = Str::upper($request->student_name);
                        if ($user_data->u_role_id == 3) { // INST Admin
                            $admittedStudentLists = User::select('s_id', 's_candidate_name', 's_phone', 's_gender', 's_religion', 's_caste', 's_alloted_category', 's_trade_code', 's_inst_code', 's_alloted_round', 's_gen_rank')->with(['institute:i_code,i_name,i_type'])->where(['is_alloted' => 1, 's_inst_code' => $user_data->u_inst_code, 's_admited_status' => 1]);

                            // if (!empty($trade)) {
                            //     $admittedStudentLists->where('s_trade_code', $trade);
                            // }
                            if (!empty($phone)) {
                                $admittedStudentLists->where('s_phone', $phone);
                            }
                            if (!empty($student_name)) {
                                $admittedStudentLists->where('s_candidate_name', 'like', '%' . $student_name . '%');
                            }
                            $admittedStudents = $admittedStudentLists->orderBy('s_gen_rank', 'ASC')->get();
                        } else { // Super Admin
                            $admittedStudentLists = User::select('s_id', 's_candidate_name', 's_phone', 's_gender', 's_religion', 's_caste', 's_alloted_category', 's_trade_code', 's_inst_code', 's_alloted_round', 's_gen_rank')->with(['institute:i_code,i_name,i_type'])->where(['is_alloted' => 1, 's_admited_status' => 1]);

                            if (!empty($inst_code)) {
                                $admittedStudentLists->where('s_inst_code', $inst_code);
                            }
                            // if (!empty($trade)) {
                            //     $admittedStudentLists->where('s_trade_code', $trade);
                            // }
                            if (!empty($phone)) {
                                $admittedStudentLists->where('s_phone', $phone);
                            }
                            if (!empty($student_name)) {
                                $admittedStudentLists->where('s_candidate_name', 'like', '%' . $student_name . '%');
                            }
                            $admittedStudents = $admittedStudentLists->orderBy('s_inst_code', 'ASC')->orderBy('s_trade_code', 'ASC')->orderBy('s_gen_rank', 'ASC')->get();
                        }

                        if (sizeof($admittedStudents) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Admitted list found',
                                'count'     =>   sizeof($admittedStudents),
                                'allotedList'   =>  AllotmentStudentResource::collection($admittedStudents),
                                'excel_name' => 'admitted_students_lists'
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

    //Seat matrix with filter
    public function seatMatrix(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('seat-matrix', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'trade_code' => ['required']
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        $trade = $request->trade_code;
                        $inst_code = $request->inst_code;
                        $seatMaster = AlotedAdmittedSeatMaster::query();
                        if (!empty($inst_code)) {
                            $allseats = $seatMaster->where('sm_inst_code', $inst_code);
                        } else {
                            $allseats = $seatMaster->where('sm_inst_code', $user_data->u_inst_code);
                        }
                        $allseats = $seatMaster->where('sm_trade_code', $trade);
                        $allseats = $seatMaster->clone()->first();

                        //dd($allseats);
                        $data = [];
                        $cast = cast();
                        foreach ($cast as $val) {
                            //$val = Str::lower($val);
                            $data[casteValue(Str::upper($val))] =   array(
                                'initial_seats'     =>    $allseats->{"m_" . $val},
                                'alloted_seats'     => ($allseats->{"m_" . $val}) - ($allseats->{$val}),
                                'not_alloted_seats'    =>     $allseats->{$val},
                                'admitted_seats'     =>     $allseats->{"a_" . $val},
                                'available_seats'     => ($allseats->{"m_" . $val}) - ($allseats->{"a_" . $val})
                            );
                        }

                        //return  $data;

                        if (sizeof($data) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'List found',
                                'count'     =>   sizeof($data),
                                'list'   =>  $data,
                                'excel_name' => 'seat_matrix_lists'
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



    //religion list
    public function allReligions(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('district-list', $url_data)) { //check url has permission or not

                        $religion_list = array(
                            'HINDUISM'  =>  'HINDUISM',
                            'ISLAM'  =>  'ISLAM',
                            'CHRISTIANITY'  =>  'CHRISTIANITY',
                            'SIKHISM'  =>  'SIKHISM',
                            'BUDDHISM'  =>  'BUDDHISM',
                            'JAINISM'  =>  'JAINISM',
                            'OTHER'  =>  'OTHER',
                        );


                        if (sizeof($religion_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'State found',
                                'count'     =>   sizeof($religion_list),
                                'religions'  =>  $religion_list
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No Religion available'
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

    //caste list
    public function allCastes(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');
                    if (in_array('district-list', $url_data)) { //check url has permission or not

                        $caste_list = array(
                            'GENERAL'  =>  'GENERAL',
                            'SC'  =>  'SC',
                            'ST'  =>  'ST',
                            'OBC-A'  =>  'OBC-A',
                            'OBC-B'  =>  'OBC-B'
                        );

                        if (sizeof($caste_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'State found',
                                'count'     =>   sizeof($caste_list),
                                'castes'  =>  $caste_list
                            );
                            return response(json_encode($reponse), 200);
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No Caste available'
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

    //Count round wise Alloted, Admitted, Rejected
    public function countAllotedAdmittedRejected(Request $request) {}

    //All Inst Admin list
    public function allInstAdminList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                //return $user_data;
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('inst-admin-list', $url_data)) { //check url has permission or not
                        $allAdminList = SuperUser::select('u_id', 'u_inst_code', 'u_inst_name', 'u_username')->where('u_role_id', 3)->where('is_active', 1)->orderBy('u_username', 'ASC')->get();


                        if (sizeof($allAdminList) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'List found',
                                'count'     =>   sizeof($allAdminList),
                                'List'   =>  InstAdminResource::collection($allAdminList)
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
    //AD
    public function fromsubmit(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required'],
                'email' => ['required', 'email'],
                'mobile' => ['required', 'digits:10'],
                'image' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            ]);

            $filePath = $request->file('image')->store('uploads', 'public');
            $test = Test::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'image' => $filePath,
            ]);

            if (!$test) {
                return response()->json(['success' => false, 'message' => 'Failed to insert data.'], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully!',
                'data' => $test
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }
    public function getdata($id)
    {
        if (!is_numeric($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ID provided'
            ], 400);
        }
        $test = Test::find($id);
        if (!$test) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'name' => $test->name,
                'email' => $test->email,
                'mobile' => $test->mobile,
                'image' => URL::to("storage/{$test->image}")
            ]
        ], 200);
    }
    public function registerstudent(Request $request)
    {
        try{
            $request->validate([
                'student_first_name' => ['required'],
                'student_middle_name' => ['nullable'],
                'student_last_name' => ['required'],
                'student_candidate_name' => ['required'],
                'student_father_name' => ['required'],
                'student_mother_name' => ['required'],
                'student_dob' => ['required'],
                'student_aadhar_no' => ['required'],
                'student_phone' => ['required', 'digits:10'],
                'student_email' => ['required', 'email'],
                'student_gender' => ['required'],
                'student_religion' => ['required'],
                'student_caste'=>['required'],
                's_tfw' => ['required'],
                's_ews' => ['required'],
                's_llq' => ['required'],
                's_exsm' => ['required'],
                's_pwd' => ['required'],
                's_gen_rank' => ['required'],
                's_sc_rank' => ['required'],
                's_st_rank' => ['required'],
                's_obca_rank' => ['required'],
                's_obcb_rank' => ['required'],
                's_tfw_rank' => ['required'],
                's_ews_rank' => ['required'],
                's_llq_rank' => ['required'],
                's_exsm_rank' => ['required'],
                's_pwd_rank' => ['required'],
                'student_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'student_sign' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'student_home_dist' => ['required'],
                'student_schooling_dist' => ['required'],
                'student_state_id' => ['required'],
                'student_alloted_category' => ['required'],
                'student_alloted_round' => ['required'],
                'student_choise_id' => ['required'],
                'student_trade_code' => ['required'],
                'student_inst_code' => ['required'],
                'student_eligible_category' => ['required'],
                'student_auto_rejected_round' => ['required'],
                'student_rejected_by' => ['required'],
                'student_address' => ['required'],
                'student_police_station' => ['required'],
                'student_post_office' => ['required'],
                'student_pin_no' => ['required'],
                'is_married' => ['required'],
                'is_kanyashree' => ['required'],
                's_admited_status' => ['required'],
                's_auto_reject' => ['required'],
                's_seat_block' => ['required'],
                'last_round_adm_status' => ['required'],
                'is_profile_updated' => ['required'],
                'is_choice_fill_up' => ['required'],
                'is_lock_manual' => ['required'],
                'is_lock_auto' => ['required'],
                'is_payment' => ['required'],
                'is_choice_downloaded' => ['required'],
                'is_upgrade_payment' => ['required'],
                'is_allotment_accept' => ['required'],
                'is_alloted' => ['required'],
                'is_upgrade' => ['required'],
                's_remarks' => ['required'],
                'is_active' => ['required'],
                's_uuid' => ['required'],
                'is_registration_payment' => ['required'],
                'is_registration_verified' => ['required'],
                'physic_marks'=>['required'],
                'chemistry_marks' => ['required'],
                'biology_marks' => ['required'],
                'mathematics_marks' => ['required'],
                'exam_elgb_code'=>['required']
            ]);
            $currentDateTime = date('Y-m-d H:i:s');
            $schedule = Schedule::where('sch_event', 'APPLICATION')
                ->where('sch_round', 1)
                ->first();
            if (!$schedule || $currentDateTime < $schedule->sch_start_dt || $currentDateTime > $schedule->sch_end_dt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your date is expired. You can no longer submit the form.'
                ], 400);
            }
            $year = date('Y');
            $lastStudent = PharmacyRegisterStudent::latest('s_id')->first();
            if ($lastStudent && preg_match('/PHARMA' . $year . '(\d+)/', $lastStudent->s_appl_form_num, $matches)) {
                $lastNumber = (int) $matches[1];
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            // Generate the new application form number
            $s_appl_form_num = 'PHARMA' . $year . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            if (PharmacyRegisterStudent::where('s_phone', $request->student_phone)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This mobile number is already registered.'
                ], 409);
            }
    
            if (PharmacyRegisterStudent::where('s_email', $request->student_email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered.'
                ], 409);
            }
            $dob = new DateTime($request->student_dob);
            $currentYear = date('Y');
            $requiredDate = new DateTime("31-12-$currentYear");
            $age = $dob->diff($requiredDate)->y;
            if ($age < 17) {
                return response()->json([
                    'success' => false,
                    'message' => 'The candidate must be at least 17 years old on or before 31st December of this year.'
                ], 400);
            }
            $fullAadhar = $request->student_aadhar_no;
            $firstPart = substr($fullAadhar, 0, -4);   
            $last4 = substr($fullAadhar, -4); 
            $encryptedPart = base64_encode(openssl_encrypt($firstPart, 'aes-256-cbc', env('APP_KEY'), 0, substr(env('APP_KEY'), 0, 16)));
            $maskedAadhar = $encryptedPart . $last4;

            $aadharexists = PharmacyRegisterStudent::where('s_aadhar_original', $request->student_aadhar_no)->exists();
            if ($aadharexists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Aadhaar number is already registered.'
                ], 409);
            }
            $student_photo = $request->file('student_photo')->store('uploads', 'public');
            $student_sign = $request->file('student_sign')->store('uploads', 'public');
            $register = PharmacyRegisterStudent::create([
                's_appl_form_num'=>$s_appl_form_num,
                's_first_name'=>$request->student_first_name,
                's_middle_name'=>$request->student_middle_name,
                's_last_name'=>$request->student_last_name,
                's_candidate_name'=>$request->student_candidate_name,
                's_father_name'=>$request->student_father_name,
                's_mother_name'=>$request->student_mother_name,
                's_dob'=>$request->student_dob,
                's_aadhar_no'=> $maskedAadhar,
                's_aadhar_original'=>$fullAadhar,
                's_phone'=>$request->student_phone,
                's_email'=>$request->student_email,
                's_gender'=>$request->student_gender,
                's_religion'=>$request->student_religion,
                's_caste'=>$request->student_caste,
                's_tfw'=>$request->s_tfw,
                's_ews'=>$request->s_ews,
                's_llq_rank'=>$request->s_llq_rank,
                's_llq'=>$request->s_llq,
                's_exsm'=>$request->s_exsm,
                's_pwd'=>$request->s_pwd,
                's_gen_rank'=>$request->s_gen_rank,
                's_sc_rank'=>$request->s_sc_rank,
                's_st_rank'=>$request->s_st_rank,
                's_obca_rank'=>$request->s_obca_rank,
                's_obcb_rank'=>$request->s_obcb_rank,
                's_tfw_rank'=>$request->s_tfw_rank,
                's_ews_rank'=>$request->s_ews_rank,
                's_exsm_rank'=>$request->s_exsm_rank,
                's_pwd_rank'=>$request->s_pwd_rank,
                's_photo' => $student_photo,
                's_sign' => $student_sign,
                's_home_district'=>$request->student_home_dist,
                's_schooling_district'=>$request->student_schooling_dist,
                's_state_id'=>$request->student_state_id,
                's_alloted_category'=>$request->student_alloted_category,
                's_alloted_round'=>$request->student_alloted_round,
                's_choice_id'=>$request->student_choise_id,
                's_trade_code'=>$request->student_trade_code,
                's_inst_code'=>$request->student_inst_code,
                's_eligible_category'=>$request->student_eligible_category,
                's_auto_reject_round'=>$request->student_auto_rejected_round,
                's_rejected_by'=>$request->student_rejected_by,
                'address'=>$request->student_address,
                'ps'=>$request->student_police_station,
                'po'=>$request->student_post_office,
                'pin'=>$request->student_pin_no,
                'is_married'=>$request->is_married,
                'is_kanyashree'=>$request->is_kanyashree,
                's_admited_status'=>$request->s_admited_status,
                's_auto_reject'=>$request->s_auto_reject,
                's_seat_block'=>$request->s_seat_block,
                'last_round_adm_status'=>$request->last_round_adm_status,
                'is_profile_updated'=>$request->is_profile_updated,
                'is_choice_fill_up'=>$request->is_choice_fill_up,
                'is_lock_manual'=>$request->is_lock_manual,
                'is_lock_auto'=>$request->is_lock_auto,
                'is_payment'=>$request->is_payment,
                'is_choice_downloaded'=>$request->is_choice_downloaded,
                'is_upgrade_payment'=>$request->is_upgrade_payment,
                'is_allotment_accept'=>$request->is_allotment_accept,
                'is_alloted'=>$request->is_alloted,
                'is_upgrade'=>$request->is_upgrade,
                's_remarks'=>$request->s_remarks,
                'is_active'=>$request->is_active,
                's_uuid'=>$request->s_uuid,
                'is_registration_payment'=>$request->is_registration_payment,
                'is_registration_verified'=>$request->is_registration_verified,
                'physic_marks'=>$request->physic_marks,
                'chemistry_marks'=>$request->chemistry_marks,
                'biology_marks'=>$request->biology_marks,
                'mathematics_marks'=>$request->mathematics_marks, 
            ]);

            if (!$register) {
                return response()->json(['success' => false, 'message' => 'Failed to insert data.'], 500);
            }   
            
            $eligibility = PharmacyAppl_ElgbExam::create([
                'exam_appl_form_num' => $s_appl_form_num,
                'exam_elgb_code' => $request->exam_elgb_code
            ]);

            if (!$eligibility) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to insert eligibility data.'], 500);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully!',
                'data' => [
                    'student' => $register,
                    'eligibility' => $eligibility
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
        

    public function eligibility(Request $request)
    {
        $course_code=$request->course_code;
        $eligible_list=PharmacyEligiblity::where('course_code',$course_code)->where('is_active','1')->get();
        if (sizeof($eligible_list) > 0) {
            $reponse = array(
                'error'     =>  false,
                'message'   =>  'Data found',
                'count'     =>   sizeof($eligible_list),
                'eligibilities'    =>  EligibilityResource::collection($eligible_list)
            );
            return response(json_encode($reponse), 200);
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'No data available'
            );
            return response(json_encode($reponse), 404);
        }


    }
    

    

}
