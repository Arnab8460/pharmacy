<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Token;
use App\Models\Schedule;
use App\Models\Institute;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StudentChoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use App\Models\RegisterStudent;
use App\Models\StudentActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\StudentChoiceResource;
use App\Http\Resources\StudentActivityResource;

class StudentController extends Controller
{
    public function choiceEntry(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-entry', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            //'trade_code' => ['required'],
                            'inst_code' => ['required'],
                            'choice_id' => ['nullable'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $trade_code = ''; //phermacy trade code static bosate hobe
                            $inst_code = $request->inst_code;
                            $choice_id = $request->choice_id;
                            $old_choice_pref_no = 1;

                            //$tradeData = Trade::where(['t_code' => $trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $inst_code, 'is_active' => 1])->first();

                            $trade_name = '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                            $checkExists = StudentChoice::where('ch_stu_id', $user_id)->where('ch_inst_code', $inst_code)->first();

                            if ($checkExists) {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Allready exists with the same choice, please try other!"
                                ], 200);
                            } else {
                                $choice_cnt = StudentChoice::where('ch_stu_id', $user_id)->count();
                                //return $choice_cnt;
                                $choice_pref_no = $choice_cnt + 1;
                                $student_name = $user_data->s_candidate_name;

                                if (!is_null($choice_id)) {
                                    $old_choice_pref = StudentChoice::where('ch_id', $choice_id)->first();

                                    if ($old_choice_pref) {
                                        $old_choice_pref_no = $old_choice_pref->ch_pref_no;
                                    }
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;

                                    $old_choice_pref->update([
                                        //'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice updated successfully";
                                } else {
                                    $pref_no    = is_null($choice_id) ? $choice_pref_no : $old_choice_pref_no;
                                    StudentChoice::create([
                                        //'ch_trade_code' => $trade_code,
                                        'ch_inst_code' => $inst_code,
                                        'ch_stu_id' => $user_id,
                                        'ch_pref_no' => $pref_no,
                                        'ch_fillup_time' => now()
                                    ]);

                                    $message = "{$student_name} selected choice #{$pref_no} as [{$inst_code}] - {$inst_name} and [{$trade_code}]  -  {$trade_name}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                    $resp_msg   =   "Choice created successfully";
                                }

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  $resp_msg
                                ], 200);
                            }
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

