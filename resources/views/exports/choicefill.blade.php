<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choice fillup of Diploma in Pharmacy</title>
    <style>
        @page {
            margin-top: 160px;
        }

        .main {
            text-align: center;
            margin-top: 30px;
        }

        body {
            background-image: url("assets/logo_bg.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 80%;
            background-attachment: fixed;
            font-size: 14px;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            margin-bottom: -10mm;
            /* opacity: 0.4;       */
            z-index: -999;
        }

        table {
            /* border: 1px solid #000000; */
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
            width: 100%;


        }

        table th {
            padding: 3px;
            border: 1px solid #000000;
            font-weight: bold;
            text-align: center
        }

        table td {
            padding: 1px;
            border: 1px solid #000000;

        }

        table tr {
            /* text-align: center; */
            border-bottom: 1px solid #000000;
        }

        .header {
            position: fixed;
            top: -100px;
            text-align: center;
            width: 100%;
        }

        .float-left {
            float: left;
            margin-right: 20px;
        }

        .float-right {
            float: right;
            margin-right: 10px;

        }

        .logo-container-right img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        .logo-container-right {
            position: absolute;
            top: 0;
            right: -10px;
        }

        .logo-container {
            width: 20%;
            text-align: left;
        }

        .logo-container img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        .payment-details {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo-container" style="position:absolute;margin-top:0px;margin-left:-10px;width:30%;">
            <img src="{{ public_path('assets/logo.png') }}" alt="Left Logo">
        </div>

        <h4 style="text-align:center;text-transform:uppercase;margin-top:20px;">
            WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION <br> AND SKILL DEVELOPMENT
        </h4>

        <h4>Online Counselling for Admission</h4>
        <h4 style="font-weight:bold;margin-top:-15px;">1<sup>st</sup> year course of Diploma in Pharmacy</h4>

        <div class="logo-container-right">
            <img src="{{ public_path('assets/logo.png') }}" alt="Right Logo">
        </div>
    </div>

    <div class="main">
        <label style="float:left;margin-left:10px;font-weight:bold;">
            Candidate Name:
            {{ isset($choices[0]['candidate_name']) ? $choices[0]['candidate_name'] : '' }}
        </label>

        <label style="float:right;margin-right:10px;font-weight:bold;">
            Phone:
            {{ isset($choices[0]['candidate_phone']) ? $choices[0]['candidate_phone'] : '' }}
        </label>

        <table style="padding: 10px;">
            <thead>
                <tr>
                    <th>Choice No</th>
                    <th>Institute Name</th>
                </tr>
            </thead>

            <tbody>
                @if (sizeof($choices) > 0)
                @foreach ($choices as $row)
                <tr>
                    <td style="width: 10%;text-align: center;">{{ $row['choice_no'] }}</td>
                    <td style="width: 40%; padding-left:10px;">{{ $row['institute_code'] }} -
                        {{ $row['institute_name'] }}
                    </td>
                    {{ $row['branch_code'] }} -
                    {{ $row['branch_name'] }}
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="2">No Data Found</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="float-left">
        </div>

        <div class="float-right">
            <p style="text-align: center"><strong>Date: {{ date('d/m/Y') }}</strong></p>
        </div>
    </div>

    <div class="payment-details">
        <h3>Payment Details:</h3>
        <div style="border: 1px dashed rgb(149, 148, 148); padding:5px 20px;">
            <div>
                <p>
                    <span style="font-weight: bold;">Transaction Id: </span>
                    <span style="margin-left: 10px;">{{ $payment->trans_id }}</span>
                </p>
                <p style="margin-top:-10px;">
                    <span style="font-weight: bold;">Payment Mode: </span>
                    <span style="margin-left: 10px;">{{ $payment->trans_mode }}</span>
                </p>
                <p style="margin-top:-10px;">
                    <span style="font-weight: bold;">Payment Date: </span>
                    <span style="margin-left: 10px;">{{ $payment->trans_time }}</span>
                </p>
                <p style="margin-top:-10px;">
                    <span style="font-weight: bold;">Paid Amount: </span>
                    <span style="margin-left: 10px;">{{ $payment->trans_amount }}</span>
                    <span>
                        [{{ Str::ucfirst(Number::spell($payment->trans_amount)) }} only]
                    </span>
                </p>
            </div>
        </div>
    </div>
</body>