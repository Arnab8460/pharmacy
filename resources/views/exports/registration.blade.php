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
    <title>{{ $data['provisional'] }} Registration Fees Receipt</title>
    <style>
        body {
            background-image: url("assets/logo_bg.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 35%;
            font-family: Arial, sans-serif;
            margin: 0;

        }

        .left {
            float: left;
            margin-right: 20px;
            margin-left: 15px;
        }

        .right {
            float: right;
        }

        .header {
            text-align: center;
            border-style: double;
        }

        .main-section {
            height: 200px;
            position: relative;
            /* margin-top: 20px;  */
        }

        .logo-container-right img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        .logo-container-right {
            width: 20%;
            float: right;
        }

        .logo-container {
            width: 10%;
            text-align: left;
        }

        .logo-container img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        /* .test td{
             border: 1px solid black;
        } */
        .center-horizontally {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .center-both {
            display: flex;
            justify-content: center;
            /* Center horizontally */
            align-items: center;
            /* Center vertically */
            height: 100vh;
            /* Full height of the viewport */
        }

        .rectangle {
            width: 150px;
            height: 150px;

            background-color: white;
            border: 2px solid black;
            margin-left: 17px;
            margin-top: 20px;
        }

        .rectangle1 {
            width: 180px;
            height: 50px;
            position: center;
            background-color: white;
            border: 2px solid black;
            margin-right: 10px;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header" style="position:relative;">
        <div class="logo-container"style="position:absolute;margin-top:10px;margin-left:5px;">
            <img src="{{ public_path('assets/logo.png') }}" alt="Left Logo">
        </div>
        <div class="header-text" style="text-align: center;flex-grow: 1;">
            <p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
                <span style="color:#2d0660;font-family:Cambria;font-size:14px;">
                    <span style="font-stretch:115%;">
                        <strong>WEST BENGAL STATE COUNCIL OF TECHNICAL & VOCATIONAL EDUCATION AND SKILL
                            DEVELOPMENT</strong>
                    </span>
                </span>
            </p>

            <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                    (A Statutory Body under Government of West Bengal Act XXVI of 2013)
                </span>
            </p>
            <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                    Department of Technical Education, Training & Skill Development, Govt. of West Bengal
                </span>
            </p>

            <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
                <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                    Karigari Bhavan, 4th Floor, Plot No. B/7, Action Area-III, Newtown, Rajarhat, Kolkata–700160
                </span>
            </p>
            <h4>FINAL {{ $data['provisional'] }} REGISTRATION MONEY RECEIPT -
                @if ($data['allotement_round'] == 1)
                    {{ $data['allotement_round'] }}<sup>st</sup>
                @elseif($data['allotement_round'] >= 4)
                    {{ $data['allotement_round'] }}<sup>th</sup>
                @elseif($data['allotement_round'] == 3)
                    {{ $data['allotement_round'] }}<sup>rd</sup>
                @elseif($data['allotement_round'] == 2)
                    {{ $data['allotement_round'] }}<sup>nd</sup>
                @elseif($data['allotement_round'] == 5)
                    {{ $data['allotement_round'] }}<sup>th</sup>
                @endif
                COUNSELING
            </h4>
            (For admission to 1st year of Pharmacy Courses during {{ $data['session'] }})
        </div>

        <div class="logo-container-right" style="position:absolute;top:0;margin-top:10px;margin-left:10px;">
            <span style="font-size: 70px; text-align:right; font-weight:700;">1F</span>
        </div>

    </div>

    <div class="main-section" style="border:1px solid black;margin-top:5px;">
        <div>
            <div class="right">
                <label style="left:10px;">Dated: {{ date('d/m/Y') }}</label>
            </div>
        </div>
        <div style="width:100%; position:absolute;">
            <table border="0" style="width:70%;padding:3px;">
                <tbody>
                    <tr>
                        <td>Application Form No:</td>
                        <td>{{ $data['appl_form_num'] }}</td>
                    </tr>
                    <tr>
                        <td>Name Of The Candidate:</td>
                        <td>{{ $data['candidate_name'] }}</td>
                    </tr>
                    <tr>
                        <td>Date Of Birth:</td>
                        <td>{{ $data['candidate_dob'] }}</td>
                    </tr>
                    <tr>
                        <td>Guardian's Name:</td>
                        <td>{{ $data['candidate_guardian_name'] }}</td>
                    </tr>
                    <tr>
                        <td>Mobile No:</td>
                        <td>{{ $data['candidate_phone'] }}</td>
                    </tr>
                    <tr>
                        <td>Category:</td>
                        <td>{{ $data['candidate_caste'] }}</td>
                    </tr>
                    <tr>
                        <td>Physical Challenged</td>
                        <td>{{ $data['candidate_physically_challenged'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="content" style="border:1px solid black;margin-top:5px;">
        <table border="1" cellpadding="1" cellspacing="1" style="width:100%;">
            <tbody>
                <tr>
                    <td style="text-align:center;color:grey;">General Rank</td>
                    <td style="text-align:center;color:grey;">SC Rank</td>
                    <td style="text-align:center;color:grey;">ST Rank</td>
                    <td style="text-align:center;color:grey;">PC Rank</td>
                    <td style="text-align:center;color:grey;">OBC-A Rank</td>
                    <td style="text-align:center;color:grey;">OBC-B Rank</td>
                </tr>
                <tr>
                    <td style="text-align:center;">{{ $data['rank'][0]['rank'] }}</td>
                    <td>{{ $data['rank'][1]['rank'] == 0 ? '' : $data['rank'][1]['rank'] }}</td>
                    <td>{{ $data['rank'][2]['rank'] == 0 ? '' : $data['rank'][2]['rank'] }}</td>
                    <td>{{ $data['rank'][5]['rank'] == 0 ? '' : $data['rank'][5]['rank'] }}</td>
                    <td>{{ $data['rank'][3]['rank'] == 0 ? '' : $data['rank'][3]['rank'] }}</td>
                    <td>{{ $data['rank'][4]['rank'] == 0 ? '' : $data['rank'][4]['rank'] }}</td>
                </tr>
            </tbody>
        </table>

        <div style="width:100%;">
            <table border="1" style="width:100%;">
                <tbody>
                    <tr style="height:200px;">
                        <td colspan="2" style="text-align: center;color:rgb(6, 6, 6);">FINAL ALLOTMENT DETAILS</td>
                    </tr>
                    <tr style="height:200px;">
                        <td style="text-transform:uppercase">Institute Name: </td>
                        <td style="padding:3px;">{{ $data['institute_name'] }}</td>
                    </tr>
                    <tr style="height:200px;">
                        <td style="padding:3px;text-transform:uppercase">Branch/Course Name:</td>
                        <td style="padding:3px;text-transform:uppercase">{{ $data['branch_name'] }}</td>
                    </tr>
                    <tr style="height:200px;">
                        <td style="padding:3px;text-transform:uppercase">Finally Seat Allotted through (quota):</td>
                        <td style="padding:3px;">{{ $data['allotement_category'] }}</td>
                    </tr>
                    <tr style="height:200px;">
                        <td style="padding:3px;">CHOICE PRIORITY : </td>
                        <td style="padding:3px;">{{ $data['choice_option'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div style="width:100%;">
            <p style="padding:5px;margin-top:5px;">Final admission will be made after physical verification of
                documents at the institute level. The admission and candidature of a candidate/student will be cancelled
                automatically if he/she fails to produce any of the documents in original before the verifying authority
                of the institute or produces fake document(s) at the time of physical verification.
            </p>
        </div>
    </div>

    <div class="footer"style="border:1px solid black;margin-top:30px;">
        <div>
            @if (!empty($data['trans_amount']))
                <h4 style="text-align:center;"><u>REGISTRATION MONEY RECEIPT</u>
                </h4>
            @else
                <h4 style="text-align:center;"><u>Counselling Fees Not Paid</u>
                </h4>
            @endif
            @if (!empty($data['trans_amount']))
                <p style="padding:5px;">Received <b>Rs.{{ $data['trans_amount'] }}/- ({{ $data['amount_in_words'] }})
                        only</b>
                    through
                    {{ $data['trans_mode'] }} on {{ $data['trans_time'] }} vide Transaction Number :
                    {{ $data['trans_id'] }}, Reference No :
                    {{ $data['bank_ref_no'] }} from
                    {{ $data['candidate_name'] }}, son/daughter of {{ $data['candidate_guardian_name'] }} having
                    Application Form Number: {{ $data['appl_form_num'] }} securing
                    General Rank {{ $data['gen_rank'] }}, towards <b>{{ $type }} REGISTRATION FEE</b> against
                    his/her
                    allotment to 1st year of 2 years’ Diploma Courses at {{ $data['institute_name'] }} in the branch of
                    {{ $data['branch_name'] }} through
                    {{ $data['allotement_category'] }}
                    during Online Counseling for the Academic Session {{ $data['session'] }} .
                </p>
            @endif
        </div>
    </div>









</body>

</html>
