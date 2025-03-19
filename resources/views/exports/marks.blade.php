<html>

<head>
    <style>
        @page {
            margin-top: 160px;
        }

        .header {
            position: fixed;
            top: -140px;
            text-align: center;
        }

        .foter {
            /* position: fixed; */
            /* bottom: 20px; */
            /* text-align: center; */
        }

        .main {
            text-align: center;
            padding-left: 100px;
        }

        .footer-left {
            float: left;
            width: 25%;
            height: 80%;
            /* background-color: #cc6633; */

        }

        .footer-right {
            margin-left: 45%;
            width: 65%;
            height: 10%;
            /* background-color: #3366cc; */

        }
    </style>
</head>

<body>
    <div class="header">
        <p style="line-height:1;margin:10.13px 130.27px 0px 128.93px;text-align:center;">
            <span style="color:#2d0660;font-family:Cambria;font-size:14px;">
                <span style="font-stretch:115%;">
                    <strong>WEST BENGAL
                        STATE COUNCIL OF TECHNICAL AND VOCATIONAL EDUCATION &amp; SKILL DEVELOPMENT</strong>
                </span>
            </span>
        </p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="color:#2d0660;font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                (Under West Bengal Act XXVI of 2013)[Vocational Education Division]
            </span>
        </p>

        <p style="line-height:11.53px;margin:0px 130.13px 0px 128.93px;text-align:center;text-indent:0px;">
            <span style="color:#2d0660;font-family:'Trebuchet MS', Helvetica, sans-serif;font-size:10px;">
                Karigori Bhavan,5thFloor,PlotNo.B/7,Action Area-III,NewTown,Rajarhat,Kolkata-700160
            </span>
        </p>



        <p style="line-height:1;margin:10px 130.13px 2.6px 128.93px;text-align:center;text-indent:0px;">
            <span style="font-family:Cambria;font-size:13px;">
                <span style="font-stretch:120%;">
                    INST NAME : <strong>{{ $students[0]['inst_name'] }}</strong></br>, Course Name :
                    <strong>{{ $students[0]['course_name'] }}</strong></br>,
                    Semester : <strong>{{ $students[0]['semester'] }}</strong></br>, Paper :
                    <strong>{{ $students[0]['paper'] }}</strong>
                </span>
            </span>
        </p>
    </div>

    <div class="main">
        <table style="border-collapse:collapse;border:1px solid #000000;">
            <thead>
                <tr>
                    <td style="border:1px solid #000000;height:26.4px;padding:0px;vertical-align:top;width:50px;">
                        <p style="line-height:1;margin:7px 0px 0px 0.27px; text-align: center;">
                            <span style="font-family:Cambria;font-size:10.67px;">
                                <span style="font-stretch:115%;">
                                    <strong>Sl No</strong>
                                </span>
                            </span>
                        </p>
                    </td>

                    <td style="border:1px solid #000000;height:26.4px;padding:0px;vertical-align:top;width:109px;">
                        <p style="line-height:1;margin:7px 0px 0px 16.6px; text-align: center;">
                            <span style="font-family:Cambria;font-size:10.67px;">
                                <span style="font-stretch:115%;">
                                    <strong>Reg No</strong>
                                </span>
                            </span>
                        </p>
                    </td>

                    <td style="border:1px solid #000000;height:26.4px;padding:0px;vertical-align:top;width:109px;">
                        <p style="line-height:1;margin:7px 0px 0px 16.6px; text-align: center;">
                            <span style="font-family:Cambria;font-size:10.67px;">
                                <span style="font-stretch:115%;">
                                    <strong>Student Name</strong>
                                </span>
                            </span>
                        </p>
                    </td>

                    <td style="border:1px solid #000000;height:26.4px;padding:0px;vertical-align:top;width:80px;">
                        <p style="line-height:1;margin:7px 0px 0px 16.6px; text-align: center;">
                            <span style="font-family:Cambria;font-size:10.67px;">
                                <span style="font-stretch:115%;">
                                    <strong>Full Marks</strong>
                                </span>
                            </span>
                        </p>
                    </td>

                    <td style="border:1px solid #000000;height:26.4px;padding:0px;vertical-align:top;width:109px;">
                        <p style="line-height:1;margin:7px 0px 0px 16.6px; text-align: center;">
                            <span style="font-family:Cambria;font-size:10.67px;">
                                <span style="font-stretch:115%;">
                                    <strong>Marks Obtained</strong>
                                </span>
                            </span>
                        </p>
                    </td>
                </tr>
            </thead>
            <tbody>
                <!-- marks -->
                @foreach ($students as $row)
                    <tr>
                        <td style="border:1px solid #000000;height:17.6px;padding:0px;vertical-align:top;">
                            <p style="line-height:1;margin:2.53px 0px 0px 2.53px; text-align: center;">
                                <span style="font-family:Cambria;font-size:10.67px;">
                                    <span style="font-stretch:110%;">
                                        {{ $row['sl_no'] }}
                                    </span>
                                </span>
                            </p>
                        </td>

                        <td style="border:1px solid #000000;height:17.6px;padding:0px;vertical-align:top;width:109px;">
                            <p style="line-height:1;margin:0px; text-align: center;">
                                <span style="font-family:Cambria;font-size:10.67px;">
                                    <span style="font-stretch:115%">
                                        {{ $row['student_reg_no'] }}
                                    </span>
                                </span>
                            </p>
                        </td>

                        <td
                            style="border:1px solid #000000;height:17.6px;padding:0px;vertical-align:top;width:65.67px;">
                            <p style="line-height:1;margin:0px; text-align: center;">
                                <span style="font-family:Cambria;font-size:10.67px;">
                                    <span style="font-stretch:115%">
                                        {{ $row['student_name'] }}
                                    </span>
                                </span>
                            </p>
                        </td>

                        <td style="border:1px solid #000000;height:17.6px;padding:0px;vertical-align:top;">
                            <p style="line-height:1;margin:0px; text-align: center;">
                                <span style="font-family:Cambria;font-size:10.67px;">
                                    <span style="font-stretch:115%">
                                        {{ $marks }}
                                    </span>
                                </span>
                            </p>
                        </td>

                        <td
                            style="border:1px solid #000000;height:17.6px;padding:0px;vertical-align:top;width:67.87px;">
                            <p style="line-height:1;margin:0px; text-align: center;">
                                <span style="font-family:Cambria;font-size:10.67px;">
                                    <span style="font-stretch:115%">
                                        {{ $row['student_marks_obtained'] }}
                                    </span>
                                </span>
                            </p>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

    <div class="foter" style="margin-top:50px;">
        <div class="footer-left">
            <p style="text-align: center">{{ $examiner_name }}</p>
            <p style="text-align: center">Examiner Name</p>
        </div>
        <div class="footer-right">
            <p style="text-align: center">------------------------</p>
            <p style="text-align: center">Examiner Signature</p>

        </div>
    </div>

</body>

</html>
