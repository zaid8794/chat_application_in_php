<?php
$error = '';
session_start();
if (isset($_GET['code'])) {
    require_once "database/ChatUser.php";
    $user_obj = new ChatUser;
    $user_obj->setUserVerificationCode($_GET['code']);
    if ($user_obj->is_valid_email_verification_code()) {
        $user_obj->setUserStatus('Enable');
        if ($user_obj->enable_user_account()) {
            $_SESSION['success_message'] = "Your email is successfully verified, Now you can login to your account.";
            header("location:index.php");
        } else {
            $error = 'Something went wrong';
        }
    } else {
        $error = 'Something went wrong';
    }
}
