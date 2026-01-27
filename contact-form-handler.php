<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$config = include(__DIR__ . '/../config.php'); 
$CF_Secret = $config['CF_SECRET']; 
$email_password = $config['EMAIL_PASSWORD'];

// Load PHPMailer
require 'vendor/autoload.php';

function validateTurnstile($token, $secret, $remoteip = null) {
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    $data = [
        'secret' => $secret,
        'response' => $token
    ];

    if ($remoteip) {
        $data['remoteip'] = $remoteip;
    }

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        return ['success' => false, 'error-codes' => ['internal-error']];
    }

    return json_decode($response, true);

}


$errors = '';
$myemail = 'website@hamishallan.uk'; 

$token = $_POST['cf-turnstile-response'] ?? '';
$remoteip = $_SERVER['HTTP_CF_CONNECTING_IP'] ??
$_SERVER['HTTP_X_FORWARDED_FOR'] ??
$_SERVER['REMOTE_ADDR'];

$validation = validateTurnstile($token, $CF_Secret, $remoteip);

if ($validation['success']) {
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
            $mail->Host = 'smtp.titan.email'; // Hostinger's SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'website@hamishallan.uk'; // Your email
            $mail->Password = $email_password; // Use an App Password if available
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465; // 465 for SSL, 587 for TLS

            // Sender & Recipient
            $mail->setFrom('website@hamishallan.uk', 'HamishAllan.uk - Contact Form');
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

            header('Location: contact-form-complete.html');
            exit();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo nl2br($errors);
    }
} else {
    echo "Verification failed. Please try again.";
    error_log('Turnstile validation failed: ' . implode(', ', $validation['error-codes']));
}
?>
