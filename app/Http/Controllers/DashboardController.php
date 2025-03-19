<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Token;
use App\Models\SuperUser;
use App\Models\MgmtStudent;
use App\Models\SpotStudent;
use Illuminate\Http\Request;
use App\Models\RegisterStudent;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function countDashboardCardsBackup(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')
                    ->where('u_id', $user_id)
                    ->first();

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')
                    ->where('rp_role_id', $user_data->u_role_id)
                    ->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('card-count', $url_data)) {

                        $choiceFillupCount = 0;
                        $councilFeesPaidCount = 0;
                        $lockManualCount = 0;
                        $lockSubmitAutoCount = 0;
                        $allotedCount = 0;
                        $allotedAcceptCount = 0;
                        $upgradeCount = 0;
                        $admittedCount = 0;
                        $rejectedCount = 0;

                        $admittedCountPhase1 = 0;
                        $rejectedCountPhase1 = 0;

                        $admittedCountPhase2 = 0;
                        $rejectedCountPhase2 = 0;

                        $allotedCountCurrent = 0;
                        $allotedAcceptCountCurrent = 0;
                        $admittedCountCurrent = 0;
                        $rejectedCountCurrent = 0;

                        if ($user_data->u_role_id == 3) { // INST Admin
                            $query = User::whereIn('is_active', [0, 1]);
                            $inst_code = $user_data->u_inst_code;

                            /* $allotedCount = $query->clone()->whereHas('choices', function ($query) use ($inst_code){
                                $query->where('ch_inst_code', $inst_code)
                                ->where('ch_fillup_time', '>', '2024-07-14 11:00:00')
                                ->where('is_alloted', 1);
                            })->where('is_alloted', 1)->where('s_auto_reject', 0)->count('is_alloted');

                            $allotedAcceptCount = $query->clone()->where('s_inst_code', $user_data->u_inst_code)->where('is_alloted', 1)->where('is_allotment_accept', 1)->count('is_allotment_accept');


                            $upgradeCount = $query->clone()->where('s_inst_code', $user_data->u_inst_code)
                            ->where('is_alloted', 1)->where('is_upgrade_payment',1)->count('is_upgrade_payment');

                            $admittedCount = $query->clone()->where('s_inst_code', $user_data->u_inst_code)->where('is_alloted', 1)->where('s_admited_status', 1)->count('s_admited_status'); */


                            $allotedCount = $query->clone()->where('s_inst_code', $inst_code)->where('is_alloted', 1)->count('is_alloted');

                            $allotedAcceptCount = $query->clone()->where('s_inst_code', $inst_code)->where('is_alloted', 1)->where('is_allotment_accept', 1)->count('is_allotment_accept');

                            $upgradeCount = $query->clone()->where('s_inst_code', $inst_code)->where('is_alloted', 1)->where('is_upgrade_payment', 1)->count('is_upgrade_payment');

                            $admittedCount = $query->clone()->where('s_inst_code', $inst_code)->where('is_alloted', 1)->where('s_admited_status', 1)->count('s_admited_status');

                            $rejectedCount = $query->clone()->where('s_inst_code', $inst_code)->where('is_alloted', 1)->where('s_admited_status', 2)->count('s_admited_status');

                            $allotedCountCurrent = DB::table('pharmacy_register_student as rs')->join('pharmacy_choice_student as cs', 'cs.ch_stu_id', '=', 'rs.s_id')->where('ch_inst_code', $inst_code)->where('cs.is_alloted', 1)->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 0)->where('rs.is_active', 1)->count('rs.s_id');


                            //$allotedCount = $allotedCount + $allotedCountCurrent;
                            $allotedCount = $allotedCountCurrent;
                        } else {
                            //    $query = User::whereIn('is_active', [0,1]);
                            //$choiceFillupCount = StudentChoice::distinct('ch_stu_id')->count('ch_stu_id');
                            //return $choiceFillupCount;

                            //$councilFeesPaidCount = $query->clone()->where('is_payment', 1)->count('is_payment');
                            /*     $choiceCount = DB::select("SELECT
                                                COUNT(DISTINCT cs.ch_stu_id) AS s_count
                                            FROM
                                                pharmacy_choice_student cs
                                            JOIN
                                                pharmacy_register_student vrs ON vrs.s_id = cs.ch_stu_id
                                            WHERE
                                                cs.ch_fillup_time >= '2024-07-14 17:00:00'");
                            $choiceFillupCount = $choiceCount[0]->s_count;

                            $lockManualCount = $query->clone()->where('is_lock_manual', 1)->count('is_lock_manual');

                            $lockSubmitAutoCount = $query->clone()->where('is_lock_auto', 1)->count('is_lock_auto'); */

                            /*     $councilFeesPaidCount = DB::select("SELECT
                                        DISTINCT pt.pmnt_stud_id,
                                        pt.pmnt_pay_type,
                                        rs.s_candidate_name
                                        FROM
                                            jexpo_payment_transaction_tbl pt
                                        JOIN
                                            pharmacy_register_student rs ON rs.s_id = pt.pmnt_stud_id
                                        WHERE
                                            pt.pmnt_created_on >= ?
                                        AND
                                            rs.is_payment = ?", ['2024-07-14 17:00:00', 1]);
                            $councilFeesPaidCount = count($councilFeesPaidCount); */

                            /*     $councilFeesPaidCount = $query->clone()->where('is_lock_manual', 1)->where('is_payment', 1)->count('is_payment');

                            $allotedCount = DB::table('pharmacy_register_student as rs')
                            ->join('pharmacy_choice_student as cs', 'cs.ch_stu_id', '=', 'rs.s_id')
                            ->where('rs.is_alloted', 1)
                            ->where('cs.is_alloted', 1)
                            ->count('rs.s_id');

                            $allotedAcceptCount = $query->clone()->where('is_alloted', 1)->where('is_allotment_accept', 1)->count('is_allotment_accept');

                            $upgradeCount = $query->clone()->where('is_alloted', 1)->where('is_upgrade_payment',1)->count('is_upgrade_payment');

                            $admittedCount = $query->clone()->where('is_alloted', 1)->where('s_admited_status', 1)->count('s_admited_status');

                            $rejectedCount = $query->clone()->where('is_alloted', 1)->where('s_admited_status', 2)->count('s_admited_status'); */


                            $admittedCountPhase1 = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 1)->where('rs.last_round_adm_status', 1)
                                ->where('rs.is_active', 0)->count('rs.s_id');

                            $rejectedCountPhase1 = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 2)->where('rs.last_round_adm_status', 1)
                                ->where('rs.is_active', 0)->count('rs.s_id');

                            $admittedCountPhase2 = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 1)->where('rs.last_round_adm_status', 1)
                                ->where('rs.is_active', 1)->count('rs.s_id');

                            $rejectedCountPhase2 = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 2)->where('rs.last_round_adm_status', 1)
                                ->where('rs.is_active', 1)->count('rs.s_id');

                            $lockManualCount = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_active', 1)->where('rs.last_round_adm_status', 1)->count('rs.s_id');

                            $allotedCount = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 1)->where('rs.is_active', 1)->count('rs.s_id');

                            $allotedAcceptCount = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 1)->where('rs.is_allotment_accept', 1)->where('rs.is_active', 1)->count('rs.s_id');

                            $upgradeCount = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 1)->where('rs.is_upgrade_payment', 1)->where('rs.is_active', 1)->count('rs.s_id');


                            $allotedCountCurrent = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 0)->where('rs.is_active', 1)->count('rs.s_id');

                            $allotedAcceptCountCurrent = DB::table('pharmacy_register_student as rs')->where('rs.is_lock_manual', 1)->where('rs.is_alloted', 1)->where('rs.last_round_adm_status', 0)->where('rs.is_allotment_accept', 1)->where('rs.is_active', 1)->count('rs.s_id');

                            $admittedCountCurrent = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 1)->where('rs.last_round_adm_status', 0)
                                ->where('rs.is_active', 1)->count('rs.s_id');

                            $rejectedCountCurrent = DB::table('pharmacy_register_student as rs')->where('rs.is_alloted', 1)->where('rs.s_admited_status', 2)->where('rs.last_round_adm_status', 0)
                                ->where('rs.is_active', 1)->count('rs.s_id');
                        }

                        $data = [
                            'alloted_count' => $allotedCount,
                            'alloted_accept_count' => $allotedAcceptCount,
                            'admitted_count' => $admittedCount,

                            'choice_fillup_count' => $choiceFillupCount,
                            'council_fees_count' => $councilFeesPaidCount,
                            'lock_manual_count' => $lockManualCount,
                            'lock_auto_count' => $lockSubmitAutoCount,
                            'upgrade_count' => $upgradeCount,
                            'rejected_count' => $rejectedCount,

                            'admitted_count_p1' => $admittedCountPhase1,
                            'rejected_count_p1' => $rejectedCountPhase1,

                            'admitted_count_p2' => $admittedCountPhase2,
                            'rejected_count_p2' => $rejectedCountPhase2,

                            'alloted_count_current' => $allotedCountCurrent,
                            'alloted_accept_count_current' => $allotedAcceptCountCurrent,
                            'admitted_count_current' => $admittedCountCurrent,
                            'rejected_count_current' => $rejectedCountCurrent,

                        ];

                        //dd($data);
                        if (sizeof($data) > 0) {
                            $reponse = array(
                                'error'     =>  false,
                                'message'   =>  'Count found',
                                'countList'   =>  $data
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

    public function countDashboardCards(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')
                    ->where('u_id', $user_id)
                    ->first();

                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')
                    ->where('rp_role_id', $user_data->u_role_id)
                    ->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('card-count', $url_data)) {
                        $total_payment = PaymentTransaction::select('pmnt_pay_type', 'trans_status', 'trans_amount')
                            ->where([
                                'pmnt_pay_type' => 'COUNSELLINGFEES',
                                'trans_status' => 'SUCCESS'
                            ])->whereNotNull('trans_id')
                            ->sum('trans_amount');

                        $student = RegisterStudent::where('is_active', 1);

                        $applied_students = $student->clone()->count();
                        $profile_updated = $student->clone()->where('is_profile_updated', 1);
                        $choice_fillup = $student->clone()->where([
                            'is_choice_fill_up' => 1,
                            'is_lock_manual' => 1,
                        ]);
                        $total_amount = $choice_fillup->clone()->where('is_payment', 1);
                        $pdf_downloaded = $student->clone()->where('is_choice_downloaded', 1);
                        $allotment_count = $student->clone()->where([
                            's_inst_code' => $user_data->u_inst_code,
                            'is_lock_manual' => 1,
                            'is_alloted' => 1,
                        ]);
                        $alloment_accept_count = $allotment_count->clone()->where([
                            'is_allotment_accept' => 1
                        ]);
                        $alloment_admitted_count = $alloment_accept_count->clone()->where([
                            's_admited_status' => 1
                        ]);

                        $private_admission = MgmtStudent::where([
                            'is_admission_payment' => 1,
                            'is_active' => 1,
                        ]);

                        $spot_admission = SpotStudent::where([
                            'is_spot_alloted' => 1,
                            'is_spot_confirmed' => 1,
                            'is_spot_inst_paid' => 1,
                            'is_active' => 1,
                        ]);

                        $counseling_admission = RegisterStudent::where('s_admited_status', 1);

                        $self_count = $private_admission->clone()->where('seat_type', 'SELF');
                        $management_count = $private_admission->clone()->where('seat_type', 'MANAGEMENT');
                        $spot_count = $spot_admission->clone();
                        $counseling_count = $counseling_admission->clone();

                        $inst_self_count = $self_count->clone()->where('s_inst_code', $request->inst_code);
                        $inst_management_count = $management_count->clone()->where('s_inst_code', $request->inst_code);
                        $inst_spot_count = $spot_admission->clone()->where('spot_inst_code', $request->inst_code);
                        $inst_counseling_count = $counseling_admission->clone()->where('s_inst_code', $request->inst_code);

                        $choice_fillup_count = $choice_fillup->count();
                        $total_amount_count = $total_amount->count();
                        $total_payment_count = (int)$total_payment;
                        $self_count = $self_count->count();
                        $management_count = $management_count->count();
                        $spot_count = $spot_count->count();
                        $counseling_count = $counseling_count->count();

                        $allotment_count = $allotment_count->count();
                        $alloted_accept_count = $alloment_accept_count->count();
                        $admitted_count = $alloment_admitted_count->count();
                        $inst_self_count = $inst_self_count->count();
                        $inst_management_count =  $inst_management_count->count();
                        $inst_spot_count = $inst_spot_count->count();
                        $inst_counseling_count = $inst_counseling_count->count();

                        return response()->json([
                            'error' =>  false,
                            'message' => 'Dasboard Data found',
                            'countList' => [
                                'applied_students' => $applied_students,
                                'choice_fillup' => $choice_fillup_count,
                                'total_amount' => $total_amount_count,
                                'total_payment' => $total_payment_count,
                                'self_count' => $self_count,
                                'management_count' => $management_count,
                                'spot_count' => $spot_count,
                                'counseling_count' => $counseling_count,
                                'total_count' => $self_count + $management_count + $spot_count + $counseling_count,

                                'alloted_count' => $allotment_count,
                                'alloted_accept_count' => $alloted_accept_count,
                                'admitted_count' => $admitted_count,
                                'inst_self_count' => $inst_self_count,
                                'inst_management_count' => $inst_management_count,
                                'inst_spot_count' => $inst_spot_count,
                                'inst_counseling_count' => $inst_counseling_count,
                                'inst_total_count' => $inst_self_count + $inst_management_count + $inst_spot_count + $inst_counseling_count,
                            ]
                        ]);
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
