<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polytechnic Spot Councilling Rank</title>
    <style>
        @page {
            margin-top: 160px;
        }

        .main {
            text-align: center;

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
            text-align: center;
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
    </style>
</head>
@php
$random = env('ENC_KEY');
@endphp
<body>

    <div class="header">
        <div class="logo-container" style="position:absolute;margin-top:0px;margin-left:-10px;width:30%;">
            <img src="{{ public_path('assets/logo.png') }}" alt="Left Logo">
        </div>
        <strong>
            <p style="text-align:center;font-size:11px;text-transform:uppercase;margin-top:20px;">
                west bengal state
                council of technical and polytechnic and skill development</p>
        </strong>

        <p style="margin-top:-12px;"> Rank In Polytechnic Spot Counselling</p>
        <p style="font-weight:bold;">First Year</p>

        <div class="logo-container-right">
            <img src="{{ public_path('assets/logo.png') }}" alt="Right Logo">
        </div>


    </div>

    <div class="main">
        <!-- <label style="float:left;margin-left:10px;font-weight:bold;">Candidate Name: {{ isset($choices[0]['candidate_name'])?$choices[0]['candidate_name'] : '' }}</label> -->
        <!-- <label style="float:right;margin-right:10px;font-weight:bold;">Phone: {{ isset($choices[0]['candidate_phone'])?$choices[0]['candidate_phone']:'' }}</label> -->

        <table style="padding: 10px;">
            <thead>
                <tr>
                    <th>Candidate Name</th>
                    <th>Candidate Rank</th>
                    <th>Candidate Category</th>
                    <th>Candidate Phone</th>



                </tr>
            </thead>

            <tbody>
                @if (sizeof($data) > 0)
                @foreach ($data as $row)
                <tr>
                    <td style="width: 40%;">{{ $row->student_name }}</td>
                    <td style="width: 10%;">{{ $row->student_rank }}
                    </td>
                    <td style="width: 30%;">{{ $row->cast_category }}
                    </td>
                    <td>{{ $row->student_phone }}</td>
                     <!-- <td>{{ decryptHEXFormat($row->student_aadhar, $random) }}</td> -->
                     <!-- <td>{{ encryptHEXFormat('724663936646', $random) }}</td> -->
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="4">No Data Found</td>
                </tr>

                @endif


            </tbody>


        </table>
        <div class="float-left">
            {{-- <p style="text-align: center"><strong>Place: kolkata<strong></p> --}}

        </div>
        <div class="float-right">
            <p style="text-align: center"><strong>Date: {{ date('d/m/Y') }}</strong></p>
        </div>
</body>