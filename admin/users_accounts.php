<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}


if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_user->execute([$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_orders->execute([$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
   $delete_messages->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tài khoản khách hàng</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="accounts">

      <h1 class="heading">khách hàng</h1>

      <div class="user-container">
         <?php
         $select_accounts = $conn->prepare("SELECT * FROM `users`");
         $select_accounts->execute();
         if ($select_accounts->rowCount() > 0) {
         ?>
            <table border="1" cellpadding="10" cellspacing="0">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Tên</th>
                     <th>Email</th>
                     <th>Loại người dùng</th>
                     <th>Hành động</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  while ($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)) {
                  ?>
                     <tr>
                        <td><?= $fetch_accounts['id']; ?></td>
                        <td><?= $fetch_accounts['name']; ?></td>
                        <td><?= $fetch_accounts['email']; ?></td>
                        <td><?= $fetch_accounts['user_type']; ?></td>
                        <td>
                           <a href="users_accounts.php?delete=<?= $fetch_accounts['id']; ?>" onclick="return confirm('Xóa tài khoản này đồng thời thông tin người dùng cũng bị xóa!')">Xóa</a>
                        </td>
                     </tr>
                  <?php
                  }
                  ?>
               </tbody>
            </table>
         <?php
         } else {
            echo '<p>Chưa có khách hàng nào!</p>';
         }
         ?>


      </div>

   </section>


   <script src="../js/admin_script.js"></script>

</body>

</html>