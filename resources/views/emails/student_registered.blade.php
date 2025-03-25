<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmation</title>
</head>
<body>
    <h2>Dear {{ $name }},</h2>
    <p>Thank you for registering. Your application has been successfully submitted.</p>
    <p><strong>Application Form Number:</strong> {{ $form_number }}</p>
    <p>We will review your application and get back to you soon.</p>
    <br>
    <p>Regards,</p>
    <p>Your Application Team</p>
</body>
</html>
