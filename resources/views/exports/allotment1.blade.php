<!DOCTYPE html>
<html lang="en">
<?php
$type = 'FINAL';
if (!empty($data['provisional'])) {
    $type = 'PROVISIONAL';
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Final {{ $data['provisional'] }} Allotment Letter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-12"></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus ullam dolor facere veniam, quibusdam
            similique ad aspernatur, alias ipsum amet quos eos architecto rerum est magni quam laborum ex laboriosam.
        </div>
        <div class="col-lg-6">
            Lorem ipsum dolor sit amet consectetur adipisicing.
        </div>
    </div>
</body>

</html>
