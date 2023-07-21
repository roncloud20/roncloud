<?php
    $pagetitle = "User Verification";
    require_once "assets/header.php";
    // Include the database connection file
    require_once 'db_connect.php';

    // Retrieve the verification code from the URL query parameter
    $verificationCode = $_GET['code'];

    // Check if the verification code is provided in the URL
    if (empty($verificationCode)) {
        die("Invalid verification code.");
    }

    // Query the database to find the user with the provided verification code
    $sql = "SELECT * FROM users WHERE verification_code = '$verificationCode' AND is_verified = 0";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Verification code is valid, update the user's 'is_verified' status to 1 (true)
        $sql = "UPDATE users SET is_verified = 1 WHERE verification_code = '$verificationCode'";
        if (mysqli_query($conn, $sql)) {
            echo "Email verification successful! Your account has been verified.";
        } else {
            echo "Error updating the verification status. Please contact support.";
        }
    } else {
        echo "Invalid verification code or the account has already been verified.";
    }

    // Close the database connection
    mysqli_close($conn);
?>
