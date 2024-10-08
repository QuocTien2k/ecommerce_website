<?php

include 'connect.php';

session_start();
unset($_SESSION['user_id']);

header('location:../home.php');
exit();
?>