<?php
    // PHPMailer library
    require 'assets/PHPMailer/src/PHPMailer.php';
    require 'assets/PHPMailer/src/SMTP.php';
    require 'assets/PHPMailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    $pagetitle = "Registration Validations";
    require_once "assets/header.php";

    // Include the database connection file
    require_once 'assets/db_connect.php';

    // Function to generate a random username
    function generateRandomUsername($firstname, $lastname)
    {
        $username = strtolower($firstname . '.' . $lastname);
        $username = preg_replace('/\s+/', '', $username); // Remove spaces
        
        // Generate a random number between 1000 and 9999
        $randomNumber = rand(0000, 9999);
        
        // Concatenate the random number to the username
        $username .= $randomNumber;
        
        return $username;
    }

    // Function to sanitize user input
    function sanitizeInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Function to generate a random verification code
    function generateVerificationCode()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $verificationCode = '';
        for ($i = 0; $i < 32; $i++) {
            $verificationCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $verificationCode;
    }

    // Retrieve and sanitize user inputs from the registration form
    $firstname = sanitizeInput($_POST['firstname']);
    $lastname = sanitizeInput($_POST['lastname']);
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password']; // Note: We do not sanitize passwords as they are hashed before storage
    $cpassword = $_POST['cpassword']; // Note: We do not sanitize passwords as they are hashed before storage
    $phone = sanitizeInput($_POST['phone']);

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password) || empty($cpassword)) {
        die("Please fill in all required fields.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    // Check if the phone number is not empty and has at least 11 numeric characters
    if (!empty($phone) && preg_match("/^\d{11,}$/", $phone)) {
        // Phone number is valid
        echo "Phone number verification successful: $phone";
    } else {
        // Set a default phone number if the input is empty or does not meet the minimum length requirement
        $defaultPhone = "08000000000";
        echo "You didn't register a phone number...\r\n Setting default phone number: $defaultPhone";
        $phone = $defaultPhone;
    }


    // Check if the username is taken
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Suggest a random username if the provided username is taken
        $suggestedUsername = generateRandomUsername($firstname, $lastname);
        echo "The username '$username' is already taken. Suggested username: '$suggestedUsername'<br>";
        $username = $suggestedUsername;
    }

    // Verify if the password matches the confirm password
    if ($password === $cpassword) {
        // echo "Password matches confirm password. Password verification successful!";
        // Hash the password for secure storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        die("Password does not match confirm password. Password verification failed.");
    }

    // Validate and process the profile picture upload
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] === 0) {
        $targetDir = "assets/img/profile_pics/"; // Directory to store profile pictures
        $profilePicture = $_FILES["profile_picture"]["name"];
        $targetFilePath = $targetDir . basename($profilePicture);
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Generate a unique file name using the user's username and user_id
        $newFileName = $username . "_" . uniqid() . "." . $imageFileType;
        $targetFilePath = $targetDir . $newFileName;

        // Check if the file is an actual image
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                echo "Profile picture uploaded and renamed successfully.";
            } else {
                echo "Error uploading the profile picture.";
                $targetFilePath = NULL;
            }
        } else {
            die("Invalid profile picture. Please upload an image file.");
        }
    } else {
        // echo "No profile picture uploaded.";
        $targetFilePath = NULL;
    }

    // Generate a verification code
    $verificationCode = generateVerificationCode();

    // Prepare and execute the SQL query to insert the user data into the 'users' table
    $sql = "INSERT INTO users (firstname, lastname, username, email, password, phone, verification_code, is_email_verified, profile_picture)
            VALUES ('$firstname', '$lastname', '$username', '$email', '$hashed_password', '$phone', '$verificationCode', 0, '$targetFilePath')";

    if (mysqli_query($conn, $sql)) {

        // Replace these settings with your Gmail credentials
        $smtpUsername = 'roncloudtechnologies@gmail.com';
        $smtpPassword = 'jxeqsxjzcuyiwpis'; // Use the 16-digit App Password

        // SMTP settings
        $smtpHost = 'smtp.gmail.com';
        $smtpPort = 587;

        // PHPMailer library

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; // Set to 2 for debugging purposes
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort;
        $mail->SMTPSecure = 'tls'; // Use 'ssl' if required by your hosting provider
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;

        // Set From, To, Subject, and Message
        $mail->setFrom('roncloudtechnologies@gmail.com', 'http://localhost/roncloud');
        $mail->addAddress($email, $firstname); // Replace $to and $recipientName with recipient's email and name
        $mail->Subject = 'Email Verification for User Registration';
        $mail->Body = "Hello $firstname,\n\nThank you for registering at Roncloud Technologies. Please click on the link below to verify your email address:\n\n";
        $mail->Body .= "http://localhost/roncloud/verify.php?code=$verificationCode";

        // Send the email
        if ($mail->send()) {
            echo "Registration successful! An email verification link has been sent to your email address. Please check your inbox to verify your account.";
        } else {
            echo "Error sending the verification email. Please contact support.";
        }

        // // Send verification email to the user
        // $to = $email;
        // $subject = "Email Verification for User Registration";
        // $message = "Hello $firstname,\n\n Thank you for registering at Roncloud Technologies. Please click on the link below to verify your email address:\n\n";
        // $message .= "http://localhost/roncloud/verify.php?code=$verificationCode";
        // $headers = "From: noreply@127.0.0.1\r\n";


        // if (mail($to, $subject, $message, $headers)) {
        //     echo "Registration successful! An email verification link has been sent to your email address. Please check your inbox to verify your account.";
        // } else {
        //     echo "Error sending the verification email. Please contact support.";
        // }
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
?>