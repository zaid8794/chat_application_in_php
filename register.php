<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require "vendor/autoload.php";
$error = '';
$success_message = '';

if (isset($_POST["register"])) {

    session_start();

    if (isset($_POST["user_data"])) {
        header("location:chatroom.php");
    }

    require_once "database/ChatUser.php";
    $user_obj = new ChatUser;
    $user_obj->setUserName($_POST['user_name']);
    $user_obj->setUserEmail($_POST['user_email']);
    $user_obj->setUserPassword($_POST['user_password']);
    $user_obj->setUserProfile($user_obj->make_avatar(strtoupper($_POST['user_name'][0])));
    $user_obj->setUserStatus('Disabled');
    $user_obj->setUserCreatedOn(date("Y-m-d H:i:s"));
    $user_obj->setUserVerificationCode(md5(uniqid()));
    $user_obj->setUserLoginStatus('Logout');
    $user_data = $user_obj->get_user_data_by_email();

    if (is_array($user_data) && count($user_data) > 0) {
        $error = 'This email is already registered';
    } else {
        if ($user_obj->save_data()) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->Username = "zaidvora9@gmail.com";
            $mail->Password = 'kjto qqrq jowp wpqw';
            $mail->From = "zaid@gmail.com";
            $mail->FromName = "Zaid Vora";
            $mail->addAddress($user_obj->getUserEmail());
            $mail->isHTML(true);
            $mail->Subject = "Verification Email for Chat App";
            $mail->Body = '
            <p>Thank you for registering for chat application.</p>
            <p>This is a verification email, please click on the link to verify your email.</p>
            <p><a href="http://localhost:80/Internship Akash Infotech/chat_application_in_php/verify.php?code=' . $user_obj->getUserVerificationCode() . '">Click to Verify</a></p>
            <p>Thank you...</p>
            ';
            $mail->send();

            $success_message = 'Verification mail sent to ' . $user_obj->getUserEmail() . ', So before login first verify your email';
        } else {
            $error = 'Something went wrong';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome 5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/parsley/parsley.css">
</head>

<body>

    <div class="container">
        <br>
        <br>
        <h1 class="text-center">
            PHP Chat Application using Websocket
        </h1>


        <div class="row justify-content-md-center">
            <div class="col col-md-4 mt-5">
                <?php
                if ($error != '') {
                ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?= $error ?>
                    </div>
                <?php
                }
                if ($success_message != '') {
                ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?= $success_message ?>
                    </div>
                <?php
                }
                ?>
                <div class="card">
                    <div class="card-header">Register</div>
                    <div class="card-body">
                        <form action="" id="register_form" method="post">
                            <div class="form-group">
                                <label for="">Enter Your Name</label>
                                <input type="text" name="user_name" id="user_name" data-parsley-pattern="/^[a-zA-Z\s]+$/" class="form-control shadow-none" required>
                            </div>
                            <div class="form-group">
                                <label for="">Enter Your E-mail</label>
                                <input type="text" name="user_email" id="user_email" data-parsley-type="email" class="form-control shadow-none" required>
                            </div>
                            <div class="form-group">
                                <label for="">Enter Your Password</label>
                                <input type="password" name="user_password" id="user_password" data-parsley-minlength="6" data-parsley-maxlength="12" data-parsley-pattern="^[a-zA-Z]+$" class="form-control shadow-none" required>
                            </div>
                            <div class="form-group text-center">

                                <input type="submit" name="register" class="btn btn-success" value="Register">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/parsley/parsley.min.js"></script>
    <script>
        $('#register_form').parsley();
    </script>
</body>

</html>