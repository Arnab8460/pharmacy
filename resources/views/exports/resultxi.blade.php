<table>
    <thead>
        <th>Name</th>
        @foreach ($papers as $paper)
            <th>{{ $paper['subject_code'] }}</th>
        @endforeach
        <th>Status</th>
    </thead>
    <tbody>
        @foreach ($results as $result)
            <tr>
                <td style="border: 1px solid blue;">
                    {{ $result->student->student_fname . ' ' . $result->student->student_mname . ' ' . $result->student->student_lname }}
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p1_code == 'BEN1')
                        {{ $result->p1_theory ? "Theo: {$result->p1_theory}" : null }}
                        @if ($result->p1_pr_internel || $result->p1_pr_externel)
                            Prac: {{ $result->p1_pr_internel + $result->p1_pr_externel }}
                        @endif
                        {{ $result->p1_project ? "Proj: {$result->p1_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p1_code == 'HIN1')
                        {{ $result->p1_theory ? "Theo: {$result->p1_theory}" : null }}
                        @if ($result->p1_pr_internel || $result->p1_pr_externel)
                            Prac: {{ $result->p1_pr_internel + $result->p1_pr_externel }}
                        @endif
                        {{ $result->p1_project ? "Proj: {$result->p1_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p2_code == 'ENG1')
                        {{ $result->p2_theory ? "Theo: {$result->p2_theory}" : null }}
                        @if ($result->p2_pr_internel || $result->p2_pr_externel)
                            Prac: {{ $result->p2_pr_internel + $result->p2_pr_externel }}
                        @endif
                        {{ $result->p2_project ? "Proj: {$result->p2_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p3_code == 'RSE1')
                        {{ $result->p3_theory ? "Theo: {$result->p3_theory}" : null }}
                        @if ($result->p3_pr_internel || $result->p3_pr_externel)
                            Prac: {{ $result->p3_pr_internel + $result->p3_pr_externel }}
                        @endif
                        {{ $result->p3_project ? "Proj: {$result->p3_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p4_code == 'MDA1')
                        {{ $result->p4_theory ? "Theo: {$result->p4_theory}" : null }}
                        @if ($result->p4_pr_internel || $result->p4_pr_externel)
                            Prac: {{ $result->p4_pr_internel + $result->p4_pr_externel }}
                        @endif
                        {{ $result->p4_project ? "Proj: {$result->p4_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p5_code == 'ACT1')
                        {{ $result->p5_theory ? "Theo: {$result->p5_theory}" : null }}
                        @if ($result->p5_pr_internel || $result->p5_pr_externel)
                            Prac: {{ $result->p5_pr_internel + $result->p5_pr_externel }}
                        @endif
                        {{ $result->p5_project ? "Proj: {$result->p5_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p6_code == 'BSM1')
                        {{ $result->p6_theory ? "Theo: {$result->p6_theory}" : null }}
                        @if ($result->p6_pr_internel || $result->p6_pr_externel)
                            Prac: {{ $result->p6_pr_internel + $result->p6_pr_externel }}
                        @endif
                        {{ $result->p6_project ? "Proj: {$result->p6_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid red; text-align: center;">
                    @if ($result->p7_code == 'EDCA')
                        {{ $result->p7_theory ? "Theo: {$result->p7_theory}" : null }}
                        @if ($result->p7_pr_internel || $result->p7_pr_externel)
                            Prac: {{ $result->p7_pr_internel + $result->p7_pr_externel }}
                        @endif
                        {{ $result->p7_project ? "Proj: {$result->p7_project}" : null }}
                    @else
                        N/A
                    @endif
                </td>

                <td style="border: 1px solid yellow; text-align: center;">
                    {{ $result->p1_theory + $result->p4_pr_internel + $result->p4_pr_externel > 80 ? 'Promoted' : 'Not Promoted' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
