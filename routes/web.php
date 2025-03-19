<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtherController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MgmtAdmissionController;
use App\Models\SuperUser;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('payment')->group(function () {
    Route::post('/success', [PaymentController::class, 'paymentSuccess']);
    Route::post('/fail', [PaymentController::class, 'paymentFail']);

    Route::post('/upgrade-success', [PaymentController::class, 'paymentUpgradeSuccess']);
    Route::post('/upgrade-fail', [PaymentController::class, 'paymentUpgradeFail']);

    Route::post('/registration-success', [PaymentController::class, 'paymentRegistrationSuccess']);
    Route::post('/registration-fail', [PaymentController::class, 'paymentRegistrationFail']);

    Route::post('/management-admission-success', [PaymentController::class, 'managementAdmissionSuccess']);
    Route::post('/management-admission-fail', [PaymentController::class, 'managementAdmissionFail']);

    Route::post('/self-admission-success', [PaymentController::class, 'selfAdmissionSuccess']);
    Route::post('/self-admission-fail', [PaymentController::class, 'selfAdmissionFail']);

    Route::post('/spot-admission-success', [PaymentController::class, 'spotAdmissionSuccess']);
    Route::post('/spot-admission-fail', [PaymentController::class, 'spotAdmissionFail']);

    Route::post('/spot-admission-college-success', [PaymentController::class, 'spotAdmissionCollegeSuccess']);
    Route::post('/spot-admission-college-fail', [PaymentController::class, 'spotAdmissionCollegeFail']);
});

// payment success
Route::get('payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('payment-success-redirect');

// payment fail
Route::get('payment-fail/{user_id}', function ($user_id) {
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
    $other = "COUNSELLINGFEES_$user_id";
    $marid = '5';
    $merchant_order_num = $orderid;
    $total_amount = env('COUNSELLING_FEES');
    $requestParameter = "1000605|DOM|IN|INR|$total_amount|$other|$success_url|$fail_url|SBIEPAY|$merchant_order_num|$marid|NB|ONLINE|ONLINE";

    $EncryptTrans = encryptedString($requestParameter, $key);
    failPaymentPharmacy($orderid, $user_id, 'COUNSELLINGFEES', $total_amount);

    return view('redirect.payment-fail', [
        'actionUrl' => $actionUrl,
        'EncryptTrans' => $EncryptTrans,
        'merchIdVal' => $merchIdVal,
    ]);
})->name('payment-fail');




// upgrade payment success
Route::get('upgrade-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.upgrade-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('upgrade-payment-success-redirect');

// upgrade payment fail
Route::get('upgrade-payment-fail/{user_id}', function ($user_id) {
    return view('redirect.upgrade-payment-fail', [
        'user_id' => $user_id
    ]);
})->name('upgrade-payment-fail');




// registration payment success
Route::get('registration-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.registration-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('registration-payment-success-redirect');

// registration payment fail
Route::get('registration-payment-fail/{user_id}', function ($user_id) {
    return view('redirect.registration-payment-fail', [
        'user_id' => $user_id
    ]);
})->name('registration-payment-fail');



// management admission payment success
Route::get('management-admission-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.management-admission-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('management-admission-payment-success-redirect');

// management admission payment fail
Route::get('management-admission-payment-fail/{user_id}/{amount}', function ($user_id, $amount) {
    return view('redirect.management-admission-payment-fail', [
        'user_id' => $user_id,
        'amount' => $amount
    ]);
})->name('management-admission-payment-fail');



// self admission payment success
Route::get('self-admission-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.self-admission-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('self-admission-payment-success-redirect');

// self admission payment fail
Route::get('self-admission-payment-fail/{user_id}/{amount}', function ($user_id, $amount) {
    return view('redirect.self-admission-payment-fail', [
        'user_id' => $user_id,
        'amount' => $amount
    ]);
})->name('self-admission-payment-fail');



// spot admission payment success
Route::get('spot-admission-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.spot-admission-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('spot-admission-payment-success-redirect');

// spot admission payment fail
Route::get('spot-admission-payment-fail/{user_id}/{amount}', function ($user_id, $amount) {
    return view('redirect.spot-admission-payment-fail', [
        'user_id' => $user_id,
        'amount' => $amount
    ]);
})->name('spot-admission-payment-fail');



// spot admission college payment success
Route::get('spot-admission-college-payment-success-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.spot-admission-college-payment-success', [
        'trans_id' => $trans_id
    ]);
})->name('spot-admission-college-payment-success-redirect');

// spot admission payment fail
Route::get('spot-admission-college-payment-fail/{user_id}/{amount}', function ($user_id, $amount) {
    return view('redirect.spot-admission-college-payment-fail', [
        'user_id' => $user_id,
        'amount' => $amount
    ]);
})->name('spot-admission-college-payment-fail');



// others
Route::get('payment-success-spot-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.payment-success-spot', [
        'trans_id' => $trans_id
    ]);
})->name('payment-success-spot-redirect');

Route::get('payment-fail-spot/{user_id}', function ($user_id) {
    return view('redirect.payment-fail-spot', [
        'user_id' => $user_id
    ]);
})->name('payment-fail-spot');

Route::get('payment-success-spot-counselling-redirect/{trans_id}', function ($trans_id) {
    return view('redirect.payment-success-spot-counselling', [
        'trans_id' => $trans_id
    ]);
})->name('payment-success-spot-counselling-redirect');

Route::get('payment-fail-spot-counselling/{user_id}', function ($user_id) {
    return view('redirect.payment-fail-spot-counselling', [
        'user_id' => $user_id
    ]);
})->name('payment-fail-spot-counselling');

Route::get('/payment-verification', [PaymentController::class, 'verifypayment']);
Route::get('/self-admission-hill-pdf/{appl_num}', [MgmtAdmissionController::class, 'getAllSelfAdmissionAssignedTradeHillPdf']);
Route::get('/council-admission-pdf/{appl_num}', [MgmtAdmissionController::class, 'getAllAdmissionAssignedTradeCouncilPdf']);
Route::get('/user-master-data-insert', [OtherController::class, 'usersMasterDataInsert']);
Route::get('/self-admission-pdf/{appl_num}', [MgmtAdmissionController::class, 'getAllSelfAdmissionAssignedTradePdf']);

Route::post('/authenticate', [AuthController::class, 'authenticate']);

Route::get('/test-database', function () {
    try {
        DB::connection()->getPdo();
        echo "Connected successfully to the database!";
    } catch (\Exception $e) {
        die("Could not connect to the database. Error: " . $e->getMessage());
    }
});

Route::get('/clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('route:clear');

    return "Cache cleared successfully";
});

Route::get('/symlink', function () {
    Artisan::call('storage:link');

    return "Symlink created successfully";
});

Route::get('aadhaar-encrypt/{aadhaar}', function ($aadhaar) {
    $key = env('ENC_KEY');
    return bin2hex(openssl_encrypt($aadhaar, 'aes-256-ecb', $key, OPENSSL_RAW_DATA));
});

Route::get('set-pass', function () {
    $users = SuperUser::whereNot('u_username', 'council')->get();

    foreach ($users as $user) {
        $user->update([
            'u_password' => hash("sha512", $user->u_username)
        ]);
    }
});
