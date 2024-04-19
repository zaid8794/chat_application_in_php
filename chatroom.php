<?php

session_start();

if (!isset($_SESSION['user_data'])) {
    header("Location:index.php");
}
require_once "database/ChatUser.php";
require_once "database/ChatRooms.php";

$chat_room_obj = new ChatRooms;
$chat_data = $chat_room_obj->get_all_chat_data();
$user_obj = new ChatUser;
$user_data = $user_obj->get_user_all_data();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatroom</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome 5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/parsley/parsley.css">
    <style type="text/css">
        html,
        body {
            height: 100%;
            width: 100%;
        }

        #wrapper {
            display: flex;
            flex-flow: column;
            height: 100%;
        }

        #remaining {
            flex-grow: 1;
        }

        #messages {
            height: 200px;
            background: whitesmoke;
            overflow: auto;
        }

        #chat-room-frm {
            margin-top: 10px;
        }

        #user_list {
            height: 450px;
            overflow-y: auto;
        }

        #messages_area {
            height: 650px;
            overflow-y: auto;
            background-color: #e6e6e6;
        }
    </style>
</head>

<body>

    <div class="container">
        <br>
        <h3 class="text-center">PHP Chat Application using Websocket</h3>
        <br>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col col-sm-6">
                                <h3>Chatroom</h3>
                            </div>
                            <div class="col col-sm-6 text-end">
                                <a href="privatechat.php" class="btn btn-success btn-sm">Private Chat</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="messages_area">
                        <?php
                        foreach ($chat_data as $chat) {
                            if (isset($_SESSION['user_data'][$chat['userid']])) {
                                $from = "Me";
                                $row_class = 'row justify-content-end';
                                $background_class = 'alert-success';
                            } else {
                                $from = $chat['user_name'];
                                $row_class = 'row justify-content-start';
                                $background_class = 'text-dark alert-light';
                            }
                            echo
                            '<div class="' . $row_class . '">
                                <div class="col-sm-10">
                                    <div class="shadow-sm alert ' . $background_class . '">
                                        <b>' . $from . ' - </b> ' . $chat['msg'] . '<br>
                                        <div class="text-end">
                                            <small><i>' . $chat['created_on'] . '</i></small>
                                        </div>
                                    </div>    
                                </div>
                            </div>
                            ';
                        }
                        ?>
                    </div>
                </div>
                <form action="" id="chat_form" method="post">
                    <div class="input-group mb-3">
                        <textarea name="chat_message" class="form-control shadow-none" id="chat_message" placeholder="Type Message Here..." data-parsley-maxlength="1000" required></textarea>
                        <div class="input-group-append">
                            <button type="submit" name="send" id="send" class="btn btn-primary shadow-none"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                    <div id="validation_error"></div>
                </form>
            </div>
            <div class="col-lg-4">
                <?php
                $login_user_id = '';
                foreach ($_SESSION['user_data'] as $key => $value) {
                    $login_user_id = $value['id'];
                ?>
                    <input type="hidden" name="login_user_id" id="login_user_id" value="<?= $login_user_id ?>">
                    <div class="mt-3 mb-3 text-center">
                        <img src="<?= $value['profile'] ?>" width="150" class="img-fluid rounded-circle img-thumbnail" alt="">
                        <h3 class="mt-2"><?= $value['name'] ?></h3>
                        <a href="profile.php" class="btn btn-secondary mt-2 mb-2">Edit</a>
                        <input type="button" class="btn btn-primary my-2" name="logout" id="logout" value="Logout">
                    </div>
                <?php
                }
                ?>
                <div class="card mt-3">
                    <div class="card-header">
                        User List
                    </div>
                    <div class="card-body" id="user-list">
                        <div class="list-group list-group-flush">
                            <?php
                            if (count($user_data) > 0) {
                                foreach ($user_data as $key => $user) {
                                    $icon = '<i class="fas fa-circle text-danger"></i>';
                                    if ($user['user_login_status'] == "Login") {
                                        $icon = '<i class="fas fa-circle text-success"></i>';
                                    }

                                    if ($user['user_id'] != $login_user_id) {
                                        echo '
                                        <a class-"list-group-item list-group-item-action my-5">
                                            <img src="' . $user['user_profile'] . '" class="img-fluid rounded-circle img-thumbnail" width="50">
                                            <span class="ms-1"><strong>' . $user['user_name'] . '</strong></span>
                                            <span class="mt-2 float-end">' . $icon . '</span>
                                        </a>
                                        ';
                                    }
                                }
                            }
                            ?>
                        </div>
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
        $(document).ready(function() {

            var conn = new WebSocket('ws://localhost:8080');
            conn.onopen = function(e) {
                console.log("Connection established!");
            };

            conn.onmessage = function(e) {
                console.log(e.data);
                var data = JSON.parse(e.data);
                var row_class = '';
                var background_class = '';

                if (data.from == 'Me') {
                    // row_class = 'row justify-content-start';
                    row_class = 'row justify-content-end';
                    background_class = 'alert-success';
                } else {
                    // row_class = 'row justify-content-end';
                    row_class = 'row justify-content-start';
                    background_class = 'text-dark alert-light';

                }

                var html_data = "<div class='" + row_class + "'><div class='col-sm-10'><div class='shadow-sm alert " + background_class + "'><b>" + data.from + " - </b> " + data.msg + "<br><div class='text-end'><small><i>" + data.dt + "</i></small></div></div></div></div>";

                $("#messages_area").append(html_data);
                $("#chat_message").val('');
            };

            $("#chat_form").parsley();

            $("#messages_area").scrollTop($("#messages_area")[0].scrollHeight);

            $("#chat_form").on('submit', function(e) {
                e.preventDefault();

                if ($("#chat_form").parsley().isValid()) {
                    var user_id = $("#login_user_id").val();
                    var message = $("#chat_message").val();
                    var data = {
                        user_id: user_id,
                        msg: message,
                    };
                    conn.send(JSON.stringify(data));
                    $("#messages_area").scrollTop($("#messages_area")[0].scrollHeight);
                }
            });

            $("#logout").click(function() {
                var user_id = $("#login_user_id").val();
                $.ajax({
                    url: "action.php",
                    method: "POST",
                    data: {
                        user_id: user_id,
                        action: 'leave',
                    },
                    success: function(data) {
                        var response = JSON.parse(data);
                        if (response.status == 1) {
                            location = "index.php";
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>