<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Provissional Allotment Letter</title>
    <style>
        body {
            background-image: url("assets/logo_bg.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 35%;
            font-family: Arial, sans-serif;
            font-size: 14px;
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
            height: 250px;
            margin-top: 20px;
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
            width: 20%;
            text-align: left;
        }

        .logo-container img {
            width: 80px;
            display: block;
            margin: 0 auto;
        }

        /* tr {
            border:1px solid black;
        } */
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
            height: 30px;
            position: center;
            background-color: white;
            border: 2px solid black;
            margin-right: 10px;
            display: inline-block;
            margin-top: 20px;
        }

        .footer {
            margin-top: 50px;
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
            <!-- {{ $data['allotement_round'] }} -->
            <h4 style="font-size:14px;">PROVISIONAL ALLOTMENT LETTER CUM MONEY RECEIPT-1st COUNSELING</h4>
            (For admission to the 1st year of 2 years’ Diploma in Pharmacy course for the academic session
            {{ $data['session'] }})

        </div>

        <div class="logo-container-right" style="position:absolute;top:0;margin-top:10px;margin-left:10px;">
            <span style="font-size: 70px; text-align:right; font-weight:700;">1P</span>
        </div>
    </div>
    <p style="text-align: left; margin-left: 10px;">
        <label>Provisional Allotment Letter No:</label>
    </p>
    <div class="main-section" style="border:1px solid black;">
        <div>
            <div class="right">
                <label style="left:10px;">Dated: {{ date('d/m/Y') }}</label>
                <div class="rectangle"></div>
                <div class="rectangle1"></div>
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
                    {{-- <tr>
                        <td>Land looser</td>
                        <td>{{ $data['candidate_land_looser'] }}</td>
                    </tr>
                    <tr>
                        <td>Applied Under TFW:</td>
                        <td>{{ $data['candidate_under_tfw'] }}</td>
                    </tr>
                    <tr>
                        <td>Applied Under EWS:</td>
                        <td>{{ $data['candidate_ews'] }}</td>
                    </tr>
                    <tr>
                        <td>Wards Of Ex-Serviceman:</td>
                        <td>{{ $data['candidate_ex_serviceman'] }}</td>
                    </tr>
                    <tr>
                        <td>District from where passed Madhyamik or Equivalent Examination:</td>
                        <td>{{ $data['candidate_schooling_district'] }}</td>
                    </tr> --}}
                    {{-- <tr>
                        <td>SUB-DIVISION (IF PASSED FROM HOOGHLY OR NADIA DISTRICT) :</td>
                        <td></td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="content"style="height:auto; border:1px solid black;margin-bottom:70px;margin-top:20px;">
        <div style="width:100%;">
            <table border="1" cellpadding="1" style="width:100%;">
                <tbody>
                    <tr>
                        <td style="text-align:center;color:grey;">General Rank</td>
                        <td style="text-align:center;color:grey;">SC Rank</td>
                        <td style="text-align:center;color:grey;">ST Rank</td>
                        <td style="text-align:center;color:grey;">PC Rank</td>
                        <td style="text-align:center;color:grey;">OBCA</td>
                        <td style="text-align:center;color:grey;">OBCB</td>
                    </tr>
                    <tr>
                        <td style="text-align:center;">{{ $data['rank'][0]['rank'] }}</td>
                        <td>{{ $data['rank'][1]['rank'] == 0 ? '' : $data['rank'][1]['rank'] }}</td>
                        <td>{{ $data['rank'][2]['rank'] == 0 ? '' : $data['rank'][2]['rank'] }}</td>
                        <td>{{ $data['rank'][5]['rank'] == 0 ? '' : $data['rank'][5]['rank'] }}</td>
                        <td>{{ $data['rank'][3]['rank'] == 0 ? '' : $data['rank'][3]['rank'] }}</td>
                        <td style="text-align:center;">
                            {{ $data['rank'][4]['rank'] == 0 ? '' : $data['rank'][4]['rank'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="width:100%;margin-top:0px;">
            <p style="padding:2px;">Candidate, whose details are furnished herein above, is hereby provisionally
                selected for admission to the 1st year of 2 years’ Diploma in Pharmacy course for the Academic Session
                {{ $data['session'] }} in the Institution mentioned below in accordance with his/her rank and given
                choices in order of preference.</p>
        </div>
        <div style="width:100%; margin-top:10px;">
            <table border="1" style="width:100%;">
                <tbody>
                    <tr>
                        <td colspan="2" style="text-align: center;color:rgb(8, 8, 8);">
                            PROVISIONAL ALLOTMENT DETAILS
                            <br>
                            The concerned candidate has been provisionally allotted a seat as he/she has opted for
                            auto-up-gradation of his/her seat to any of the higher prioritized seats given by him/her.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="text-transform:uppercase">Institute Name:</p>
                        </td>
                        <td>
                            <p style="font-weight: bold;">{{ $data['institute_name'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="text-transform:uppercase">BRANCH NAME :</p>
                        </td>
                        <td>
                            <p style="font-weight: bold;text-transform:uppercase;">{{ $data['branch_name'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="text-transform:uppercase">Provisionally Seat Allotted through (quota) :</p>
                        </td>
                        <td>
                            <p style="font-weight: bold;">{{ $data['allotement_category'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>CHOICE PRIORITY :</p>
                        </td>
                        <td style="font-weight: bold;">{{ $data['choice_option'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <br><br>
    <div class="footer" style="border:1px solid black;">
        <div>
            @if (!empty($data['trans_amount']))
                <h4 style="text-align:center;"><u>MONEY RECEIPT</u>
                </h4>
            @else
                <h4 style="text-align:center;"><u>Counselling Fees Not Paid</u>
                </h4>
            @endif
            <p style="text-align:center;">(To be retained by the candidate)</p>
            @if (!empty($data['trans_amount']))
                <p style="padding:5px;">Received <b>Rs.{{ $data['trans_amount'] }}/- (Rupees One thousand) only</b>
                    through
                    {{ $data['trans_mode'] }} on {{ $data['trans_time'] }} vide Transaction Number :
                    {{ $data['trans_id'] }}, Reference No : {{ $data['bank_ref_no'] }} from
                    {{ $data['candidate_name'] }}, son/daughter of {{ $data['candidate_guardian_name'] }} having
                    Application Form Number: {{ $data['appl_form_num'] }} securing General
                    Rank
                    {{ $data['gen_rank'] }} towards <b>PROVISIONAL SEAT BOOKING FEE</b> against his/her allotment for
                    admission to
                    1st year of 2 years’ Diploma
                    Course at {{ $data['institute_name'] }} in the branch of {{ $data['branch_name'] }} through
                    {{ $data['allotement_category'] }} during
                    Online Counseling for the academic session {{ $data['session'] }}</p>
            @endif
            <p style="padding:5px;  font-weight: bold;text-align:center;">N.B.:
                The Candidate is not required to report to any Institution.
                Wait for the result of next phase.
                If not upgraded in next phase, your presently allotted seat will remain booked for you.
            </p>
        </div>
    </div>









</body>

</html>
