<?php
// Test email confirmation system
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include functions
require_once 'config/functions.php';

// Test data
$testEmail = "confirmation-test@example.com";
$testSubject = "Email Confirmation Test";
$testMessage = "
<h1>Email Confirmation Test</h1>
<p>This is a test of the email confirmation system.</p>
<p>If you're seeing this email, it means the PHPMailer configuration is working correctly with MailHog.</p>
";

// Test sending email
echo "Testing email sending to: " . $testEmail . "\n";

if (enviarEmail($testEmail, $testSubject, $testMessage)) {
    echo "SUCCESS: Email sent successfully!\n";
    echo "Check MailHog at http://localhost:8025 to verify the email was received.\n";
} else {
    echo "ERROR: Failed to send email\n";
}

echo "\nTest completed.\n";
?>