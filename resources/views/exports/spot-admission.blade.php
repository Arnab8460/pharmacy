<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Spot Admission</title>
    <style>
        body {
            background-image: url("assets/logo_bg.png");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 35%;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        td {
            font-size: 14px;
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
            height: 350px;
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
            <h4>Admission Form For 1st year Admission {{ date('Y') }}</h4>
            <span style="font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:14px;">
                (For {{ $institute_name }} on 18/02/2025 Spot Admission)
            </span>
        </div>

        <div class="logo-container-right" style="position:absolute;top:0;margin-top:10px;margin-left:10px;">
            <img src="{{ public_path('assets/logo.png') }}" alt="Right Logo">
        </div>

    </div>

    <div class="main-section" style="border:1px solid black;">
        <div style="margin-top:20px;">
            <div class="right">
                <label style="left:10px;">Dated: {{ date('d/m/Y') }}</label>
                <div class="rectangle">

                </div>
                <div class="rectangle1"></div>
            </div>
        </div>
        <div style="width:100%; position:absolute; margin-top: 30px;">
            <table style="width:70%;padding:3px;">
                <tbody>
                    <tr>
                        <td>Name Of The Candidate:</td>
                        <td>{{ $student_name }}</td>
                    </tr>
                    <tr>
                        <td>Date Of Birth:</td>
                        <td>{{ $dob }}
                        </td>
                    </tr>
                    <tr>
                        <td>Father's Name:</td>
                        <td>{{ $father_name }}</td>
                    </tr>
                    <tr>
                        <td>Mother's Name:</td>
                        <td>{{ $mother_name }}</td>
                    </tr>
                    <tr>
                        <td>Mobile No:</td>
                        <td>{{ $phone }}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{ $email }}</td>
                    </tr>
                    <tr>
                        <td>Caste:</td>
                        <td>{{ $caste }}</td>
                    </tr>
                    <tr>
                        <td>Gender:</td>
                        <td>{{ $gender }}</td>
                    </tr>
                    <tr>
                        <td>Inst. Name:</td>
                        <td>{{ $institute_name }}</td>
                    </tr>
                    <tr>
                        <td>Paid Amount:</td>
                        <td>{{ $trans_amount }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div style="width:100%; height:50px;  position:relative;">
        <div style="position:absolute; float:right; padding:5px;margin-top:80px;">
            <label>(full signature of the candidate) </label>
        </div>
        <div style="position:absolute; float:left; padding:5px;margin-top:80px;">
            <label>Date:</label>
        </div>
    </div>


</body>

</html>
