<?php
include 'admin/confiq.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    
    // Image file handling
    $image = $_FILES['user_image'];
    $target_dir = "admin/uploads/";
    $target_file = $target_dir . basename($image["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($image["tmp_name"]);
    if($check === false) {
        die("File is not an image.");
    }

    // Validate inputs
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        echo "All fields are required.";
    } elseif ($password !== $confirm_password) {
        echo "Passwords do not match.";
    } elseif (!move_uploaded_file($image["tmp_name"], $target_file)) {
        echo "Error uploading your image.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            echo "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(50)); // Generate verification token

            // Insert user data
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, user_image, verification_token) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone, $hashed_password, $target_file, $verification_token);

            if ($stmt->execute()) {
                // Send confirmation email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Your SMTP host
                    $mail->SMTPAuth = true;
                    $mail->Username = 'asmadilshad76@gmail.com'; // Your SMTP username
                    $mail->Password = 'ykjyvjbjnjswnhbu'; // Your SMTP password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('asmadilshad76@gmail.com', 'Simple');
                    $mail->addAddress($email);
                    $domain = "https://" . $_SERVER['HTTP_HOST'];

                    $mail->isHTML(true);
                    $mail->Subject = 'Email Verification';
                    $mail->Body = "
                        <h2>Thank you for registering!</h2>
                        <p>Please click the link below to verify your email address:</p>
                        <a href='$domain/activate.php?token=$verification_token'>Activate Your Account</a>
                    ";

                    $mail->send();
                    echo "Signup successful! Please check your email to verify your account. <a href='https://mail.google.com/'>gmail</a>";
                        header("Location: sign-in");
                        
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                echo "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>


