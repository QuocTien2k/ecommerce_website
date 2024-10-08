<?php

include './connect.php';

session_start();
unset($_SESSION['seller_id']);

header('location:../seller/seller_login.php');
exit();
?>