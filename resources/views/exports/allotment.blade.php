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
            height: 280px;
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
        <div class="logo-container" style="position:absolute;margin-top:10px;margin-left:5px;">
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
            <h4>FINAL {{ $data['provisional'] }} ALLOTMENT LETTER CUM MONEY RECEIPT -
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
                <!-- 5<sup>th</sup> -->
                COUNSELING
            </h4>
            (For admission to 1st year of Pharmacy Courses during {{ $data['session'] }})
        </div>

        <div class="logo-container-right" style="position:absolute;top:0;margin-top:10px;margin-left:10px;">
            {{-- <img src="{{ public_path('assets/logo.png') }}" alt="Right Logo"> --}}
            <span style="font-size: 70px; text-align:right; font-weight:700;">{{ $data['allotement_round'] }}F</span>
        </div>

    </div>
    <p style="text-align: left; margin-left: 10px;">
        <label>Final Allotment Letter No:</label>
    </p>
    <div class="main-section" style="border:1px solid black;">
        <div>
            {{-- <div class="left">
                <label>Final Allotment Letter No:<span></span></label>
            </div> --}}


            <div class="right">
                <label style="left:10px;">Dated: {{ date('d/m/Y') }}</label>
                <div class="rectangle"></div>
                <div class="rectangle1"></div>
                <!-- <img src="images/Untitled.jpg" alt="Logo"style="position:absolute;bottom:60%;left:155px; top:40%;width: 200px;height: 50px; border: 1px solid black;"> -->
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
                    <!-- <tr>
                        <td>SUB-DIVISION (IF PASSED FROM HOOGHLY OR NADIA DISTRICT) :</td>
                        <td></td>
                    </tr> -->
                </tbody>
            </table>
        </div>
    </div>

    <div class="content" style="border:1px solid black;height:950px;margin-top:30px;">
        {{-- <div style="width:100%;"> --}}
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
        {{-- </div> --}}
        <div style="width:100%;">
            <p style="padding-left:8px;">
                Candidate, whose details are furnished herein above, is hereby finally selected for admission to the 1st
                year of 2 years’ Diploma in Pharmacy course for the Academic Session {{ $data['session'] }} in the
                Institution mentioned below in accordance with his/her rank and given choices in order of preference.
            </p>
        </div>
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
        <br>
        <br><br>
        <div style="width:100%;">
            <p style="padding-left:7px;margin-top:25px;">
                He/She is directed to report to the Principal/Officer-in-Charge of the allotted Pharmacy with the
                following documents for verification and admission. <strong><em>The candidate must report to and take
                        admission in the concerned Pharmacy within {{ $data['admission_end_date'] }} failing which
                        this allotment will stand
                        cancelled automatically.<strong><em><br>
            </p>
            <ol list-style-type: decimal;>

                <li>Rank Card <b>(1 copy)</b></li>
                <li>Aadhaar Card <b>(ORIGINAL)</b></li>
                <li>This Final Allotment Letter cum Money Receipt <b>(2 copies)</b></li>
                <li>Recent passport size colour photograph <b>(1 copy)</b></li>
                <li>Admit Card of Madhyamik or equivalent examination or Birth Registration Certificate
                    <b>(ORIGINAL)</b>
                </li>
                <li>Mark sheet & Certificate of Higher Secondary or equivalent examination (ORIGINAL). </li>
                <li>Mark sheet of Madhyamik or equivalent examination <b>(ORIGINAL)</b></li>
                <li>Migration or School Leaving Certificate <b>(ORGINAL)</b> (Required if passed from the Board/Council
                    other than the West Bengal Board of Secondary Education)</li>
                <li>SC/ST/OBC-A/OBC-B Certificate <b>(ORIGINAL)</b> (Required only if allotted through SC/ST/OBC-A/OBC-B
                    quota) issued by competent authority of the Government of West Bengal</li>
                <li>Physically Challenged (PC) Certificate <b>(ORIGINAL)</b> (Required only if allotted through PC
                    quota) issued by competent authority</li>
                <li>Medical Fitness Certificate <b>(ORIGINAL)</b> issued by a Registered Medical Practitioner / Medical
                    Officer in prescribed format</li>
                <li>For taking final admission, a candidate has to pay the Admission Fees, Tuition Fees and other fees,
                    as applicable to the Government and Private Institutions. Admission of students should only be made
                    on receipt of all fees related to admission of students.
                    Payment of fees must be made through GRIPS portal as per the directives of the Government issued
                    from time to time.
                </li>
                <li>Filled-in Anti Ragging Affidavit <b>(ORIGINAL)</b> in the prescribed format</li>
                <li>Filled-in Domicile Certificate <b>(ORIGINAL)</b> in the prescribed format</li>
                <li>A set of photocopies of all the aforesaid documents</li>
                <li>For obtaining information regarding admission process at the allotted institution and amount of
                    initial fees to be paid and procedure of payment thereof, a candidate may contact the person(s)
                    whose details are furnished below. </li>
            </ol>


            <div style="width:100%; margin-top:70px;">
                <table border="1" style="width:100%;">
                    <tbody>
                        <tr style="height:200px;">
                            <td colspan="2" style="text-align: center;color:rgb(11, 11, 11);">DETAILS OF THE ALLOTTED
                                INSTITUION</td>
                        </tr>
                        <tr style="height:200px;">
                            <td>NAME OF THE INSTITUTE: </td>
                            <td style="padding:3px;">{{ $data['institute_name'] }}</td>
                        </tr>
                        <tr style="height:200px;">
                            <td style="padding:3px;">E-MAIL ID :</td>
                            <td style="padding:3px;">{{ $data['contact_inst_email'] }}</td>
                        </tr>
                        <tr style="height:200px;">
                            <td style="padding:3px;">CONTACT PERSON:</td>
                            <td style="padding:3px;">{{ $data['contact_person_name'] }}</td>
                        </tr>
                        <tr style="height:200px;">
                            <td style="padding:3px;">MOBILE NO. OF THE CONTACT PERSON : </td>
                            <td style="padding:3px;">{{ $data['contact_person_phone'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p style="padding-left:7px;margin-top:25px;">Final admission will be made after physical verification of
                documents at the institute level. The admission and candidature of a candidate/student will be cancelled
                automatically if he/she fails to produce any of the documents in original before the verifying authority
                of the institute or produces fake document(s) at the time of physical verification. </p>


        </div>

        <div style="width:100%; height:50px;  position:relative;">
            <div style="position:absolute; float:right; padding:5px;margin-top:80px;">
                <label>(full signature of the candidate) </label>
            </div>
            <div style="position:absolute; float:left; padding:5px;margin-top:80px;">
                <label>Date:</label>
            </div>
        </div>
        {{-- <div style="width:100%;height:200px;"></div> --}}


    </div>
    <div style="width:100%;height:50px;"></div>

    <div class="footer" style="border:1px solid black;margin-top:30px;">
        <div>
            @if (!empty($data['trans_amount']))
                <h4 style="text-align:center;"><u>MONEY RECEIPT</u>
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
                    General Rank {{ $data['gen_rank'] }}, towards <b>{{ $type }} SEAT BOOKING FEE</b> against
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
