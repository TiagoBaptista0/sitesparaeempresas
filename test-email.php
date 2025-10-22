<?php
// Test email sending
$to = "test@example.com";
$subject = "Test Email";
$message = "This is a test email.";
$headers = "From: noreply@sitesparaempresas.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully";
} else {
    echo "Failed to send email";
}
?>