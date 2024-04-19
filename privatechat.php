<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header("location:index.php");
}
require_once "database/ChatUser.php";
require_once "database/ChatRooms.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome 5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/parsley/parsley.css">
    <style type="text/css">
        html,
        body {
            height: 100%;
            width: 100%;
            margin: 0;
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
            height: 75vh;
            overflow-y: auto;
            background-color: #e6e6e6;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-md-4 col-sm-5" style="background-color: #f1f1f1; height: 100vh; border-right: 1px solid #ccc;">
                <?php
                $login_user_id = '';
                $token = '';
                foreach ($_SESSION['user_data'] as $key => $value) {
                    $login_user_id = $value['id'];
                    $token = $value['token'];
                ?>
                    <input type="hidden" name="login_user_id" id="login_user_id" value="<?= $login_user_id ?>">
                    <input type="hidden" name="is_active_chat" id="is_active_chat" value="No">
                    <div class="mt-3 mb-3 text-center">
                        <img src="<?= $value['profile'] ?>" width="150" class="img-fluid rounded-circle img-thumbnail" alt="">
                        <h3 class="mt-2"><?= $value['name'] ?></h3>
                        <a href="profile.php" class="btn btn-secondary mt-2 mb-2">Edit</a>
                        <input type="button" class="btn btn-primary my-2" name="logout" id="logout" value="Logout">
                    </div>
                <?php
                }
                $user_obj = new ChatUser;
                $user_obj->setUserId($login_user_id);
                $user_data = $user_obj->get_user_all_data_with_status_count();
                ?>
                <div class="list-group" style="max-height: 100vh; margin-bottom: 10px; overflow-y: scroll; -webkit-overflow-scrolling: touch;">

                    <?php
                    foreach ($user_data as $key => $user) {
                        $icon = '<i class="fas fa-circle text-danger"></i>';
                        if ($user['user_login_status'] == "Login") {
                            $icon = '<i class="fas fa-circle text-success"></i>';
                        }

                        if ($user['user_id'] != $login_user_id) {
                            if ($user['count_status'] > 0) {
                                $total_unread_message = '<span class="badge badge-danger badge-pill">' . $user['count_status'] . '</span>';
                            } else {
                                $total_unread_message = '';
                            }
                            echo '
                            <a class="list-group-item list-group-item-action select_user" style="cursor: pointer;" data-userid="' . $user['user_id'] . '"> 
                                <img src="' . $user['user_profile'] . '" class="img-fluid rounded-circle img-thumbnail" width="50">
                                <span class="ms-1">
                                    <strong>
                                        <span id="list_user_name_' . $user['user_id'] . '">' . $user['user_name'] . '</span>
                                        <span id="userid_' . $user['user_id'] . '">' . $total_unread_message . '</span>
                                    </strong>
                                </span>
                                <span class="mt-2 float-end" id="userstatus_' . $user['user_id'] . '">' . $icon . '</span>
                            </a>
                            ';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="col-lg-9 col-md-8 col-sm-7">
                <br>
                <h3 class="text-center">Real Time One to One Chat App using Ratchet Websockets With PHP MYSQl</h3>
                <hr>
                <br>
                <div id="chat_area">

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

            var reciever_userid = '';

            var conn = new WebSocket('ws://localhost:8080?token=<?= $token  ?>');

            conn.onopen = function(e) {
                console.log("Connection established!");
            };

            conn.onmessage = function(e) {
                var data = JSON.parse(e.data);
                if (data.status_type == 'Online') {
                    $("#userstatus_" + data.user_id_status).html('<i class="fas fa-circle text-success"></i>');
                } else if (data.status_type == 'Offline') {
                    $("#userstatus_" + data.user_id_status).html('<i class="fas fa-circle text-danger"></i>');
                } else {


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

                    if (reciever_userid == data.userId || data.from == 'Me') {
                        if ($("#is_active_chat").val == 'Yes') {
                            var html_data = "<div class='" + row_class + "'><div class='col-sm-10'><div class='shadow-sm alert " + background_class + "'><b>" + data.from + " - </b> " + data.msg + "<br><div class='text-end'><small><i>" + data.datetime + "</i></small></div></div></div></div>";
                            $("#messages_area").append(html_data);
                            $("#messages_area").scrollTop($("#messages_area")[0].scrollHeight);
                            $("#chat_message").val('');
                        }
                    } else {
                        var count_chat = $('#userid' + data.userId).text();
                        if (count_chat == '') {
                            count_chat = 0;
                        }
                        count_chat++;
                        $('#userid' + data.userId).html('<span class="badge badge-danger badge-pill">' + count_chat + '</span>')
                    }
                }
            };

            conn.onclose = function(e) {
                console.log('Connection Close');
            }

            function make_chat_area(user_name) {
                var html = `
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col col-sm-6">
                                <b>Chat With <span class="text-danger" id="chat_user_name">` + user_name + `</span></b>
                            </div>
                            <div class="col col-sm-6 text-end">
                                <a href="chatroom.php" class="btn btn-success btn-sm">Group Chat</a>
                                <button type="button" class="close btn-close" id="close_chat_area" data-dismiss="alert" aria-label="close">
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="messages_area">

                    </div>
                </div>
                <form action="" id="chat_form" method="post" data-parsley-errors-container="#validation_error">
                    <div class="input-group mb-3" style="height: 7vh">
                        <textarea name="chat_message" class="form-control shadow-none" id="chat_message" placeholder="Type Message Here..." data-parsley-maxlength="1000" required></textarea>
                        <div class="input-group-append">
                            <button type="submit" name="send" id="send" class="btn btn-primary shadow-none"><i class="fas fa-paper-plane"></i></button>
                        </div>
                    </div>
                    <div id="validation_error"></div>
                </form>
                `;

                $("#chat_area").html(html);
                $("#chat_form").parsley();
            }

            $(document).on('click', '.select_user', function() {
                reciever_userid = $(this).data('userid');
                var from_user_id = $("#login_user_id").val();
                var reciever_user_name = $("#list_user_name_" + reciever_userid).text();
                $('.select_user.active').removeClass("active");
                $(this).addClass("active");
                make_chat_area(reciever_user_name);
                $("#is_active_chat").val('Yes');

                $.ajax({
                    url: "action.php",
                    method: "POST",
                    data: {
                        action: 'fetch_chat',
                        to_user_id: reciever_userid,
                        from_user_id: from_user_id,
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            var html_data = '';
                            for (var count = 0; count < data.length; count++) {
                                var row_class = '';
                                var background_class = '';
                                var user_name = '';
                                if (data[count].from_user_id == from_user_id) {
                                    row_class = 'row justify-content-end';
                                    background_class = 'alert-success';
                                    user_name = 'Me';
                                } else {
                                    row_class = 'row justify-content-start';
                                    background_class = 'text-dark alert-light';
                                    user_name = data[count].from_user_name;
                                }
                                html_data = "<div class='" + row_class + "'><div class='col-sm-10'><div class='shadow-sm alert " + background_class + "'><b>" + user_name + " - </b> " + data[count].chat_message + "<br><div class='text-end'><small><i>" + data[count].timestamp + "</i></small></div></div></div></div>";
                            }
                            $("#userid_" + reciever_userid).html('');
                            $("#messages_area").html(html_data);
                            $("#messages_area").scrollTop($("#messages_area")[0].scrollHeight);
                        }
                    }
                });
            });

            $(document).on('click', '#close_chat_area', function() {
                $('#chat_area').html('');
                $(".select_user.active").removeClass("active");
            });

            $(document).on('click', '#chat_form', function(event) {
                event.preventDefault();
                if ($("#chat_form").parsley().isValid()) {
                    var user_id = $("#login_user_id").val();
                    var message = $("#chat_message").val();
                    var data = {
                        userId: user_id,
                        msg: message,
                        reciever_userid: reciever_userid,
                        command: 'private',
                    };
                    conn.send(JSON.stringify(data));
                    // $("#messages_area").scrollTop($("#messages_area")[0].scrollHeight);
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
                            conn.close();
                            location = "index.php";
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>