<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course_duration }} Diploma in Vocational in {{ $course_name }} ({{ $semester }})</title>\
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Times New Roman', Times, serif;
        }

        .container {
            /* max-width: 1024px; */
            width: 100%;
            min-height: 100vh;
            margin: auto;
            padding: 10px;
        }

        .head-scetion,
        .mid-section {
            width: 100%;
            height: auto;
            padding: 10px;
        }

        .header-container {
            width: 100%;
            height: auto;
            padding: 10px;
            text-align: center;
        }

        .table-container {
            width: 100%;
            min-height: 50vh;
            margin-top: 5px;
        }

        table {
            border: 1px solid #000000;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
            min-width: 100%;
        }

        table th {
            padding: 8px;
            border: 1px solid #000000;
            font-weight: bold;
            text-align: center
        }

        table td {
            padding: 8px;
            border: 1px solid #000000;
        }

        table tr {
            text-align: center;
        }
    </style>
</head>
<?php
$th_cnt = $theory_papers;
$ses_cnt = $sessional_papers;
$tot_paper_cnt = $th_cnt + $ses_cnt;
$row_span = $tot_paper_cnt > 7 ? 6 : 5;

?>

<body>
    <div class="container">
        <section class="head-scetion">
            <div class="header-container">
                <span style="text-transform: uppercase; font-size: 16px; font-weight: bold; text-decoration: underline;">
                    west bengal state council of technical and vocational education and skill development
                </span>
                <p>{{ $course_duration }} Diploma in Vocational in {{ $course_name }} &#40;{{ $semester }}&#41;</p>
            </div>
        </section>

        <section class="mid-section">
            <div style="margin-top: 5px; text-transform: uppercase;">
                <p class="float:left;">
                    <span style="font-weight: bold;">center&#58; </span>
                    <span style="margin-left: 10px;">{{ $institute_name }}</span>
                </p>
                <p style="margin-right: 5rem; float:right; margin-top: -15px;">
                    <span style="font-weight: bold;">held in&#58; </span>
                    <span style="margin-left: 2rem;">may&comma; {{ $examYear }}</span>
                </p>
            </div>
            <div class="table-container">
                <table>

                    <tr>
                        <th rowspan='{{ $tot_paper_cnt }}' colspan='2'>ROLL</th>
                        <th rowspan='{{ $row_span }}' colspan='5' style="width: 50vw;">{{ $institute_code }} /
                            {{ $course_code }} / {{ $course_name }} / {{ $semester }}
                        </th>
                        <th rowspan='1' colspan='{{ $th_cnt }}'>THEORITICAL</th>
                        <th rowspan='1' colspan='{{ $ses_cnt }}'>SESSIONAL</th>
                        <th rowspan='{{ $row_span }}' colspan='1'>TOTAL</th>
                        <th rowspan='{{ $row_span }}' colspan='2'>REMARKS</th>
                    </tr>
                    <tr style="height: 50vh; font-weight: bold;">
                        @foreach ($papers as $key => $row)
                            <td rowspan='{{ $row_span }}' colspan='1' style="transform: rotate(-90deg);">
                                {{ $row->paper_name }}
                            </td>
                        @endforeach
                    </tr>
                    <?php //if($tot_paper_cnt >=7){
                    ?>
                    <tr>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                    </tr>
                    <tr>
                    </tr>
                    <?php //}
                    ?>
                    <tr>
                        <td rowspan='1' colspan='7' style="font-weight: bold;">Full Marks</td>
                        @foreach ($papers as $k => $val)
                            @php
                                $total[] = $val->paper_full_marks;
                                $totalFullMarks = array_sum($total);
                            @endphp
                            <td>{{ $val->paper_full_marks }}</td>
                        @endforeach
                        <td>{{ $totalFullMarks }}</td>
                        <td rowspan='7' colspan='2'></td>
                    </tr>
                    <tr>
                        <td rowspan='1' colspan='7' style="font-weight: bold;">Pass Marks</td>
                        @foreach ($papers as $pkey => $pval)
                            <td>{{ $pval->paper_pass_marks }}</td>
                        @endforeach
                        <td>-</td>
                    </tr>
                    <tr>
                        <td rowspan='4' colspan='7'></td>
                        <td>INT</td>
                        <td>INT</td>
                        <td>INT</td>
                        <td>INT</td>
                        <td>INT</td>
                        <td>INT</td>
                        <td>-</td>
                        <td rowspan='4' colspan='1'></td>
                    </tr>
                    <tr>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td>ATD</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>EXT</td>
                        <td>EXT</td>
                        <td>EXT</td>
                        <td>EXT</td>
                        <td>EXT</td>
                        <td>EXT</td>
                        <td>EXT</td>
                    </tr>
                    <tr>
                        <td>TOT</td>
                        <td>TOT</td>
                        <td>TOT</td>
                        <td>TOT</td>
                        <td>TOT</td>
                        <td>TOT</td>
                        <td>TOT</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td rowspan='1' colspan='2'>NUMBER</td>
                        <td rowspan='1' colspan='5'>NAME OF THE CANDIDATE</td>
                        <td rowspan='1' colspan='8'></td>
                    </tr>

                    @php
                        //Start Student Dynamic
                    @endphp
                    @foreach ($students as $skey => $student)
                        <tr>
                            <td rowspan='5' colspan='2'>{{ $student['sl_no'] }}</td>
                            <td rowspan='5' colspan='5'>{{ $student['student_name'] }}</td>
                            <td>{{ $student['sub1_th_int_marks'] }}</td>
                            <td>8</td>
                            <td>9</td>
                            <td>9</td>
                            <td>24</td>
                            <td>24</td>
                            <td></td>
                            <td rowspan='5' colspan='1'>{{ $student['GRAND_TOTAL'] }}</td>
                            <td rowspan='5' colspan='2'>{{ $student['RESULT'] }}</td>
                        </tr>
                        <tr>
                            <td>{{ $student['sub1_th_int_attd_marks'] }}</td>
                            <td>5</td>
                            <td>5</td>
                            <td>5</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>{{ $student['sub1_th_ext_marks'] }}</td>
                            <td>28</td>
                            <td>20</td>
                            <td>30</td>
                            <td>24</td>
                            <td>24</td>
                            <td>168</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>38</td>
                            <td>41</td>
                            <td>34</td>
                            <td>44</td>
                            <td>48</td>
                            <td>48</td>
                            <td>168</td>
                        </tr>
                    @endforeach
                    @php
                        //End Student Dynamic
                    @endphp
                </table>
            </div>
        </section>
    </div>
</body>

</html>
