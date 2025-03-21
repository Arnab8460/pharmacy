<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MgmtStudent;
use App\Models\SpotStudent;
use Illuminate\Http\Request;
use App\Models\SpotAllotment;
use App\PaymentLib\AESEncDec;
use App\Models\RegisterStudent;
use App\Models\PaymentTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // Payment
    public function payment(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $actionUrl = env('SBI_PAYMENT_API');

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}success";
        $fail_url = "{$base_url}fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "COUNSELLINGFEES_{$request->student_id}";
        $marid = '5';
        $merchant_order_num = $orderid;
        $total_amount = env('COUNSELLING_FEES');
        $requestParameter = "{$merchIdVal}|DOM|IN|INR|$total_amount|$other|$success_url|$fail_url|SBIEPAY|$merchant_order_num|$marid|NB|ONLINE|ONLINE";
        $EncryptTrans = encryptedString($requestParameter, $key);

        PaymentTransaction::create([
            'order_id' => $orderid,
            'pmnt_modified_by' => $request->student_id,
            'pmnt_stud_id' => $request->student_id,
            'pmnt_created_on' => $trans_time,
            'trans_amount' => intval($total_amount),
            'pmnt_pay_type' => 'COUNSELLINGFEES'
        ]);

        auditTrail($request->student_id, "Payment initiated for order ID {$orderid}");
        studentActivite($request->student_id, "Payment initiated for order ID {$orderid}");

        return response()->json([
            'error' => false,
            'message' => 'Payment Data',
            'EncryptTrans' => $EncryptTrans,
            'merchIdVal' => $merchIdVal,
            'actionUrl' => $actionUrl
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $trans_details = $aes->decrypt($request->encData, $key);

        $data = explode('|', $trans_details);

        $order_id        =    $data[0]; // D3UGOGS1D2
        $trans_id        =    $data[1]; // 4984477659512
        $trans_status    =    $data[2]; // SUCCESS
        $trans_amount        =    $data[3]; // 500
        $currency        =    $data[4]; // INR
        $trans_mode        =    $data[5]; // NB
        $stu_data        =    explode('_', $data[6]); // OD
        $trans_time        =    $data[10]; // 2024-05-09 14:41:52
        $country_code        =    $data[11]; // IN
        $marchnt_id        =    $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
            ]);
        }

        $user_id = $stu_data[1];
        $student = RegisterStudent::where('s_id', $user_id)->first();

        auditTrail($user_id, "Payment {$trans_status} for {$student->s_candidate_name} whose order ID is {$order_id}");
        studentActivite($user_id, "Payment {$trans_status} for {$student->s_candidate_name} whose order ID is {$order_id} for counselling fees of amount {$trans_amount}");

        $student->update([
            'updated_at' => now(),
            'is_payment' => 1
        ]);

        return redirect()->route('payment-success-redirect', $trans_id);
    }

    public function paymentFail(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $decrypt = $aes->decrypt($request->encData, $key);
        $data = explode('|', $decrypt);

        // dd($decrypt);

        $user_id = explode('_', $data[6]);
        $order_id        =    $data[0];
        $trans_time = date('Y-m-d H:i:s');

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_details' => $decrypt,
                'trans_time' => $trans_time
            ]);
        }

        $message = "Payment failed for order ID {$order_id}";
        auditTrail($request->student_id, $message);
        studentActivite($request->student_id, $message);

        return redirect()->route('payment-fail', $user_id[1]);
    }

    // Upgrade payment
    public function paymentUpgrade(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}upgrade-success";
        $fail_url = "{$base_url}upgrade-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "COUNSELLINGUPGRADEFEES_{$request->student_id}";
        $marid =  '5';
        $merchant_order_num = $orderid;
        $total_amount = env('COUNSELLING_UPGRADE_FEES');
        $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$merchant_order_num}|{$marid}|NB|ONLINE|ONLINE";

        $aes =  new AESEncDec();
        $EncryptTrans = $aes->encrypt($requestParameter, $key);
        $actionUrl = env('SBI_PAYMENT_API');

        $user_data = User::where('s_id', $request->student_id)->first();

        $user_data->update([
            'updated_at' => now(),
        ]);

        PaymentTransaction::create([
            'order_id' => $orderid,
            'pmnt_modified_by' => $request->student_id,
            'pmnt_stud_id' => $request->student_id,
            'pmnt_created_on' => $trans_time,
            'trans_amount' => intval($total_amount),
            'pmnt_pay_type' => 'COUNSELLINGUPGRADEFEES'
        ]);

        auditTrail($request->student_id, "Payment for upgrade initiated for order ID {$orderid}");
        studentActivite($request->student_id, "Payment for upgrade initiated for order ID {$orderid}");

        return response()->json([
            'error'         =>  false,
            'message'       =>  'Payment Data',
            'EncryptTrans' => $EncryptTrans,
            'merchIdVal' => $merchIdVal,
            'actionUrl' => $actionUrl
        ]);
    }

    public function paymentUpgradeSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $trans_details = $aes->decrypt($request->encData, $key);

        $data = explode('|', $trans_details);

        $order_id        =    $data[0]; // D3UGOGS1D2
        $trans_id        =    $data[1]; // 4984477659512
        $trans_status    =    $data[2]; // SUCCESS
        $trans_amount        =    $data[3]; // 500
        $currency        =    $data[4]; // INR
        $trans_mode        =    $data[5]; // NB
        $stu_data        =    explode('_', $data[6]); // OD
        $trans_time        =    $data[10]; // 2024-05-09 14:41:52
        $country_code        =    $data[11]; // IN
        $marchnt_id        =    $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],

            ]);
        }
        $user_id = $stu_data[1];
        $user_data = User::where('s_id', $user_id)->first();
        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'updated_at' => now(),
            'is_upgrade_payment' => 1
        ]);

        studentActivite($user_id, "Payment for choice upgradation was {$trans_status} for {$student_name} having Order ID {$order_id}");
        auditTrail($user_id, "Payment for choice upgradation was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('upgrade-payment-success-redirect', $trans_id);
    }

    public function paymentUpgradeFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $decrypt = $aes->decrypt($request->encData, $key);
        $data = explode('|', $decrypt);
        $user_id = explode('_', $data[6]);
        $order_id        =    $data[0];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_details' => $decrypt,
                'trans_time' => $trans_time
            ]);
        }

        $message = "Payment failed for order ID {$order_id}";
        auditTrail($request->student_id, $message);
        studentActivite($request->student_id, $message);

        return redirect()->route('upgrade-payment-fail', $user_id[1]);
    }

    // Registration payment
    public function paymentRegistration(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}registration-success";
        $fail_url = "{$base_url}registration-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "COUNSELLINGREGISTRATIONFEES_{$request->student_id}";
        $marid =  '5';
        $merchant_order_num = $orderid;

        $student = RegisterStudent::where('s_id', $request->student_id)->first();

        if ($student) {
            $total_amount = $student->s_gender === 'MALE' ? env('COUNSELLING_MALE_REGISTRATION_FEES') : ($student->s_gender === 'FEMALE' ? env('COUNSELLING_FEMALE_REGISTRATION_FEES') : env('COUNSELLING_MALE_REGISTRATION_FEES'));

            $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$merchant_order_num}|{$marid}|NB|ONLINE|ONLINE";

            $aes =  new AESEncDec();
            $EncryptTrans = $aes->encrypt($requestParameter, $key);
            $actionUrl = env('SBI_PAYMENT_API');

            $student->update([
                'updated_at' => now(),
            ]);

            PaymentTransaction::create([
                'order_id' => $orderid,
                'pmnt_modified_by' => $request->student_id,
                'pmnt_stud_id' => $request->student_id,
                'pmnt_created_on' => $trans_time,
                'trans_amount' => intval($total_amount),
                'pmnt_pay_type' => 'COUNSELLINGREGISTRATIONFEES'
            ]);

            auditTrail($request->student_id, "Payment for registration initiated for order ID {$orderid}");
            studentActivite($request->student_id, "Payment for registration initiated for order ID {$orderid}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data',
                'EncryptTrans' => $EncryptTrans,
                'merchIdVal' => $merchIdVal,
                'actionUrl' => $actionUrl
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student Not Found',
            ]);
        }
    }

    public function paymentRegistrationSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $trans_details = $aes->decrypt($request->encData, $key);

        $data = explode('|', $trans_details);

        $order_id        =    $data[0]; // D3UGOGS1D2
        $trans_id        =    $data[1]; // 4984477659512
        $trans_status    =    $data[2]; // SUCCESS
        $trans_amount        =    $data[3]; // 500
        $currency        =    $data[4]; // INR
        $trans_mode        =    $data[5]; // NB
        $stu_data        =    explode('_', $data[6]); // OD
        $trans_time        =    $data[10]; // 2024-05-09 14:41:52
        $country_code        =    $data[11]; // IN
        $marchnt_id        =    $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],

            ]);
        }

        $user_id = $stu_data[1];
        $user_data = User::where('s_id', $user_id)->first();
        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'updated_at' => now(),
            'is_registration_payment' => 1
        ]);

        studentActivite($user_id, "Payment for registration was {$trans_status} for {$student_name} having Order ID {$order_id}");
        auditTrail($user_id, "Payment for registration was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('registration-payment-success-redirect', $trans_id);
    }

    public function paymentRegistrationFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $decrypt = $aes->decrypt($request->encData, $key);
        $data = explode('|', $decrypt);
        $user_id = explode('_', $data[6]);
        $order_id        =    $data[0];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_details' => $decrypt,
                'trans_time' => $trans_time
            ]);
        }

        $message = "Payment failed for order ID {$order_id}";
        auditTrail($request->student_id, $message);
        studentActivite($request->student_id, $message);

        return redirect()->route('registration-payment-fail', $user_id[1]);
    }

    // Management Admission payment
    public function managementAdmissionPayment(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}management-admission-success";
        $fail_url = "{$base_url}management-admission-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "MANAGEMENTADMISSIONFEES_{$request->student_id}";
        $marid =  '5';

        $student = MgmtStudent::where([
            's_id' => $request->student_id,
            'seat_type' => 'MANAGEMENT',
        ])->first();

        if ($student) {
            $total_amount = $student->s_gender === 'MALE' ? env('MANAGEMENT_MALE_ADMISSION_FEES') : ($student->s_gender === 'FEMALE' ? env('MANAGEMENT_FEMALE_ADMISSION_FEES') : env('MANAGEMENT_MALE_ADMISSION_FEES'));

            $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$orderid}|{$marid}|NB|ONLINE|ONLINE";

            $aes = new AESEncDec();
            $EncryptTrans = $aes->encrypt($requestParameter, $key);
            $actionUrl = env('SBI_PAYMENT_API');

            $student->update([
                'updated_at' => now(),
            ]);

            PaymentTransaction::create([
                'order_id' => $orderid,
                'pmnt_modified_by' => $request->student_id,
                'pmnt_stud_id' => $request->student_id,
                'pmnt_created_on' => $trans_time,
                'trans_amount' => intval($total_amount),
                'pmnt_pay_type' => 'MANAGEMENTADMISSIONFEES'
            ]);

            auditTrail($request->student_id, "Payment for management admission initiated for order ID {$orderid}");
            // studentActivite($request->student_id, "Payment for management admission initiated for order ID {$orderid}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data',
                'EncryptTrans' => $EncryptTrans,
                'merchIdVal' => $merchIdVal,
                'actionUrl' => $actionUrl
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student Not Found',
            ]);
        }
    }

    public function managementAdmissionSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0]; // D3UGOGS1D2
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3]; // 500
        $currency = $data[4]; // INR
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
                'is_double_verify' => 1,
            ]);
        }
        $user_id = $stu_data[1];
        $user_data = MgmtStudent::where([
            's_id' => $user_id,
            'seat_type' => 'MANAGEMENT',
        ])->first();
        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'updated_at' => now(),
            'is_admission_payment' => 1,
            's_admitted_status' => 'ADMITTED AND PAID',
        ]);

        auditTrail($user_id, "Payment for management admission was {$trans_status} for {$student_name} having Order ID {$order_id}");
        // studentActivite($user_id, "Payment for management admission was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('management-admission-payment-success-redirect', $trans_id);
    }

    public function managementAdmissionFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0];
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3];
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $user_id = $stu_data[1];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'pmnt_modified_by' => $user_id,
                'is_double_verify' => 1,
            ]);
        }

        auditTrail($user_id, "Payment for management admission failed for order ID {$order_id}");
        // studentActivite($user_id, "Payment for management admission failed for order ID {$order_id}");

        return redirect()->route('self-admission-payment-fail', [
            'user_id' => $user_id,
            'amount' => $trans_amount,
        ]);
    }

    // self Admission payment
    public function selfAdmissionPayment(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}self-admission-success";
        $fail_url = "{$base_url}self-admission-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "SELFADMISSIONFEES_{$request->student_id}";
        $marid =  '5';

        $student = MgmtStudent::where([
            's_id' => $request->student_id,
            'seat_type' => 'SELF',
        ])->first();

        if ($student) {
            $total_amount = $student->s_gender === 'MALE' ? (env('SELF_ADMISSION_FEES') + env('MANAGEMENT_MALE_ADMISSION_FEES')) : ($student->s_gender === 'FEMALE' ? (env('SELF_ADMISSION_FEES') + env('MANAGEMENT_FEMALE_ADMISSION_FEES')) : (env('SELF_ADMISSION_FEES') + env('MANAGEMENT_MALE_ADMISSION_FEES')));

            $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$orderid}|{$marid}|NB|ONLINE|ONLINE";

            $aes = new AESEncDec();
            $EncryptTrans = $aes->encrypt($requestParameter, $key);
            $actionUrl = env('SBI_PAYMENT_API');

            $student->update([
                'updated_at' => now(),
            ]);

            PaymentTransaction::create([
                'order_id' => $orderid,
                'pmnt_modified_by' => $request->student_id,
                'pmnt_stud_id' => $request->student_id,
                'pmnt_created_on' => $trans_time,
                'trans_amount' => intval($total_amount),
                'pmnt_pay_type' => 'SELFADMISSIONFEES'
            ]);

            auditTrail($request->student_id, "Payment for self admission initiated for order ID {$orderid}");
            // studentActivite($request->student_id, "Payment for self admission initiated for order ID {$orderid}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data',
                'EncryptTrans' => $EncryptTrans,
                'merchIdVal' => $merchIdVal,
                'actionUrl' => $actionUrl
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student Not Found',
            ]);
        }
    }

    public function selfAdmissionSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0]; // D3UGOGS1D2
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3]; // 500
        $currency = $data[4]; // INR
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
                'is_double_verify' => 1,
            ]);
        }
        $user_id = $stu_data[1];
        $user_data = MgmtStudent::where([
            's_id' => $user_id,
            'seat_type' => 'SELF',
        ])->first();
        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'updated_at' => now(),
            'is_admission_payment' => 1,
            's_admitted_status' => 'ADMITTED AND PAID',
        ]);

        auditTrail($user_id, "Payment for self admission was {$trans_status} for {$student_name} having Order ID {$order_id}");
        // studentActivite($user_id, "Payment for self admission was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('self-admission-payment-success-redirect', $trans_id);
    }

    public function selfAdmissionFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0];
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3];
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $user_id = $stu_data[1];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'pmnt_modified_by' => $user_id,
                'is_double_verify' => 1,
            ]);
        }

        auditTrail($user_id, "Payment for self admission failed for order ID {$order_id}");
        // studentActivite($user_id, "Payment for self admission failed for order ID {$order_id}");

        return redirect()->route('self-admission-payment-fail', [
            'user_id' => $user_id,
            'amount' => $trans_amount,
        ]);
    }

    // spot Admission Student payment
    public function spotAdmissionPayment(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}spot-admission-success";
        $fail_url = "{$base_url}spot-admission-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "SPOTADMISSIONFEES_{$request->student_id}";
        $marid =  '5';

        $student = SpotStudent::where([
            's_id' => $request->student_id
        ])->first();

        if ($student) {
            $total_amount = env('SPOT_ADMISSION_FEES');

            $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$orderid}|{$marid}|NB|ONLINE|ONLINE";

            $aes = new AESEncDec();
            $EncryptTrans = $aes->encrypt($requestParameter, $key);
            $actionUrl = env('SBI_PAYMENT_API');

            PaymentTransaction::create([
                'order_id' => $orderid,
                'pmnt_modified_by' => $request->student_id,
                'pmnt_stud_id' => $request->student_id,
                'pmnt_created_on' => $trans_time,
                'trans_amount' => intval($total_amount),
                'pmnt_pay_type' => 'SPOTADMISSIONFEES'
            ]);

            auditTrail($request->student_id, "Payment for Spot admission initiated for order ID {$orderid}");
            // studentActivite($request->student_id, "Payment for self admission initiated for order ID {$orderid}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data',
                'EncryptTrans' => $EncryptTrans,
                'merchIdVal' => $merchIdVal,
                'actionUrl' => $actionUrl
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student Not Found',
            ]);
        }
    }

    public function spotAdmissionSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0]; // D3UGOGS1D2
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3]; // 500
        $currency = $data[4]; // INR
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
                'is_double_verify' => 1,
            ]);
        }

        $user_id = $stu_data[1];

        $user_data = SpotStudent::where([
            's_id' => $user_id
        ])->first();

        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'is_spot_payment' => 1,
            'spot_payment_at' => now(),
        ]);

        auditTrail($user_id, "Payment for spot admission was {$trans_status} for {$student_name} having Order ID {$order_id}");
        // studentActivite($user_id, "Payment for spot admission was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('spot-admission-payment-success-redirect', $trans_id);
    }

    public function spotAdmissionFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0];
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3];
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $user_id = $stu_data[1];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'pmnt_modified_by' => $user_id,
                'is_double_verify' => 1,
            ]);
        }

        auditTrail($user_id, "Payment for spot admission failed for order ID {$order_id}");
        // studentActivite($user_id, "Payment for spot admission failed for order ID {$order_id}");

        return redirect()->route('spot-admission-payment-fail', [
            'user_id' => $user_id,
            'amount' => $trans_amount,
        ]);
    }

    // spot Admission College payment
    public function spotAdmissionCollegePayment(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}spot-admission-college-success";
        $fail_url = "{$base_url}spot-admission-college-fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "SPOTCOLLEGEFEES_{$request->student_id}";
        $marid =  '5';

        $student = SpotStudent::where([
            's_id' => $request->student_id
        ])->first();

        if ($student) {
            $total_amount = $student->s_gender === 'MALE' ? env('SPOT_MALE_ADMISSION_FEES') : ($student->s_gender === 'FEMALE' ? env('SPOT_FEMALE_ADMISSION_FEES') : env('SPOT_MALE_ADMISSION_FEES'));

            $requestParameter  = "{$merchIdVal}|DOM|IN|INR|{$total_amount}|{$other}|{$success_url}|{$fail_url}|SBIEPAY|{$orderid}|{$marid}|NB|ONLINE|ONLINE";

            $aes = new AESEncDec();
            $EncryptTrans = $aes->encrypt($requestParameter, $key);
            $actionUrl = env('SBI_PAYMENT_API');

            $student->update([
                'updated_at' => now(),
            ]);

            PaymentTransaction::create([
                'order_id' => $orderid,
                'pmnt_modified_by' => $request->student_id,
                'pmnt_stud_id' => $request->student_id,
                'pmnt_created_on' => $trans_time,
                'trans_amount' => intval($total_amount),
                'pmnt_pay_type' => 'SPOTCOLLEGEFEES'
            ]);

            auditTrail($request->student_id, "Payment by College for spot admission initiated for order ID {$orderid}");
            // studentActivite($request->student_id, "Payment for self admission initiated for order ID {$orderid}");

            return response()->json([
                'error' => false,
                'message' => 'Payment Data',
                'EncryptTrans' => $EncryptTrans,
                'merchIdVal' => $merchIdVal,
                'actionUrl' => $actionUrl
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Student Not Found',
            ]);
        }
    }

    public function spotAdmissionCollegeSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0]; // D3UGOGS1D2
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3]; // 500
        $currency = $data[4]; // INR
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
                'is_double_verify' => 1,
            ]);
        }
        $user_id = $stu_data[1];

        $user_data = SpotStudent::where([
            's_id' => $user_id
        ])->first();

        $student_name = $user_data->s_candidate_name;

        $user_data->update([
            'is_spot_inst_paid' => 1,
            'spot_inst_paid_at' => now(),
        ]);

        SpotAllotment::where([
            'stu_id' => $user_id
        ])->update([
            'is_inst_paid' => 1,
        ]);

        auditTrail($user_id, "Payment by college for spot admission was {$trans_status} for {$student_name} having Order ID {$order_id}");
        // studentActivite($user_id, "Payment for self admission was {$trans_status} for {$student_name} having Order ID {$order_id}");

        return redirect()->route('spot-admission-college-payment-success-redirect', $trans_id);
    }

    public function spotAdmissionCollegeFail(Request $request)
    {
        $trans_time = date('Y-m-d H:i:s');
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();

        $trans_details = $aes->decrypt($request->encData, $key);
        $data = explode('|', $trans_details);

        $order_id = $data[0];
        $trans_id = $data[1]; // 4984477659512
        $trans_status = $data[2]; // SUCCESS
        $trans_amount = $data[3];
        $trans_mode = $data[5]; // NB
        $stu_data = explode('_', $data[6]); // OD
        $trans_time = $data[10]; // 2024-05-09 14:41:52
        $country_code = $data[11]; // IN
        $marchnt_id = $data[13]; // 1000605
        $user_id = $stu_data[1];

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'pmnt_modified_by' => $user_id,
                'is_double_verify' => 1,
            ]);
        }

        auditTrail($user_id, "Payment by college for spot admission failed for order ID {$order_id}");
        // studentActivite($user_id, "Payment for self admission failed for order ID {$order_id}");

        return redirect()->route('spot-admission-college-payment-fail', [
            'user_id' => $user_id,
            'amount' => $trans_amount,
        ]);
    }

    // verify payment
    public function verifypayment(Request $request)
    {
        $payment_type = $request->payment_type === 'upgrade' ? 'COUNSELLINGUPGRADEFEES' : ($request->type === 'registration' ? 'COUNSELLINGREGISTRATIONFEES' : 'COUNSELLINGFEES');

        $marchnt_id = env('SBI_MERCHANT_ID');
        $url = "https://www.sbiepay.sbi/payagg/statusQuery/getStatusQuery";

        $pending = PaymentTransaction::whereNot('trans_status', 'SUCCESS')
            ->whereNotNull('order_id')
            ->where([
                'pmnt_pay_type' => $payment_type,
                'is_double_verify' => 0
            ])
            ->get();

        foreach ($pending as $transaction) {
            $trans_amount   = $transaction->trans_amount;
            $order_id   = $transaction->order_id;

            $queryRequest = "|$marchnt_id| $order_id|$trans_amount";
            $queryRequest33 = http_build_query(array(
                'queryRequest' => $queryRequest,
                "aggregatorId" => "SBIEPAY",
                "merchantId" => $marchnt_id
            ));

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_SSLVERSION, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $queryRequest33);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo curl_error($ch);
            }

            curl_close($ch);
            $decrypt = $response;
            $data = explode('|', $decrypt);

            if ($data[2] == "SUCCESS") {
                echo $transaction;

                $transaction->update([
                    'trans_id' => $data[1],
                    'trans_status' => $data[2],
                    'trans_mode' => $data[12],
                    'bank_code' => $data[9],
                    'bank_ref' => $data[10],
                    'trans_details' => $response,
                    'trans_time' => $data[11],
                    'country_code' => $data[3],
                    'marchnt_id' => $marchnt_id,
                    'is_double_verify' => 1,
                ]);

                if ($transaction->pmnt_pay_type == 'COUNSELLINGUPGRADEFEES') {
                    User::where('s_id', $transaction->pmnt_modified_by)->update([
                        'updated_at' => now(),
                        'is_upgrade_payment' => 1
                    ]);
                } else if ($transaction->pmnt_pay_type == 'COUNSELLINGREGISTRATIONFEES') {
                    User::where('s_id', $transaction->pmnt_modified_by)->update([
                        'updated_at' => now(),
                        'is_registration_payment' => 1
                    ]);
                } else {
                    User::where('s_id', $transaction->pmnt_modified_by)->update([
                        'updated_at' => now(),
                        'is_payment' => 1
                    ]);
                }
            }
        }

        return "verification done";
    }
    //AD
    public function registerpayment()
    {
        $trans_time = date('Y-m-d H:i:s');
        $validated = Validator::make($request->all(), [
            'student_id' => ['required'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validated->errors()
            ]);
        }

        $merchIdVal = env('SBI_MERCHANT_ID');
        $actionUrl = env('SBI_PAYMENT_API');

        $orderid = '';
        for ($i = 0; $i < 10; $i++) {
            $d = rand(1, 30) % 2;
            $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
            $orderid .= $d;
        }

        $base_url = env('APP_URL') . '/payment/';
        $success_url = "{$base_url}register_success";
        $fail_url = "{$base_url}register_fail";
        $key = env('SBI_PAYMENT_KEY');
        $other = "REGISTERFEES_{$request->student_id}";
        $marid = '5';
        $merchant_order_num = $orderid;
        $total_amount = env('REGISTER_FEES');
        $requestParameter = "{$merchIdVal}|DOM|IN|INR|$total_amount|$other|$success_url|$fail_url|SBIEPAY|$merchant_order_num|$marid|NB|ONLINE|ONLINE";
        $EncryptTrans = encryptedString($requestParameter, $key);

        PaymentTransaction::create([
            'order_id' => $orderid,
            'pmnt_modified_by' => $request->student_id,
            'pmnt_stud_id' => $request->student_id,
            'pmnt_created_on' => $trans_time,
            'trans_amount' => intval($total_amount),
            'pmnt_pay_type' => 'REGISTERFEES'
        ]);

        auditTrail($request->student_id, "Payment initiated for order ID {$orderid}");
        studentActivite($request->student_id, "Payment initiated for order ID {$orderid}");

        return response()->json([
            'error' => false,
            'message' => 'Payment Data',
            'EncryptTrans' => $EncryptTrans,
            'merchIdVal' => $merchIdVal,
            'actionUrl' => $actionUrl
        ]);
    }

    public function registerpaymentSuccess(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $trans_details = $aes->decrypt($request->encData, $key);

        $data = explode('|', $trans_details);

        $order_id        =    $data[0]; // D3UGOGS1D2
        $trans_id        =    $data[1]; // 4984477659512
        $trans_status    =    $data[2]; // SUCCESS
        $trans_amount        =    $data[3]; // 500
        $currency        =    $data[4]; // INR
        $trans_mode        =    $data[5]; // NB
        $stu_data        =    explode('_', $data[6]); // OD
        $trans_time        =    $data[10]; // 2024-05-09 14:41:52
        $country_code        =    $data[11]; // IN
        $marchnt_id        =    $data[13]; // 1000605
        $bank_code = $data[8];             // SBIN
        $bank_ref_no = $data[9];           // 415228230701

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();

        if ($payment_data) {
            $payment_data->update([
                'trans_id' => $trans_id,
                'trans_status' => $trans_status,
                'trans_amount' => $trans_amount,
                'trans_mode' => $trans_mode,
                'trans_time' => $trans_time,
                'country_code' => $country_code,
                'marchnt_id' => $marchnt_id,
                'trans_details' => $trans_details,
                'bank_code' => $bank_code,
                'bank_ref' => $bank_ref_no,
                'pmnt_pay_type' => $stu_data[0],
                'pmnt_modified_by' => $stu_data[1],
            ]);
        }

        $user_id = $stu_data[1];
        $student = RegisterStudent::where('s_id', $user_id)->first();

        auditTrail($user_id, "Payment {$trans_status} for {$student->s_candidate_name} whose order ID is {$order_id}");
        studentActivite($user_id, "Payment {$trans_status} for {$student->s_candidate_name} whose order ID is {$order_id} for register fees of amount {$trans_amount}");

        $student->update([
            'updated_at' => now(),
            'is_payment' => 1
        ]);

        return redirect()->route('pregisterpayment-success-redirect', $trans_id);
    }
    public function registerpaymentFail(Request $request)
    {
        $key = env('SBI_PAYMENT_KEY');
        $aes =  new AESEncDec();
        $decrypt = $aes->decrypt($request->encData, $key);
        $data = explode('|', $decrypt);

        // dd($decrypt);

        $user_id = explode('_', $data[6]);
        $order_id        =    $data[0];
        $trans_time = date('Y-m-d H:i:s');

        $payment_data = PaymentTransaction::where('order_id', $order_id)->first();
        if ($payment_data) {
            $payment_data->update([
                'trans_details' => $decrypt,
                'trans_time' => $trans_time
            ]);
        }

        $message = "Payment failed for order ID {$order_id}";
        auditTrail($request->student_id, $message);
        studentActivite($request->student_id, $message);

        return redirect()->route('payment-fail', $user_id[1]);
    }
    
}
