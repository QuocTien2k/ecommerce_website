<?php

$db_name = 'mysql:host=localhost;dbname=e-commerce';
$user_name = 'root';
$user_password = '';

$conn = new PDO($db_name, $user_name, $user_password);

date_default_timezone_set('Asia/Ho_Chi_Minh'); // cài đặt múi giờ Việt Nam
?>