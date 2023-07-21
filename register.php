<?php
    $pagetitle = "User Registration";
    require_once "assets/header.php";
?>
<script>
    function readProfilePicture(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('profile_picture_preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
<h2>User Registration</h2>
<form action="registration.php" method="post" enctype="multipart/form-data">
    <label for="firstname">First Name:</label>
    <input type="text" name="firstname" placeholder="First Name*:" required><br>

    <label for="lastname">Last Name:</label>
    <input type="text" name="lastname" placeholder="Last Name*:" required><br>

    <label for="username">Username:</label>
    <input type="text" name="username" placeholder="Username*:" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" placeholder="E-Mail Address*:" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" placeholder="Password*:" required><br>
    
    <label for="password">Confirm Password:</label>
    <input type="password" name="cpassword" placeholder="Confirm Password*:" required><br>

    <label for="phone">Phone:</label>
    <input type="tel" name="phone" placeholder="Phone Number (Optional): "><br>

    <label for="profile_picture">Profile Picture:</label>
    <input type="file" name="profile_picture" onchange="readProfilePicture(this)"><br>
    <img id="profile_picture_preview" src="assets/img/FCA.png" alt="Preview"><br>

    <input type="submit" value="Register">
</form>