<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Token;
use App\Models\Institute;
use App\Models\SuperUser;
use Illuminate\Http\Request;
use App\Models\StudentChoice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ReportController extends Controller
{
    protected $auth;
    public $back_url = null;

    //get profile registered
    public function getProfileRegister(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $timestamp = date('YmdHis');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/profile-register', $url_data)) { //check url has permission or not
                        try {
                            $fileName = 'profile_register_' . $timestamp . '.csv';
                            $filePath = 'exports/' . $fileName;

                            // Ensure the 'exports' directory exists
                            Storage::disk('public')->makeDirectory('exports');

                            $file = Storage::disk('public')->path($filePath);
                            $handle = fopen($file, 'w+');

                            // Add your column headers
                            fputcsv($handle, ['Application Number', 'Candidate Name', 'Father Name', 'Mother Name', 'DoB', 'Aadhaar Number', 'Phone', 'Email', 'Gender', 'Religion', 'Caste', 'TFW Status', 'EWS Status', 'LLQ Status', 'ExSM Status', 'PWD Status', 'Gen Rank', 'SC Rank', 'ST Rank', 'OBC-A Rank', 'OBC-B Rank', 'TFW Rank', 'EWS Rank', 'LLQ Rank', 'ExSM Rank', 'PWD Rank', 'Photo', 'Home District', 'Schooling District', 'State', 'Lock Manual Status', 'Counselling Payment Status', 'Allotment Status', 'Allotment Accept Status', 'Allotment Upgrade Status', 'Upgrade Payment Status', 'Admitted Status', 'Remarks', 'Choice Count']);

                            DB::table('jexpo_register_student_mv')->orderBy('gen_rank', 'ASC')->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->application_number,
                                        $row->candidate_name,
                                        $row->father_name,
                                        $row->mother_name,
                                        formatDate($row->dob),
                                        decryptHEXFormat($row->aadhaar_num),
                                        $row->phone,
                                        $row->email,
                                        $row->gender,
                                        $row->religion,
                                        $row->caste,
                                        $row->tfw_status,
                                        $row->ews_status,
                                        $row->llq_status,
                                        $row->exsm_status,
                                        $row->pwd_status,
                                        $row->gen_rank,
                                        $row->sc_rank,
                                        $row->st_rank,
                                        $row->obca_rank,
                                        $row->obcb_rank,
                                        $row->tfw_rank,
                                        $row->ews_rank,
                                        $row->llq_rank,
                                        $row->exsm_rank,
                                        $row->pwd_rank,
                                        $row->photo,
                                        $row->home_district,
                                        $row->schooling_district,
                                        $row->state,
                                        $row->lock_manual_status,
                                        $row->counselling_payment_status,
                                        $row->allotment_status,
                                        $row->allotment_accept_status,
                                        $row->allotment_upgrade_status,
                                        $row->upgrade_payment_status,
                                        $row->admitted_status,
                                        $row->remarks,
                                        $row->choice_count,
                                    ]);
                                }
                            });

                            fclose($handle);
                            $fileUrl = Storage::url($filePath);

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                //'count'         =>  sizeof($candidates),
                                'file_url'      =>  $fileUrl
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
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
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //get profile update or not
    public function getProfileUpdate(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $timestamp = date('YmdHis');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/profile-update', $url_data)) { //check url has permission or not
                        try {
                            $fileName = 'profile_update_' . $timestamp . '.csv';
                            $filePath = 'exports/' . $fileName;

                            // Ensure the 'exports' directory exists
                            Storage::disk('public')->makeDirectory('exports');

                            $file = Storage::disk('public')->path($filePath);
                            $handle = fopen($file, 'w+');

                            // Add your column headers
                            fputcsv($handle, ['Application Number', 'Candidate Name', 'Father Name', 'Mother Name', 'DoB', 'Aadhaar Number', 'Phone', 'Email', 'Gender', 'Religion', 'Caste', 'TFW Status', 'EWS Status', 'LLQ Status', 'ExSM Status', 'PWD Status', 'Gen Rank', 'SC Rank', 'ST Rank', 'OBC-A Rank', 'OBC-B Rank', 'TFW Rank', 'EWS Rank', 'LLQ Rank', 'ExSM Rank', 'PWD Rank', 'Photo', 'Home District', 'Schooling District', 'State', 'Lock Manual Status', 'Counselling Payment Status', 'Allotment Status', 'Allotment Accept Status', 'Allotment Upgrade Status', 'Upgrade Payment Status', 'Admitted Status', 'Remarks', 'Choice Count']);

                            DB::table('jexpo_register_student_mv')->where('home_district', '<>', '')->orderBy('gen_rank', 'ASC')->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->application_number,
                                        $row->candidate_name,
                                        $row->father_name,
                                        $row->mother_name,
                                        formatDate($row->dob),
                                        decryptHEXFormat($row->aadhaar_num),
                                        $row->phone,
                                        $row->email,
                                        $row->gender,
                                        $row->religion,
                                        $row->caste,
                                        $row->tfw_status,
                                        $row->ews_status,
                                        $row->llq_status,
                                        $row->exsm_status,
                                        $row->pwd_status,
                                        $row->gen_rank,
                                        $row->sc_rank,
                                        $row->st_rank,
                                        $row->obca_rank,
                                        $row->obcb_rank,
                                        $row->tfw_rank,
                                        $row->ews_rank,
                                        $row->llq_rank,
                                        $row->exsm_rank,
                                        $row->pwd_rank,
                                        $row->photo,
                                        $row->home_district,
                                        $row->schooling_district,
                                        $row->state,
                                        $row->lock_manual_status,
                                        $row->counselling_payment_status,
                                        $row->allotment_status,
                                        $row->allotment_accept_status,
                                        $row->allotment_upgrade_status,
                                        $row->upgrade_payment_status,
                                        $row->admitted_status,
                                        $row->remarks,
                                        $row->choice_count,
                                    ]);
                                }
                            });

                            fclose($handle);
                            $fileUrl = Storage::url($filePath);

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'file_url'      =>  $fileUrl
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
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
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //get choice fillup
    public function getProfileChoiceFillup(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $timestamp = date('YmdHis');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/choice-fillup', $url_data)) { //check url has permission or not
                        try {
                            $fileName = 'choice_fillup_' . $timestamp . '.csv';
                            $filePath = 'exports/' . $fileName;

                            // Ensure the 'exports' directory exists
                            Storage::disk('public')->makeDirectory('exports');

                            $file = Storage::disk('public')->path($filePath);
                            $handle = fopen($file, 'w+');

                            // Add your column headers
                            fputcsv($handle, ['Application Number', 'Candidate Name', 'Father Name', 'Mother Name', 'DoB', 'Aadhaar Number', 'Phone', 'Email', 'Gender', 'Religion', 'Caste', 'TFW Status', 'EWS Status', 'LLQ Status', 'ExSM Status', 'PWD Status', 'Gen Rank', 'SC Rank', 'ST Rank', 'OBC-A Rank', 'OBC-B Rank', 'TFW Rank', 'EWS Rank', 'LLQ Rank', 'ExSM Rank', 'PWD Rank', 'Photo', 'Home District', 'Schooling District', 'State', 'Lock Manual Status', 'Counselling Payment Status', 'Allotment Status', 'Allotment Accept Status', 'Allotment Upgrade Status', 'Upgrade Payment Status', 'Admitted Status', 'Remarks', 'Choice Count']);

                            DB::table('jexpo_register_student_mv')->where('choice_count', '>', 0)->orderBy('gen_rank', 'ASC')->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->application_number,
                                        $row->candidate_name,
                                        $row->father_name,
                                        $row->mother_name,
                                        formatDate($row->dob),
                                        decryptHEXFormat($row->aadhaar_num),
                                        $row->phone,
                                        $row->email,
                                        $row->gender,
                                        $row->religion,
                                        $row->caste,
                                        $row->tfw_status,
                                        $row->ews_status,
                                        $row->llq_status,
                                        $row->exsm_status,
                                        $row->pwd_status,
                                        $row->gen_rank,
                                        $row->sc_rank,
                                        $row->st_rank,
                                        $row->obca_rank,
                                        $row->obcb_rank,
                                        $row->tfw_rank,
                                        $row->ews_rank,
                                        $row->llq_rank,
                                        $row->exsm_rank,
                                        $row->pwd_rank,
                                        $row->photo,
                                        $row->home_district,
                                        $row->schooling_district,
                                        $row->state,
                                        $row->lock_manual_status,
                                        $row->counselling_payment_status,
                                        $row->allotment_status,
                                        $row->allotment_accept_status,
                                        $row->allotment_upgrade_status,
                                        $row->upgrade_payment_status,
                                        $row->admitted_status,
                                        $row->remarks,
                                        $row->choice_count,
                                    ]);
                                }
                            });

                            fclose($handle);
                            $fileUrl = Storage::url($filePath);

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'file_url'      =>  $fileUrl
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
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
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //get choice fillup lock
    public function getProfileChoiceFillupLock(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $timestamp = date('YmdHis');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/choice-fillup-lock', $url_data)) { //check url has permission or not
                        try {
                            $fileName = 'choice_lock_' . $timestamp . '.csv';
                            $filePath = 'exports/' . $fileName;

                            // Ensure the 'exports' directory exists
                            Storage::disk('public')->makeDirectory('exports');

                            $file = Storage::disk('public')->path($filePath);
                            $handle = fopen($file, 'w+');

                            // Add your column headers
                            fputcsv($handle, ['Application Number', 'Candidate Name', 'Father Name', 'Mother Name', 'DoB', 'Aadhaar Number', 'Phone', 'Email', 'Gender', 'Religion', 'Caste', 'TFW Status', 'EWS Status', 'LLQ Status', 'ExSM Status', 'PWD Status', 'Gen Rank', 'SC Rank', 'ST Rank', 'OBC-A Rank', 'OBC-B Rank', 'TFW Rank', 'EWS Rank', 'LLQ Rank', 'ExSM Rank', 'PWD Rank', 'Photo', 'Home District', 'Schooling District', 'State', 'Lock Manual Status', 'Counselling Payment Status', 'Allotment Status', 'Allotment Accept Status', 'Allotment Upgrade Status', 'Upgrade Payment Status', 'Admitted Status', 'Remarks', 'Choice Count']);

                            DB::table('jexpo_register_student_mv')->where('choice_count', '>', 0)->whereIn('lock_manual_status', array('YES', 'NO'))->orderBy('lock_manual_status', 'DESC')->orderBy('gen_rank', 'ASC')->chunk(1000, function ($rows) use ($handle) {
                                foreach ($rows as $row) {
                                    fputcsv($handle, [
                                        $row->application_number,
                                        $row->candidate_name,
                                        $row->father_name,
                                        $row->mother_name,
                                        formatDate($row->dob),
                                        decryptHEXFormat($row->aadhaar_num),
                                        $row->phone,
                                        $row->email,
                                        $row->gender,
                                        $row->religion,
                                        $row->caste,
                                        $row->tfw_status,
                                        $row->ews_status,
                                        $row->llq_status,
                                        $row->exsm_status,
                                        $row->pwd_status,
                                        $row->gen_rank,
                                        $row->sc_rank,
                                        $row->st_rank,
                                        $row->obca_rank,
                                        $row->obcb_rank,
                                        $row->tfw_rank,
                                        $row->ews_rank,
                                        $row->llq_rank,
                                        $row->exsm_rank,
                                        $row->pwd_rank,
                                        $row->photo,
                                        $row->home_district,
                                        $row->schooling_district,
                                        $row->state,
                                        $row->lock_manual_status,
                                        $row->counselling_payment_status,
                                        $row->allotment_status,
                                        $row->allotment_accept_status,
                                        $row->allotment_upgrade_status,
                                        $row->upgrade_payment_status,
                                        $row->admitted_status,
                                        $row->remarks,
                                        $row->choice_count,
                                    ]);
                                }
                            });

                            fclose($handle);
                            $fileUrl = Storage::url($filePath);

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'file_url'      =>  $fileUrl
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
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
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //get choice fillup payment
    public function getProfileChoiceFillupPayment(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $timestamp = date('YmdHis');

            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();

            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/choice-fillup-payment', $url_data)) { //check url has permission or not
                        try {
                            $fileName = 'choice_payment_' . $timestamp . '.csv';
                            $filePath = 'exports/' . $fileName;

                            // Ensure the 'exports' directory exists
                            Storage::disk('public')->makeDirectory('exports');

                            $file = Storage::disk('public')->path($filePath);
                            $handle = fopen($file, 'w+');

                            // Add your column headers
                            fputcsv($handle, ['Application Number', 'Candidate Name', 'Father Name', 'Mother Name', 'DoB', 'Aadhaar Number', 'Phone', 'Email', 'Gender', 'Religion', 'Caste', 'TFW Status', 'EWS Status', 'LLQ Status', 'ExSM Status', 'PWD Status', 'Gen Rank', 'SC Rank', 'ST Rank', 'OBC-A Rank', 'OBC-B Rank', 'TFW Rank', 'EWS Rank', 'LLQ Rank', 'ExSM Rank', 'PWD Rank', 'Photo', 'Home District', 'Schooling District', 'State', 'Lock Manual Status', 'Counselling Payment Status', 'Allotment Status', 'Allotment Accept Status', 'Allotment Upgrade Status', 'Upgrade Payment Status', 'Admitted Status', 'Remarks', 'Choice Count']);

                            DB::table('jexpo_register_student_mv')->where('choice_count', '>', 0)->where('lock_manual_status', 'YES')
                                ->whereIn('counselling_payment_status', array('YES', 'NO'))
                                ->orderBy('counselling_payment_status', 'DESC')->orderBy('gen_rank', 'ASC')->chunk(1000, function ($rows) use ($handle) {
                                    foreach ($rows as $row) {
                                        fputcsv($handle, [
                                            $row->application_number,
                                            $row->candidate_name,
                                            $row->father_name,
                                            $row->mother_name,
                                            formatDate($row->dob),
                                            decryptHEXFormat($row->aadhaar_num),
                                            $row->phone,
                                            $row->email,
                                            $row->gender,
                                            $row->religion,
                                            $row->caste,
                                            $row->tfw_status,
                                            $row->ews_status,
                                            $row->llq_status,
                                            $row->exsm_status,
                                            $row->pwd_status,
                                            $row->gen_rank,
                                            $row->sc_rank,
                                            $row->st_rank,
                                            $row->obca_rank,
                                            $row->obcb_rank,
                                            $row->tfw_rank,
                                            $row->ews_rank,
                                            $row->llq_rank,
                                            $row->exsm_rank,
                                            $row->pwd_rank,
                                            $row->photo,
                                            $row->home_district,
                                            $row->schooling_district,
                                            $row->state,
                                            $row->lock_manual_status,
                                            $row->counselling_payment_status,
                                            $row->allotment_status,
                                            $row->allotment_accept_status,
                                            $row->allotment_upgrade_status,
                                            $row->upgrade_payment_status,
                                            $row->admitted_status,
                                            $row->remarks,
                                            $row->choice_count,
                                        ]);
                                    }
                                });

                            fclose($handle);
                            $fileUrl = Storage::url($filePath);

                            $reponse = array(
                                'error'         =>  false,
                                'message'       =>  'Data found',
                                'file_url'      =>  str_replace("exports", "app/public/exports", $fileUrl)
                            );
                            return response(json_encode($reponse), 200);
                        } catch (Exception $e) {
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
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //get allotment received
    public function getProfileAllotment(Request $request, $college_code = null)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('reports/allotment', $url_data)) { //check url has permission or not

                        /* // First query
                        $query1 = DB::table('jexpo_register_student')
                            ->select('s_id')
                            ->where('is_alloted', 1)
                            ->where('last_round_adm_status', 1);
                            //->where('s_inst_code', 'APC');

                        // Second query
                        $query2 = DB::table('jexpo_register_student AS rs')
                            ->join('jexpo_choice_student AS cs', 'cs.ch_stu_id', '=', 'rs.s_id')
                            ->select('rs.s_id')
                            ->where('rs.is_alloted', 1)
                            ->where('rs.last_round_adm_status', 0)
                            ->where('cs.is_alloted', 1);
                            //->where('cs.ch_inst_code', 'APC');

                        // Combine queries with UNION
                        $combinedQuery = $query1->union($query2)
                            ->orderBy('s_id', 'asc')
                            ->get();

                        $id_arr = $combinedQuery->pluck('s_id')->toArray();
                        //print_r($id_arr); exit(); */

                        $results = DB::table('pharmacy_register_student as rs')
                            ->select([
                                'rs.s_id as student_id',
                                'rs.s_appl_form_num as appl_num',
                                'rs.s_candidate_name as candidate_name',
                                'rs.s_father_name as father_name',
                                'rs.s_mother_name as mother_name',
                                'rs.s_phone as phone',
                                'rs.s_email as email',
                                'rs.s_aadhar_no as aadhaar',
                                'rs.s_dob as dob',
                                'rs.s_gender as gender',
                                'rs.s_religion as religion',
                                'rs.s_caste as caste',
                                DB::raw("CASE WHEN rs.s_tfw = 1 THEN 'YES' ELSE 'NO' END as tfw_status"),
                                DB::raw("CASE WHEN rs.s_ews = 1 THEN 'YES' ELSE 'NO' END as ews_status"),
                                DB::raw("CASE WHEN rs.s_llq = 1 THEN 'YES' ELSE 'NO' END as llq_status"),
                                DB::raw("CASE WHEN rs.s_exsm = 1 THEN 'YES' ELSE 'NO' END as exsm_status"),
                                DB::raw("CASE WHEN rs.s_pwd = 1 THEN 'YES' ELSE 'NO' END as pwd_status"),
                                'rs.s_gen_rank as gen_rank',
                                //'hd.d_name as home_district',
                                //'sd.d_name as schooling_district',
                                //'sm.state_name as state',
                                DB::raw("CASE WHEN rs.is_lock_manual = 1 THEN 'YES' ELSE 'NO' END as lock_manual_status"),
                                DB::raw("CASE WHEN rs.is_payment = 1 THEN 'YES' ELSE 'NO' END as counselling_payment_status"),
                                DB::raw("CASE WHEN rs.is_alloted = 1 THEN 'YES' ELSE 'NO' END as allotment_status"),
                                DB::raw("CASE WHEN rs.is_allotment_accept = 1 THEN 'YES' ELSE 'NO' END as allotment_accept_status"),
                                DB::raw("CASE WHEN rs.is_upgrade = 1 THEN 'YES' ELSE 'NO' END as allotment_upgrade_status"),
                                DB::raw("CASE WHEN rs.is_upgrade_payment = 1 THEN 'YES' ELSE 'NO' END as upgrade_payment_status"),
                                DB::raw("CASE
                                        WHEN rs.s_admited_status = 1 THEN 'YES'
                                        WHEN rs.s_admited_status = 2 THEN 'NO'
                                    ELSE ''
                                    END as admitted_status"),
                                'rs.s_inst_code as institute_code',
                                'institute_master.i_name as institute_name',
                                //'rs.s_trade_code as trade_code',
                                //'trade_master.t_name as trade_name',
                                'rs.s_alloted_category as allotment_category',
                                'rs.s_alloted_round as allotment_round'
                            ])
                            //->leftJoin('district_master as hd', 'hd.d_id', '=', 'rs.s_home_district')
                            //->leftJoin('district_master as sd', 'sd.d_id', '=', 'rs.s_home_district')
                            //->leftJoin('jexpo_state_master as sm', 'sm.state_id_pk', '=', 'rs.s_state_id')
                            ->leftJoin('institute_master', 'institute_master.i_code', '=', 'rs.s_inst_code')
                            //->leftJoin('trade_master', 'trade_master.t_code', '=', 'rs.s_trade_code')
                            ->where('rs.is_alloted', 1)
                            ->where('last_round_adm_status', 1);

                        $current_round_ids = DB::table('pharmacy_register_student')->select('s_id')->where('last_round_adm_status', 0)->where('is_alloted', 1)->orderBy('s_id')->get()->pluck('s_id')->toArray();

                        $results_new = DB::table('pharmacy_register_student as rs')
                            ->select([
                                'rs.s_id as student_id',
                                'rs.s_appl_form_num as appl_num',
                                'rs.s_candidate_name as candidate_name',
                                'rs.s_father_name as father_name',
                                'rs.s_mother_name as mother_name',
                                'rs.s_phone as phone',
                                'rs.s_email as email',
                                'rs.s_aadhar_no as aadhaar',
                                'rs.s_dob as dob',
                                'rs.s_gender as gender',
                                'rs.s_religion as religion',
                                'rs.s_caste as caste',
                                DB::raw("CASE WHEN rs.s_tfw = 1 THEN 'YES' ELSE 'NO' END as tfw_status"),
                                DB::raw("CASE WHEN rs.s_ews = 1 THEN 'YES' ELSE 'NO' END as ews_status"),
                                DB::raw("CASE WHEN rs.s_llq = 1 THEN 'YES' ELSE 'NO' END as llq_status"),
                                DB::raw("CASE WHEN rs.s_exsm = 1 THEN 'YES' ELSE 'NO' END as exsm_status"),
                                DB::raw("CASE WHEN rs.s_pwd = 1 THEN 'YES' ELSE 'NO' END as pwd_status"),
                                'rs.s_gen_rank as gen_rank',
                                //'hd.d_name as home_district',
                                //'sd.d_name as schooling_district',
                                //'sm.state_name as state',
                                DB::raw("CASE WHEN rs.is_lock_manual = 1 THEN 'YES' ELSE 'NO' END as lock_manual_status"),
                                DB::raw("CASE WHEN rs.is_payment = 1 THEN 'YES' ELSE 'NO' END as counselling_payment_status"),
                                DB::raw("CASE WHEN rs.is_alloted = 1 THEN 'YES' ELSE 'NO' END as allotment_status"),
                                DB::raw("CASE WHEN rs.is_allotment_accept = 1 THEN 'YES' ELSE 'NO' END as allotment_accept_status"),
                                DB::raw("CASE WHEN rs.is_upgrade = 1 THEN 'YES' ELSE 'NO' END as allotment_upgrade_status"),
                                DB::raw("CASE WHEN rs.is_upgrade_payment = 1 THEN 'YES' ELSE 'NO' END as upgrade_payment_status"),
                                DB::raw("CASE
                                        WHEN rs.s_admited_status = 1 THEN 'YES'
                                        WHEN rs.s_admited_status = 2 THEN 'NO'
                                    ELSE ''
                                    END as admitted_status"),
                                'cs.ch_inst_code as institute_code',
                                'institute_master.i_name as institute_name',
                                //'cs.ch_trade_code as trade_code',
                                //'trade_master.t_name as trade_name',
                                'cs.ch_alloted_category as allotment_category',
                                'cs.ch_alloted_round as allotment_round'
                            ])
                            ->join('pharmacy_choice_student AS cs', 'cs.ch_stu_id', '=', 'rs.s_id')
                            //->leftJoin('district_master as hd', 'hd.d_id', '=', 'rs.s_home_district')
                            //->leftJoin('district_master as sd', 'sd.d_id', '=', 'rs.s_home_district')
                            //->leftJoin('jexpo_state_master as sm', 'sm.state_id_pk', '=', 'rs.s_state_id')
                            ->leftJoin('institute_master', 'institute_master.i_code', '=', 'cs.ch_inst_code')
                            //->leftJoin('trade_master', 'trade_master.t_code', '=', 'cs.ch_trade_code')
                            ->whereIn('cs.ch_stu_id', $current_round_ids)
                            ->where('cs.is_alloted', 1)
                            ->where('ch_fillup_time', '>', '2024-07-14 10:00:00');


                        if ($college_code == null) {
                            if (isset($request->inst_code) && ($request->inst_code != "")) {
                                $results->where('s_inst_code', $request->inst_code);
                                $results_new->where('ch_inst_code', $request->inst_code);
                            }
                        } else {
                            $results->where('s_inst_code', $college_code);
                            $results_new->where('ch_inst_code', $college_code);
                        }

                        // if (isset($request->trade_code) && ($request->trade_code != "")) {
                        //     $results->where('s_trade_code', $request->trade_code);
                        //     $results_new->where('ch_trade_code', $request->trade_code);
                        // }
                        if (isset($request->student_name) && ($request->student_name != "")) {
                            $results->where('s_candidate_name', 'LIKE', '%' . $request->student_name . '%');
                            $results_new->where('s_candidate_name', 'LIKE', '%' . $request->student_name . '%');
                        }

                        if (isset($request->student_phone) && ($request->student_phone != "")) {
                            $results->where('s_phone', $request->student_phone);
                            $results_new->where('s_phone', $request->student_phone);
                        }

                        $results = $results->get();
                        $results_new = $results_new->get();

                        $data = $results_new->merge($results);
                        //return $data;

                        $reponse = array(
                            'error'         =>  false,
                            'total'         =>  count($data),
                            'message'       =>  'Data found',
                            'candidates'    =>  $data
                        );
                        return response(json_encode($reponse), 200);
                    } else {
                        return response()->json([
                            'error'     =>  true,
                            'message'   =>   "1 Oops! you don't have sufficient permission"
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'error'     =>  true,
                        'message'   =>   "2 Oops! you don't have sufficient permission"
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

    //Student choice list
    public function profileNotUpdated(Request $request)
    {
        if ($request->header('token')) {
            $now    =   date('Y-m-d H:i:s');
            $token_check = Token::where('t_token', '=', $request->header('token'))->where('t_expired_on', '>=', $now)->first();
            if ($token_check) {  // check the token is expire or not
                $user_id = $token_check->t_user_id;
                $user_data = SuperUser::select('u_id', 'u_role_id', 'u_inst_code', 'u_inst_name')->where('u_id', $user_id)->first();
                $role_url_access_id = DB::table('pharmacy_auth_roles_permissions')->where('rp_role_id', $user_data->u_role_id)->pluck('rp_url_id');

                if (sizeof($role_url_access_id) > 0) {
                    $urls = DB::table('pharmacy_auth_urls')->where('url_visible', 1)->whereIn('url_id', $role_url_access_id)->get()->toArray();
                    $url_data = array_column($urls, 'url_name');

                    if (in_array('choice-list', $url_data)) { //check url has permission or not
                        $choice_res = null;
                        $choice_list = StudentChoice::where('ch_stu_id',  $user_id)->with('student')->orderBy('ch_pref_no', 'ASC')->get();

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


    // admission data
    public function admissionData()
    {
        $list = Institute::select('i_code', 'i_name')
            ->where('is_active', 1)
            ->withCount([
                'enrollments' => function (Builder $query) {
                    $query->where('s_admited_status', 1);
                },
                'selfs' => function (Builder $query) {
                    $query->where('is_admission_payment', 1);
                },
                'managements' => function (Builder $query) {
                    $query->where('is_admission_payment', 1);
                },
                'spots' => function (Builder $query) {
                    $query->where('is_spot_inst_paid', 1);
                },
            ])->get()
            ->map(function ($value) {
                return [
                    'inst_code' => $value->i_code,
                    'inst_name' => $value->i_name,
                    'counseling_count' => $value->enrollments_count,
                    'self_count' => $value->selfs_count,
                    'management_count' => $value->managements_count,
                    'spot_count' => $value->spots_count,
                    'total_count' => $value->enrollments_count + $value->selfs_count + $value->managements_count + $value->spots_count
                ];
            });

        if (count($list)) {
            return response()->json([
                'error' => false,
                'message' => 'Data found',
                'list' => $list
            ]);
        } else {
            return response()->json([
                'error' => false,
                'message' => 'No Data Found'
            ]);
        }
    }
}
