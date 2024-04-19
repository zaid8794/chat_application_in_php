<?php

session_start();
$error = '';
if (isset($_SESSION['user_data'])) {
    header("location:chatroom.php");
}

if (isset($_POST['login'])) {
    require_once "database/ChatUser.php";
    $user_obj = new ChatUser;
    $user_obj->setUserEmail($_POST['user_email']);
    $user_data = $user_obj->get_user_data_by_email();
    if (is_array($user_data) && count($user_data) > 0) {
        if ($user_data['user_status'] == 'Enable') {
            if ($user_data['user_password'] == $_POST['user_password']) {
                $user_obj->setUserId($user_data['user_id']);
                $user_obj->setUserLoginStatus('Login');
                $user_token = md5(uniqid());
                $user_obj->setUserToken($user_token);
                if ($user_obj->update_user_login_data()) {
                    $_SESSION['user_data'][$user_data['user_id']] = [
                        'id' => $user_data['user_id'],
                        'name' => $user_data['user_name'],
                        'profile' => $user_data['user_profile'],
                        'token' => $user_token,
                    ];
                    header("Location:chatroom.php");
                } else {
                }
            } else {
                $error = 'Wrong user password';
            }
        } else {
            $error = 'Please verify your email address';
        }
    } else {
        $error = 'Wrong email address';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            <div class="col-md-4">
                <?php
                if (isset($_SESSION['success_message'])) {
                ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?= $_SESSION['success_message'] ?>
                    </div>
                <?php
                    unset($_SESSION['success_message']);
                }
                if ($error != '') {
                ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?= $error ?>
                    </div>
                <?php
                }
                ?>
                <div class="card">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form action="" id="login_form" method="post">
                            <div class="form-group">
                                <label for="">Enter Your E-mail</label>
                                <input type="text" name="user_email" id="user_email" data-parsley-type="email" class="form-control shadow-none" required>
                            </div>
                            <div class="form-group">
                                <label for="">Enter Your Password</label>
                                <input type="password" name="user_password" id="user_password" class="form-control shadow-none" required>
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="login" id="login" class="btn btn-success" value="Login">
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
        $('#login_form').parsley();
    </script>
</body>

</html>