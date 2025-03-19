<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\PaymentLib\AESEncDec;
use App\Models\SuperUser;

class OtherController extends Controller
{


    //Payment
    public function payment()
    {
        //1001954 || 1000605
        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid = $orderid . $d;
        }
        $base_url = env('APP_URL') . '/payment/';
        $success_url = $base_url . 'success';
        //echo $success_url;
        $fail_url = $base_url . 'fail';
        $key = "IlzvLopkj/XEopyGTmrJPvNGu2v/NwWFX0qo2F2U1uA=";
        $other            =    "OD";
        $marid =  '5';
        $merchant_order_num = $orderid;
        $total_amount = 1.00;
        $requestParameter  = "1001954|DOM|IN|INR|" . $total_amount . "|" . $other . "|" . $success_url . "|" . $fail_url . "|SBIEPAY|" . $merchant_order_num . "|" . $marid . "|NB|ONLINE|ONLINE";

        // 1000605|DOM|IN|INR|500|Other|https://council.aranax.tech/diploma/services/public/payment/success|https://council.aranax.tech/diploma/services/public/payment/fail|SBIEPAY|A8ZJ40X3LB|2|NB|ONLINE|ONLINE

        $aes =  new AESEncDec();
        $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($EncryptTrans, $key);
        // echo $EncryptTrans . '=====' . $decrypt;
        // die();

        $merchIdVal = '1001954';
        return view('test', compact('EncryptTrans', 'merchIdVal'));
    }
    public function paymentSuccess(Request $request)
    {
        $key = "IlzvLopkj/XEopyGTmrJPvNGu2v/NwWFX0qo2F2U1uA=";
        //dd($request->all());
        $aes =  new AESEncDec();
        // $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($request->encData, $key);
        dd($decrypt);
    }

    public function paymentFail(Request $request)
    {
        $key = "IlzvLopkj/XEopyGTmrJPvNGu2v/NwWFX0qo2F2U1uA=";
        //dd($request->all());
        $aes =  new AESEncDec();
        // $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $decrypt = $aes->decrypt($request->encData, $key);
        dd($decrypt);
    }

    public function usersMasterDataInsert()
    {
        $instData = DB::table('institute_master')->select('i_code', 'i_name')->get();

        if (sizeof($instData) > 0) {
            foreach ($instData as $row) {
                $data[] = [
                    'u_inst_code' => $row->i_code,
                    'u_inst_name' => $row->i_name,
                    'u_username' => $row->i_code,
                    'u_password' => hash("sha512", $row->i_code),
                    'u_role_id' => 3,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_default_password' => 1
                ];
            }

            SuperUser::insert($data);
        } else {
            echo 'No data found';
        }
    }
}
