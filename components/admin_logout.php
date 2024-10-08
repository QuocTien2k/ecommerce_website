<?php

include './connect.php';

session_start();
unset($_SESSION['admin_id']);

header('location:../admin/admin_login.php');
exit();
?>