    public function studentInfoUpdate(Request $request)
    {
        $request->validate([
            'form_no' => ['required'],
        ]);

        try {
            $student = RegisterStudent::where('s_appl_form_num', $request->form_no)->first();

            if ($request->is_updated) {
                $request->validate([
                    'first_name' => ['required'],
                    'middle_name' => ['nullable'],
                    'last_name' => ['required'],
                    'father_name' => ['required'],
                    'mother_name' => ['required'],
                    'dob' => ['required'],
                    'email' => ['required'],
                    'gender' => ['required'],
                    'address' => ['required'],
                    'ps' => ['required'],
                    'po' => ['required'],
                    'pin' => ['required'],
                    'is_married' => ['nullable'],
                    'is_kanyashree' => ['nullable'],
                    'is_pwd' => ['nullable'],
                ]);

                $student->update([
                    's_first_name' => $request->first_name,
                    's_middle_name' => $request->middle_name,
                    's_last_name' => $request->last_name,
                    's_candidate_name' => Str::replace('  ', ' ', "{$request->first_name} {$request->middle_name} {$request->last_name}"),
                    's_father_name' => $request->father_name,
                    's_mother_name' => $request->mother_name,
                    's_dob' => $request->dob,
                    's_email' => $request->email,
                    's_gender' => $request->gender,
                    'address' => $request->address,
                    'ps' => $request->ps,
                    'po' => $request->po,
                    'pin' => $request->pin,
                    'is_married' => $request->is_married,
                    'is_kanyashree' => $request->is_kanyashree,
                    's_pwd' => $request->is_pwd,
                    'is_profile_updated' => true,
                ]);

                auditTrail($student->s_id, "{$student->s_candidate_name} updated details");
                studentActivite($student->s_id, "{$student->s_candidate_name} updated details");
            } else {
                $student->update([
                    'is_profile_updated' => true
                ]);

                auditTrail($student->s_id, "{$student->s_candidate_name} confirmed details");
                studentActivite($student->s_id, "{$student->s_candidate_name} confirmed details");
            }

            $student = RegisterStudent::where('s_appl_form_num', $request->form_no)->first();

            $rank_data = [];
            $userRank = $student;

            $rankArr = [
                's_gen_rank',
                's_sc_rank',
                's_st_rank',
                's_obca_rank',
                's_obcb_rank',
                's_pwd_rank',
                's_tfw_rank',
                's_ews_rank',
                's_llq_rank',
                's_exsm_rank'
            ];

            foreach ($rankArr as $val) {
                $userRankData = (int)$userRank[$val];
                if (!is_null($userRankData) && ($userRankData != 0)) {
                    array_push($rank_data, [
                        'category' => casteValue(Str::upper(explode('_', $val)[1])),
                        'rank' => $userRankData
                    ]);
                }
            }

            $check_choice_fillup = config_schedule('CHOICE_FILLUP');
            $choice_sehedule = $check_choice_fillup['status'];

            $check_accept = config_schedule('ACCEPT');
            $allotment_schedule = $check_accept['status'];

            return response()->json([
                'error' => false,
                'message' => 'Updated Successfully',
                'profile_update'   => (bool)$student->is_profile_updated,
                'choice_sehedule' => ($student->is_profile_updated == 1) && $choice_sehedule,
                'allotment_schedule' => ($student->is_choice_fill_up == 1) && ($student->is_lock_manual == 1) && $allotment_schedule,
                'user' => json_encode([
                    's_id' => $student->s_id,
                    's_uuid' => $student->s_uuid,
                    's_ref' => md5($student->s_id),
                    's_index_num' => $student->s_index_num,
                    's_appl_form_num' => $student->s_appl_form_num,
                    's_first_name' => $student->s_first_name,
                    's_middle_name' => $student->s_middle_name,
                    's_last_name' => $student->s_last_name,
                    's_full_name' => $student->s_candidate_name,
                    's_father_name' => $student->s_father_name,
                    's_mother_name' => $student->s_mother_name,
                    's_dob' => $student->s_dob,
                    's_aadhar_no' => $student->s_aadhar_no,
                    's_phone' => $student->s_phone,
                    's_email' => $student->s_email,
                    's_gender' => $student->s_gender,
                    's_religion' => $student->s_religion,
                    's_caste' => $student->s_caste,
                    's_tfw' => $student->s_tfw,
                    's_pwd' => $student->s_pwd,
                    's_llq' => $student->s_llq,
                    's_exsm' => $student->s_exsm,
                    's_alloted_category' => $student->s_alloted_category,
                    's_alloted_round' => $student->s_alloted_round,
                    's_choice_id' => $student->s_choice_id,
                    's_trade_code' => $student->s_trade_code,
                    's_inst_code' => $student->s_inst_code,
                    'is_alloted' => $student->is_alloted,
                    'is_choice_fill_up' => $student->is_choice_fill_up,
                    'is_payment' => $student->is_payment,
                    'is_upgrade' => $student->is_upgrade,
                    's_photo' => $student->s_photo,
                    's_home_district' => !is_null($student->s_home_district) ? $student->s_home_district : "",
                    's_schooling_district' => !is_null($student->s_schooling_district) ? $student->s_schooling_district : "",
                    's_state_id' => $student->s_state_id,
                    'is_active' => $student->is_active,
                    'is_lock_manual' => $student->is_lock_manual,
                    'is_lock_auto' => $student->is_lock_auto,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at,
                    'manual_lock_at' => $student->manual_lock_at,
                    'auto_lock_at' => $student->auto_lock_at,
                    'rank' => $rank_data,
                    'address' => $student->address,
                    'ps' => $student->ps,
                    'po' => $student->po,
                    'pin' => $student->pin,
                    'is_married' => (bool)$student->is_married,
                    'is_kanyashree' => (bool)$student->is_kanyashree,
                    'role_id' => 2,
                ]),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    //Student choice list
    public function choiceList(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id', 'is_lock_manual', 'is_lock_auto')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-list', $url_data)) { //check url has permission or not
                        $choice_res = null;
                        $choice_list = StudentChoice::where('ch_stu_id',  $user_id)->where('ch_fillup_time', '>', '2024-07-14 11:00:00')->with('student')->orderBy('ch_pref_no', 'ASC')->get();

                        if (sizeof($choice_list) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student choice found',
                                'count'     =>   sizeof($choice_list),
                                'choiceList'   =>  StudentChoiceResource::collection($choice_list)
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

    //Student choice remove
    public function choiceRemove(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-remove', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;

                            $oldData = StudentChoice::where('ch_id', $choice_id)->first();
                            $old_pref_no = $oldData->ch_pref_no;
                            //$old_trade_code = $oldData->ch_trade_code;
                            $old_inst_code = $oldData->ch_inst_code;

                            //$tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                            $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                            $trade_name = '';
                            $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                            $del = $oldData->delete();
                            //dd($del);
                            if ($del == 1) {
                                //dd($old_pref_no);
                                $list = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', '>', $old_pref_no)->get();
                                //return $list;
                                if (sizeof($list) > 0) {
                                    foreach ($list as $key => $val) {
                                        $val->update([
                                            'ch_pref_no' => (int)$val->ch_pref_no - 1,
                                        ]);
                                    }
                                }


                                $student_name = $user_data->s_candidate_name;

                                $message = "{$student_name} removed choice #{$old_pref_no} having [{$old_inst_code}] - {$inst_name}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Choice removed successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'message'              =>  "Student choice does not exist"
                                ], 200);
                            }
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

    //Student up/down choice
    public function choiceUpDown(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-up-down', $url_data)) { //check url has permission or not
                        $validated = Validator::make($request->all(), [
                            'choice_id' => ['required'],
                            'type' => ['required'],
                            'old_preference_no' => ['required'],
                        ]);

                        if ($validated->fails()) {
                            return response()->json([
                                'error' => true,
                                'message' => $validated->errors()
                            ]);
                        }
                        DB::beginTransaction();
                        try {

                            $choice_id = (int)$request->choice_id;
                            $type = $request->type;
                            $old_choice_pref_no = (int)$request->old_preference_no;
                            //$new_choice_pref_no = "";
                            $student_name = $user_data->s_candidate_name;

                            if ($type == 'up') {
                                $new_choice_pref_no = (int)($old_choice_pref_no - 1);
                                $existingData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();



                                $toData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                // $old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;


                                //$to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                //$tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();


                                //$totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';


                                $to_trade_name = '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';


                                if ($existingData) {
                                    StudentChoice::where('ch_id', $existingData->ch_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$old_choice_pref_no} to #{$new_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                StudentChoice::where('ch_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                // $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                // auditTrail($user_id, $message);
                                // studentActivite($user_id, $message);
                            } else {
                                $new_choice_pref_no = (int)($old_choice_pref_no + 1);
                                $existingData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $new_choice_pref_no)->first();
                                //dd($existingData);
                                $toData = StudentChoice::where('ch_stu_id', $user_id)->where('ch_pref_no', $old_choice_pref_no)->first();

                                //$old_trade_code = $existingData->ch_trade_code;
                                $old_inst_code = $existingData->ch_inst_code;

                                //$to_trade_code = $toData->ch_trade_code;
                                $to_inst_code = $toData->ch_inst_code;



                                //$tradeData = Trade::where(['t_code' => $old_trade_code, 'is_active' => 1])->first();
                                $instData = Institute::where(['i_code' => $old_inst_code, 'is_active' => 1])->first();

                                //$totradeData = Trade::where(['t_code' => $to_trade_code, 'is_active' => 1])->first();
                                $toinstData = Institute::where(['i_code' => $to_inst_code, 'is_active' => 1])->first();

                                $trade_name = '';
                                $inst_name = !is_null($instData->i_name) ? $instData->i_name : '';

                                $to_trade_name = '';
                                $to_inst_name = !is_null($toinstData->i_name) ? $toinstData->i_name : '';

                                if ($existingData) {
                                    StudentChoice::where('ch_id', $existingData->ch_id)->where('ch_stu_id', $user_id)->update([
                                        'ch_pref_no' => $old_choice_pref_no,
                                    ]);

                                    $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                    auditTrail($user_id, $message);
                                    studentActivite($user_id, $message);
                                }
                                StudentChoice::where('ch_id', $choice_id)->where('ch_stu_id', $user_id)->update([
                                    'ch_pref_no' => $new_choice_pref_no,
                                ]);

                                /* $message = "{$student_name} updated choice preference from #{$new_choice_pref_no} to #{$old_choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message); */
                            }

                            DB::commit();
                            return response()->json([
                                'error'             =>  false,
                                'message'              =>  "Choice updated successfully"
                            ], 200);
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

    //Student bulk final submit
    public function choiceLockFinalSubmit(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::select('s_id', 's_candidate_name')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-lock-final-submit', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            User::where('s_id', $user_id)->update([
                                'is_lock_manual' => 1,
                                'is_choice_fill_up' => 1,
                                'manual_lock_at' => now()
                            ]);
                            $student_name = $user_data->s_candidate_name;
                            $redirect = $this->checkRedirect($user_id);

                            $message = "{$student_name} submitted and locked choices";

                            auditTrail($user_id, $message);
                            studentActivite($user_id, $message);

                            DB::commit();
                            return response()->json([
                                'error'     =>  false,
                                'message'   =>  "Choices are successfully locked and submitted",
                                'redirect'  =>  $redirect
                            ], 200);
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

    //Student Activities
    public function activities(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::select('s_id', 'is_lock_manual', 'is_lock_auto')->where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('activities', $url_data)) { //check url has permission or not
                        $activity_res = null;
                        $activity_list = StudentActivity::where('a_stu_id',  $user_id);
                        $activity_res = $activity_list->orderBy('a_id', 'ASC')->get();


                        if (sizeof($activity_res) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Student activities found',
                                'count'     =>   sizeof($activity_res),
                                'activityList'   =>  StudentActivityResource::collection($activity_res)
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

    //Choice fill up pdf
    public function choicePdf(Request $request)
    {
        $choice_list = StudentChoice::select('ch_id', 'ch_trade_code', 'ch_inst_code', 'ch_pref_no', 'ch_stu_id', 'ch_fillup_time')->where('ch_stu_id',  $request->student_id)
            // ->where('ch_fillup_time', '>', '2024-07-14 11:00:00')
            ->with([
                'institute:i_code,i_name',
                'student:s_id,s_candidate_name,s_phone'
            ])
            ->orderBy('ch_pref_no', 'ASC')
            ->get();

        $finalList = $choice_list->map(function ($single) {
            return [
                'choice_time' => $single->ch_fillup_time,
                'choice_no' => $single->ch_pref_no,
                'institute_name' => $single->institute->i_name,
                'branch_name' => '',
                'branch_code' => '',
                'institute_code' => $single->institute->i_code,
                'candidate_name' => $single->student->s_candidate_name,
                'candidate_phone' => $single->student->s_phone,
            ];
        });

        $payment = PaymentTransaction::where('pmnt_stud_id', $request->student_id)
            ->whereNotNull('trans_id')
            ->whereNotNull('trans_mode')
            ->where('trans_status', 'SUCCESS')
            ->first();

        $pdf = Pdf::loadView('exports.choicefill', [
            'choices' => $finalList,
            'payment' => $payment,
        ]);

        $student = RegisterStudent::where('s_id', $request->student_id)->first();

        if ($student) {
            $student->update([
                'is_choice_downloaded' => 1
            ]);

            return $pdf->setPaper('a4', 'portrait')
                ->setOption(['defaultFont' => 'sans-serif'])
                ->stream("choice-{$student->s_appl_form_num}.pdf");
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student not found'
            ]);
        }
    }

    //Alotement Details
    public function allotementDetails(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('allotement-details', $url_data)) { //check url has permission or not
                        if ($user_data) {
                            $choiceData = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->first();

                            if ($choiceData) {
                                $institute = Institute::where('i_code', $choiceData->ch_inst_code)->first();
                                //$branch = Trade::where('t_code', $choiceData->ch_trade_code)->first();
                                $choice = $choiceData->ch_pref_no;
                                $user = [
                                    'institute_name' =>  $institute->i_name,
                                    'branch_name' =>  '',
                                    'allotement_category' =>  !empty($choiceData->ch_alloted_category) ? casteValue(Str::upper($choiceData->ch_alloted_category)) : "N/A",
                                    'allotement_round' =>  !empty($choiceData->ch_alloted_round) ? $choiceData->ch_alloted_round : "N/A",
                                    'choice_option' =>  $choice,
                                    'allotment_accepted' => (bool)$user_data->is_allotment_accept
                                ];

                                $reponse = array(
                                    'error'     =>  false,
                                    'message'   =>  'Data found',
                                    'allotementDetails'   =>  $user
                                );
                                return response(json_encode($reponse), 200);
                            } else {
                                $reponse = array(
                                    'error'     =>  true,
                                    'details_found' => !is_null($choiceData) ? true : false,
                                    'message'   =>  'Sorry allotement is not done yet'
                                );
                                return response(json_encode($reponse), 200);
                            }
                        } else {
                            $reponse = array(
                                'error'     =>  true,
                                'message'   =>  'No user found!'
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

    public function checkRedirect($user_id)
    {
        $newuser = User::where('s_id', $user_id)->first();

        if ($newuser) {
            $profile_updated = $choice_fillup_page = $payment_page = $allotement_page = $choice_preview_page
                = $payment_done =  $upgrade_done = $admitted = $accept_allotement = $upgrade_payment_done = $reject =  $schedule_choice_fillup = $schedule_admission = $student_auto_reject = false;

            $checkChoice = $newuser->is_lock_manual;
            $checkChoiceAuto = $newuser->is_lock_auto;
            $checkPayment = $newuser->is_payment;
            $checkallotement = $newuser->is_alloted;
            $profile_updated = (bool)$newuser->is_profile_updated;
            $checkUpgrade = $newuser->is_upgrade;
            $checkUpgradePayment = $newuser->is_upgrade_payment;
            $checkAdmitted = $newuser->s_admited_status;
            $checkAllotementAccept = $newuser->is_allotment_accept;
            $checkStatusReject = $newuser->s_admited_status;
            $student_reject_remarks = $newuser->s_remarks;

            $check_choice_fillup = config_schedule('CHOICE_FILLUP');
            $check_choice_status = $check_choice_fillup['status'];

            $check_accept = config_schedule('ACCEPT');
            $check_accept_status = $check_accept['status'];

            $check_upgrade = config_schedule('UPGRADE');
            $check_upgrade_status = $check_upgrade['status'];

            $check_admission = config_schedule('ADMISSION');
            $check_admission_status = $check_admission['status'];

            $checkStudentAutoRejectRound = $newuser->s_auto_reject;

            $got_first_choice = StudentChoice::where([
                'ch_stu_id' => $newuser->s_id,
                'ch_inst_code' => $newuser->s_inst_code,
                'ch_pref_no' => 1,
            ])->first();

            if ($check_choice_status && (($checkChoice == 0) &&  ($checkChoiceAuto == 0))) {
                $choice_fillup_page = true;
            }

            if ((($checkChoice == 1) ||  ($checkChoiceAuto == 1)) && ($checkallotement == 0)) { //&& ($checkallotement == 0)
                $choice_preview_page = true;
            }

            if ((($checkChoice == 1) || ($checkChoiceAuto == 1)) && ($checkPayment == 0)) {
                $payment_page = true;
            }

            if ($checkPayment == 1) {
                $payment_done = true;
            }

            if ((($checkChoice == 1) || ($checkChoiceAuto == 1))) {
                $allotement_page = true;
            }

            // if ((($checkChoice == 1) || ($checkChoiceAuto == 1)) && ($checkallotement == 1)) {
            //     $allotement_page = true;
            // }

            if ($checkUpgrade == 1) {
                $upgrade_done = true;
            }
            if ($checkUpgradePayment == 1) {
                $upgrade_payment_done = true;
            }
            if ($checkAdmitted == 1) {
                $admitted = true;
            }
            if ($checkAllotementAccept == 1) {
                $accept_allotement = true;
            }
            if ($checkStatusReject == 2) {
                $reject = true;
            }
            if ($check_choice_status == true) {
                $schedule_choice_fillup = true;
            }
            if ($check_admission_status == true) {
                $schedule_admission = true;
            }
            if ($checkStudentAutoRejectRound == 1) {
                $student_auto_reject = true;
            }

            $redirect = [
                'profile_update' => $profile_updated,
                'choice_fillup_page'   => $choice_fillup_page,
                'payment_page'   => $payment_page,
                'choice_preview_page'   => $choice_preview_page,
                'payment_done' => $payment_done,
                'allotement_page'   => $allotement_page,
                'upgrade_done' => $upgrade_done,
                'upgrade_payment_done' => $upgrade_payment_done,
                'student_admitted' => $admitted,
                'student_allotment_accepted' => $accept_allotement,
                'allotment_accepted' => $accept_allotement,
                'student_reject_status' => $reject,
                'student_reject_remarks' => $student_reject_remarks,
                'schedule_choice_fillup' => $schedule_choice_fillup,
                'schedule_acceptance' => $check_accept_status,
                'schedule_upgradation' => $check_upgrade_status,
                'schedule_admission' => $schedule_admission,
                'student_auto_reject' => $student_auto_reject,
                'can_upgrade' => $newuser->is_alloted == 1 && is_null($got_first_choice) ? true : false,
                'upgrade_enabled' => env('UPGRADE_ENABLED'),
                'registration_fees_paid' => (bool)$newuser->is_registration_payment,
                'is_spot_payment' => (bool)$newuser->is_spot_payment,
                'overall_status' => getOverallStatus($newuser->s_id)
            ];

            return response()->json([
                'error'     =>  false,
                'message'   =>  'Data found',
                'redirect' => $redirect
            ]);
        }
    }

    //Allotement Pdf
    public function allotmentPdf(Request $request)
    {
        $student_uuid = $request->student_id;
        if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $student_uuid) == 1) {
            $userData = User::where('s_uuid', $student_uuid)->first();
            if ($userData) {
                $student_id = $userData->s_id;
                $option_type = $request->type;
                $trans_time = $trans_amount =  $trans_mode = $transactionId = $amount_in_words = $bank_ref_no =  '';
                $alloted_round = $userData->s_alloted_round;
                $event = 'ADMISSION';
                $configSchedule = Schedule::where('sch_event', $event)->where('sch_round', $alloted_round)->first();
                $end_date = '';

                if ($configSchedule) {
                    $dateStr = explode(' ', $configSchedule->sch_end_dt);
                    $end_date = $dateStr[0];
                }

                $choice_list = StudentChoice::where('ch_stu_id', $student_id)->where('is_alloted', 1)->with('student')->first();
                $checkPayment = PaymentTransaction::where('pmnt_modified_by', $student_id)->where('trans_status', 'SUCCESS');

                if (!is_null($option_type)) {
                    $checkPayment = $checkPayment->where('pmnt_pay_type', 'COUNSELLINGUPGRADEFEES')->first();

                    $transactionId = $checkPayment['trans_id'];
                    $amount_in_words = 'Rupees One Thousand';
                    $trans_time = $checkPayment['trans_time'] ?? '';
                    $trans_amount = $checkPayment['trans_amount'] ?? '';
                    $trans_mode = $checkPayment['trans_mode'] ?? '';
                    $bank_ref_no = $checkPayment['bank_ref'] ?? '';
                } else {
                    $checkPayment = $checkPayment->where('pmnt_pay_type', 'COUNSELLINGFEES')->first();

                    $transactionId = isset($checkPayment['trans_id']) ? $checkPayment['trans_id'] : '';
                    $amount_in_words = 'Rupees Five Hundred';
                    $trans_time = $checkPayment['trans_time'] ?? '';
                    $trans_amount = $checkPayment['trans_amount'] ?? '';
                    $trans_mode = $checkPayment['trans_mode'] ?? '';
                    $bank_ref_no = $checkPayment['bank_ref'] ?? '';
                }

                if ($choice_list) {
                    $institute = Institute::where('i_code', $choice_list->ch_inst_code)->first();

                    $con_person1 = !is_null($institute->i_contact_person_name_1) ? Str::upper($institute->i_contact_person_name_1) : 'N/A';
                    $con_person2 = !is_null($institute->i_contact_person_name_2) ? Str::upper($institute->i_contact_person_name_2) : 'N/A';
                    $con_person3 = !is_null($institute->i_contact_person_name_3) ? Str::upper($institute->i_contact_person_name_3) : 'N/A';

                    $con_person_ph1 = !is_null($institute->i_contact_person_phone_1) ? $institute->i_contact_person_phone_1 : 'N/A';
                    $con_person_ph2 = !is_null($institute->i_contact_person_phone_2) ? $institute->i_contact_person_phone_2 : 'N/A';
                    $con_person_ph3 = !is_null($institute->i_contact_person_phone_3) ? $institute->i_contact_person_phone_3 : 'N/A';
                    $contact_persons = $con_person1 . ',' . $con_person2 . ',' . $con_person3;
                    $contact_person_phones = $con_person_ph1 . ',' . $con_person_ph2 . ',' . $con_person_ph3;
                    $contact_inst_email = !is_null($institute->i_contact_email) ? $institute->i_contact_email : 'N/A';

                    $choice = $choice_list->ch_pref_no;
                    $rankArr = array('s_gen_rank', 's_sc_rank', 's_st_rank', 's_obca_rank', 's_obcb_rank', 's_pwd_rank');
                    $rank_data = [];
                    $userRank = $choice_list->student;
                    $genRank = $choice_list->student->s_gen_rank;

                    foreach ($rankArr as $val) {
                        $userRankData = (int)$userRank[$val];
                        $cat = explode('_', $val);
                        array_push(
                            $rank_data,
                            [
                                'category' => casteValue(Str::upper($cat[1])),
                                'rank' => $userRankData
                            ]
                        );
                    }

                    $finalList = [
                        'institute_name' =>  $institute->i_name,
                        'branch_name' =>  'Diploma in Pharmacy',
                        'contact_person_name' =>  $contact_persons,
                        'contact_person_phone' =>  $contact_person_phones,
                        'contact_inst_email' =>  $contact_inst_email,
                        'allotement_category' =>  !empty($choice_list->ch_alloted_category) ? casteValue(Str::upper($choice_list->ch_alloted_category)) : "N/A",
                        'allotement_round' =>  $alloted_round,
                        'choice_option' =>  $choice,
                        'appl_form_num' => $choice_list->student->s_appl_form_num,
                        'candidate_gender' => $choice_list->student->s_gender,
                        'candidate_name' => Str::upper($choice_list->student->s_candidate_name),
                        'candidate_guardian_name' => !is_null($choice_list->student->s_father_name) ? Str::upper($choice_list->student->s_father_name) : "N/A",
                        'candidate_caste' => $choice_list->student->s_caste,
                        'candidate_physically_challenged' => ($choice_list->student->s_pwd == 0) ? "No" : "Yes",
                        'candidate_home_district' => '',
                        'candidate_schooling_district' => '',
                        'rank' => $rank_data,
                        'candidate_photo' => $choice_list->student->s_photo,
                        'candidate_sign' => $choice_list->student->s_sign,
                        'candidate_dob' => Carbon::parse($choice_list->student->s_dob)->format('d/m/Y'),
                        'provisional' => !is_null($option_type) ? Str::upper($option_type) : "",
                        'trans_time' => !empty($trans_time) ? Carbon::parse($trans_time)->format('d/m/Y H:i:s') : '',
                        'trans_amount' => $trans_amount,
                        'trans_mode' => $trans_mode,
                        'trans_id' => $transactionId,
                        'gen_rank' => $genRank,
                        'candidate_land_looser' => ($choice_list->student->s_llq == 0) ? "No" : "Yes",
                        'candidate_under_tfw' => ($choice_list->student->s_tfw == 0) ? "No" : "Yes",
                        'candidate_ex_serviceman' => ($choice_list->student->s_exsm == 0) ? "No" : "Yes",
                        'candidate_ews' => ($choice_list->student->s_ews == 0) ? "No" : "Yes",
                        'session' => "2024-25",
                        'candidate_phone' => $choice_list->student->s_phone,
                        'admission_end_date' => Carbon::parse($end_date)->format('d/m/Y'),
                        'bank_ref_no' => $bank_ref_no,
                        'amount_in_words' => $amount_in_words
                    ];

                    $student_name = Str::upper($choice_list->student->s_candidate_name);
                    $message = "{$student_name} downloaded the final allotment letter";

                    auditTrail($student_id, $message);
                    studentActivite($student_id, $message);
                    if (is_null($option_type)) {
                        $pdf = Pdf::loadView('exports.allotment', [
                            'data' => $finalList,
                        ]);
                        return $pdf->setPaper('a4', 'portrait')
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->stream('allotment.pdf');
                    } else {
                        $pdf = Pdf::loadView('exports.allotment-provissional', [
                            'data' => $finalList,
                        ]);

                        return $pdf->setPaper('a4', 'portrait')
                            ->setOption(['defaultFont' => 'sans-serif'])
                            ->stream('provissional_allotment.pdf');
                    }
                } else {
                    $reponse = array(
                        'error'     =>  true,
                        'message'   =>  'No data found!'
                    );
                    return response(json_encode($reponse), 200);
                }
            } else {
                $reponse = array(
                    'error'     =>  true,
                    'message'   =>  'No data found!'
                );
                return response(json_encode($reponse), 200);
            }
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'Invalid Request!'
            );
            return response(json_encode($reponse), 200);
        }
    }

    public function registrationPdf(Request $request)
    {
        $student_uuid = $request->student_id;
        if (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $student_uuid) == 1) {
            $userData = User::where('s_uuid', $student_uuid)->first();
            if ($userData) {
                $student_id = $userData->s_id;
                $option_type = $request->type;
                $trans_time = $trans_amount =  $trans_mode = $transactionId = $amount_in_words = $bank_ref_no =  '';
                $alloted_round = $userData->s_alloted_round;
                $event = 'REGISTRATION';
                $configSchedule = Schedule::where('sch_event', $event)->where('sch_round', $alloted_round)->first();
                $end_date = '';

                if ($configSchedule) {
                    $dateStr = explode(' ', $configSchedule->sch_end_dt);
                    $end_date = $dateStr[0];
                }

                $choice_list = StudentChoice::where('ch_stu_id', $student_id)->where('is_alloted', 1)->with('student')->first();
                $checkPayment = PaymentTransaction::where('pmnt_modified_by', $student_id)->where('trans_status', 'SUCCESS');

                $checkPayment = $checkPayment->where('pmnt_pay_type', 'COUNSELLINGREGISTRATIONFEES')->first();

                $transactionId = $checkPayment['trans_id'];
                $trans_time = $checkPayment['trans_time'] ?? '';
                $trans_amount = $checkPayment['trans_amount'] ?? '';
                $amount_in_words = Str::ucfirst(Number::spell($checkPayment['trans_amount']));
                $trans_mode = $checkPayment['trans_mode'] ?? '';
                $bank_ref_no = $checkPayment['bank_ref'] ?? '';

                if ($choice_list) {
                    $institute = Institute::where('i_code', $choice_list->ch_inst_code)->first();

                    $con_person1 = !is_null($institute->i_contact_person_name_1) ? Str::upper($institute->i_contact_person_name_1) : 'N/A';
                    $con_person2 = !is_null($institute->i_contact_person_name_2) ? Str::upper($institute->i_contact_person_name_2) : 'N/A';
                    $con_person3 = !is_null($institute->i_contact_person_name_3) ? Str::upper($institute->i_contact_person_name_3) : 'N/A';

                    $con_person_ph1 = !is_null($institute->i_contact_person_phone_1) ? $institute->i_contact_person_phone_1 : 'N/A';
                    $con_person_ph2 = !is_null($institute->i_contact_person_phone_2) ? $institute->i_contact_person_phone_2 : 'N/A';
                    $con_person_ph3 = !is_null($institute->i_contact_person_phone_3) ? $institute->i_contact_person_phone_3 : 'N/A';
                    $contact_persons = $con_person1 . ',' . $con_person2 . ',' . $con_person3;
                    $contact_person_phones = $con_person_ph1 . ',' . $con_person_ph2 . ',' . $con_person_ph3;
                    $contact_inst_email = !is_null($institute->i_contact_email) ? $institute->i_contact_email : 'N/A';

                    $choice = $choice_list->ch_pref_no;
                    $rankArr = array('s_gen_rank', 's_sc_rank', 's_st_rank', 's_obca_rank', 's_obcb_rank', 's_pwd_rank');
                    $rank_data = [];
                    $userRank = $choice_list->student;
                    $genRank = $choice_list->student->s_gen_rank;

                    foreach ($rankArr as $val) {
                        $userRankData = (int)$userRank[$val];
                        $cat = explode('_', $val);
                        array_push(
                            $rank_data,
                            [
                                'category' => casteValue(Str::upper($cat[1])),
                                'rank' => $userRankData
                            ]
                        );
                    }

                    $finalList = [
                        'institute_name' =>  $institute->i_name,
                        'branch_name' =>  'Diploma in Pharmacy',
                        'contact_person_name' =>  $contact_persons,
                        'contact_person_phone' =>  $contact_person_phones,
                        'contact_inst_email' =>  $contact_inst_email,
                        'allotement_category' =>  !empty($choice_list->ch_alloted_category) ? casteValue(Str::upper($choice_list->ch_alloted_category)) : "N/A",
                        'allotement_round' =>  $alloted_round,
                        'choice_option' =>  $choice,
                        'appl_form_num' => $choice_list->student->s_appl_form_num,
                        'candidate_gender' => $choice_list->student->s_gender,
                        'candidate_name' => Str::upper($choice_list->student->s_candidate_name),
                        'candidate_guardian_name' => !is_null($choice_list->student->s_father_name) ? Str::upper($choice_list->student->s_father_name) : "N/A",
                        'candidate_caste' => $choice_list->student->s_caste,
                        'candidate_physically_challenged' => ($choice_list->student->s_pwd == 0) ? "No" : "Yes",
                        'candidate_home_district' => '',
                        'candidate_schooling_district' => '',
                        'rank' => $rank_data,
                        'candidate_photo' => $choice_list->student->s_photo,
                        'candidate_sign' => $choice_list->student->s_sign,
                        'candidate_dob' => Carbon::parse($choice_list->student->s_dob)->format('d/m/Y'),
                        'provisional' => !is_null($option_type) ? Str::upper($option_type) : "",
                        'trans_time' => !empty($trans_time) ? Carbon::parse($trans_time)->format('d/m/Y H:i:s') : '',
                        'trans_amount' => $trans_amount,
                        'trans_mode' => $trans_mode,
                        'trans_id' => $transactionId,
                        'gen_rank' => $genRank,
                        'candidate_land_looser' => ($choice_list->student->s_llq == 0) ? "No" : "Yes",
                        'candidate_under_tfw' => ($choice_list->student->s_tfw == 0) ? "No" : "Yes",
                        'candidate_ex_serviceman' => ($choice_list->student->s_exsm == 0) ? "No" : "Yes",
                        'candidate_ews' => ($choice_list->student->s_ews == 0) ? "No" : "Yes",
                        'session' => "2024-25",
                        'candidate_phone' => $choice_list->student->s_phone,
                        'admission_end_date' => Carbon::parse($end_date)->format('d/m/Y'),
                        'bank_ref_no' => $bank_ref_no,
                        'amount_in_words' => $amount_in_words
                    ];

                    $student_name = Str::upper($choice_list->student->s_candidate_name);
                    $message = "{$student_name} downloaded the final allotment letter";

                    auditTrail($student_id, $message);
                    studentActivite($student_id, $message);

                    $pdf = Pdf::loadView('exports.registration', [
                        'data' => $finalList,
                    ]);

                    return $pdf->setPaper('a4', 'portrait')
                        ->setOption(['defaultFont' => 'sans-serif'])
                        ->stream('registration_fees.pdf');
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>  'No data found!'
                    ]);
                }
            } else {
                return response()->json([
                    'error'     =>  true,
                    'message'   =>  'No data found!'
                ]);
            }
        } else {
            $reponse = array(
                'error'     =>  true,
                'message'   =>  'Invalid Request!'
            );
            return response(json_encode($reponse), 200);
        }
    }

    //Accept allotment
    public function allotmentAccept(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('accept-allotment', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            $choice_list = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->with('student')->first();

                            if ($choice_list) {
                                //dd($old_pref_no);
                                $inst_code = $choice_list->ch_inst_code;
                                $trade_code = 'PHARM';
                                $choice_pref_no = $choice_list->ch_pref_no;
                                $allotment_category = $choice_list->ch_alloted_category;
                                $alloted_round = $choice_list->ch_alloted_round;
                                $choice_id = $choice_list->ch_id;

                                $user_data->update([
                                    'updated_at' => now(),
                                    'is_alloted' => 1,
                                    's_inst_code' => $inst_code,
                                    's_trade_code' => $trade_code,
                                    's_choice_id' => $choice_id,
                                    's_alloted_round' => $alloted_round,
                                    's_alloted_category' => $allotment_category,
                                    'is_allotment_accept' => 1,
                                ]);

                                $student_name = $user_data->s_candidate_name;
                                $message = "{$student_name} accepted the allotment having Institute Code [{$inst_code}] and Stream Code [{$trade_code}] against the Choice #{$choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Allotment Updated Successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'details_found' => !is_null($choice_list) ? true : false,
                                    'message'              =>  "Choice List Not Found"
                                ], 200);
                            }
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

    //allotment upgrade
    public function allotmentUpgrade(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            //return $token_check;
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                //return $user_id;
                $user_data = User::where('s_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', 2)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('accept-allotment', $url_data)) { //check url has permission or not

                        DB::beginTransaction();
                        try {
                            $choice_list = StudentChoice::where('ch_stu_id', $user_id)->where('is_alloted', 1)->with('student')->first();

                            if ($choice_list) {
                                //dd($old_pref_no);
                                $inst_code = $choice_list->ch_inst_code;
                                $trade_code = $choice_list->ch_trade_code;
                                $choice_pref_no = $choice_list->ch_pref_no;
                                $allotment_category = $choice_list->ch_alloted_category;
                                $alloted_round = $choice_list->ch_alloted_round;
                                $choice_id = $choice_list->ch_id;

                                $choice_list->update([
                                    'ch_auto_upgrade' => 1
                                ]);

                                $user_data->update([
                                    'updated_at' => now(),
                                    's_inst_code' => $inst_code,
                                    //'s_trade_code' => $trade_code,
                                    's_choice_id' => $choice_id,
                                    's_alloted_round' => $alloted_round,
                                    's_alloted_category' => $allotment_category,
                                    'is_upgrade' => 1
                                ]);

                                $student_name = $user_data->s_candidate_name;
                                $message = "{$student_name} upgraded the allotment having Institute Code [{$inst_code}] and Stream Code [{$trade_code}] against the Choice #{$choice_pref_no}";

                                auditTrail($user_id, $message);
                                studentActivite($user_id, $message);

                                DB::commit();

                                return response()->json([
                                    'error'             =>  false,
                                    'message'              =>  "Allotment upgraded Successfully"
                                ], 200);
                            } else {
                                return response()->json([
                                    'error'             =>  true,
                                    'details_found' => !is_null($choice_list) ? true : false,
                                    'message'              =>  "Choice List Not Found"
                                ], 200);
                            }
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
}
