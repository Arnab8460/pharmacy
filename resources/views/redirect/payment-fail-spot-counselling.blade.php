<!DOCTYPE html>

<head>
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:400,400i,700,900&display=swap" rel="stylesheet">
</head>
<style>
    body {
        text-align: center;
        padding: 40px 0;
        background: #EBF0F5;
    }

    h1 {
        color: #88B04B;
        font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
        font-weight: 900;
        font-size: 40px;
        margin-bottom: 10px;
    }

    p {
        color: #404F5E;
        font-family: "Nunito Sans", "Helvetica Neue", sans-serif;
        font-size: 20px;
        margin: 0;
    }

    i {
        color: #9ABC66;
        font-size: 100px;
        line-height: 200px;
        margin-left: -15px;
    }

    .card {
        background: white;
        padding: 60px;
        border-radius: 4px;
        box-shadow: 0 2px 3px #C8D0D8;
        display: inline-block;
        margin: 0 auto;
    }
</style>

<?php
$merchIdVal = env('SBI_MERCHANT_ID');
$actionUrl = env('SBI_PAYMENT_API');
$orderid = '';
for ($i = 0; $i < 10; $i++) {
    $d = rand(1, 30) % 2;
    $d = $d ? chr(rand(65, 90)) : chr(rand(48, 57));
    $orderid = $orderid . $d;
}
$base_url = env('APP_URL') . '/payment/';
$success_url = $base_url . 'success-spot-counselling';
$fail_url = $base_url . 'fail-spot-counselling';
$key = env('SBI_PAYMENT_KEY');
$other = 'COUNSELLINGSPOTFEES_' . $user_id;
$marid = '5';
$merchant_order_num = $orderid;
$total_amount = env('COUNSELLING_FEES');
$requestParameter  = "{$merchIdVal}|DOM|IN|INR|" . $total_amount . "|" . $other . "|" . $success_url . "|" . $fail_url . "|SBIEPAY|" . $merchant_order_num . "|" . $marid . "|NB|ONLINE|ONLINE";

$EncryptTrans = encryptedString($requestParameter, $key);
failSpotPaymentCounselling($orderid,$user_id,'COUNSELLINGSPOTFEES',$total_amount);

?>

<body>
    <div class="card">
        <div style="border-radius:200px; height:200px; width:200px; background: #F8FAF5; margin:0 auto;">
            <i class="checkmark">X</i>
        </div>
        <h1>Fail</h1>
        <p>Fail payment!<br /> Try again</p>
        <br />
        <a href="{{ env('REDIRECT_SPOT_CHOICE_URL') }}" style="text-decoration: none;">Go To Spot choice preview page</a>
        <br />
        <form action="{{ $actionUrl }}" method="POST">
            <input type="hidden" name="EncryptTrans" value="<?php echo $EncryptTrans; ?>">

            <input type="hidden" name="merchIdVal" value="{{ $merchIdVal }}" />
            <input type="submit" value="Pay Again">
        </form>
    </div>
</body>

</html>
