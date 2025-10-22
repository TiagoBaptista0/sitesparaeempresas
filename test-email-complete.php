<?php
require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include functions directly
require_once 'config/functions.php';

// Test sending an email
$to = "test@example.com";
$subject = "Test Email from SitesParaEmpresas";
$message = "<h1>Test Email</h1><p>This is a test email sent from SitesParaEmpresas using PHPMailer and MailHog.</p>";

if (enviarEmail($to, $subject, $message)) {
    echo "Email sent successfully! Check MailHog at http://localhost:8025";
} else {
    echo "Failed to send email";
}
?>