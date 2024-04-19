<?php
// make database connection
class Database_connection
{
    function connect()
    {
        $connect = new PDO("mysql:host=localhost; dbname=chat_app_php", "root", "");
        return $connect;
    }
}
