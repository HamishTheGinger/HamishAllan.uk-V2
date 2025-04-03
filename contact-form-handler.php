<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'vendor/autoload.php';

$errors = '';
$myemail = 'hamish@hamishallan.uk'; 

$name = isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8') : '';
$email_address = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$message = isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : '';
$cc = isset($_POST['copy']) ? $_POST['copy'] : null;

if (empty($name) || empty($email_address) || empty($message)) {
    $errors .= "\n Error: All fields are required";
}

if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
    $errors .= "\n Error: Invalid email address";
}

if (empty($errors)) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com'; // Hostinger's SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'hamish@hamishalln.uk'; // Your email
        $mail->Password = ''; // Use an App Password if available
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // 465 for SSL, 587 for TLS

        // Sender & Recipient
        $mail->setFrom('contact-form@hamishallan.uk', 'HamishAllan.uk - Contact Form');
        $mail->addAddress($myemail); 

        if ($cc === "on") {
            $mail->addCC($email_address);
        }
        $mail->Subject = "Contact form submission: $name";
        $mail->Body = "You have received a new message.\n\n".
                      "Here are the details:\n".
                      "Name: $name\n".
                      "Email: $email_address\n".
                      "Message:\n$message";

        $mail->send();

        header('Location: contact-form-thank-you.html');
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo nl2br($errors);
}
?>
