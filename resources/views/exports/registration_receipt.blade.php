<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        h2 { color: #4CAF50; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Payment Receipt</h2>
        <table>
            <tr>
                <th>Application Form No:</th>
                <td>{{ $registerstudent->application_form_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Candidate Name:</th>
                <td>{{ $registerstudent->candidate_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Father's Name:</th>
                <td>{{ $registerstudent->father_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Mother's Name:</th>
                <td>{{ $registerstudent->mother_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date of Birth:</th>
                <td>{{ isset($registerstudent->date_of_birth) ? date('d/m/Y', strtotime($registerstudent->date_of_birth)) : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Phone Number:</th>
                <td>{{ $registerstudent->phone_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Email:</th>
                <td>{{ $registerstudent->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Gender:</th>
                <td>{{ $registerstudent->gender ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Religion:</th>
                <td>{{ $registerstudent->religion ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Caste:</th>
                <td>{{ $registerstudent->caste ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>General Rank:</th>
                <td>{{ $registerstudent->general_rank ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Payment Amount:</th>
                <td>{{ isset($payment->trans_amount) ? "Rs. {$payment->trans_amount}/-" : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Payment Mode:</th>
                <td>{{ $payment->trans_mode ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Payment Status:</th>
                <td>{{ $payment->trans_status ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Transaction ID:</th>
                <td>{{ $payment->trans_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Payment Date:</th>
                <td>{{ isset($payment->trans_time) ? date('d/m/Y H:i:s', strtotime($payment->trans_time)) : 'N/A' }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
