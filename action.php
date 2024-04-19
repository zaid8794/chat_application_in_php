<?php
session_start();
if (isset($_POST['action']) && $_POST['action'] == 'leave') {
    require_once "database/ChatUser.php";
    $user_obj = new ChatUser;
    $user_obj->setUserId($_POST['user_id']);
    $user_obj->setUserLoginStatus('Logout');
    if ($user_obj->update_user_login_data()) {
        unset($_SESSION['user_data']);
        session_destroy();
        echo json_encode(['status' => 1]);
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'fetch_chat') {
    require_once "database/PrivateChat.php";
    $private_chat_obj = new PrivateChat;
    $private_chat_obj->setFromUserId($_POST['to_user_id']);
    $private_chat_obj->setToUserId($_POST['from_user_id']);
    $private_chat_obj->change_chat_status();
    echo json_encode($private_chat_obj->get_all_chat_data());
}
