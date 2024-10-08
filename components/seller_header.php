<?php
   if (isset($message)) {
      foreach ($message as $msg) {
          echo '<div class="message ' . $msg['type'] . '">
            <div class="toast">
               <i class="'.$msg['icon'].'"></i>
               <p>' . $msg['text'] . '</p>
            </div>
          </div>';
      }
  }

?>

<header class="header">

   <section class="flex">

      <a href="../seller/seller_dashboard.php" class="logo">Seller<span>Panel</span></a>

      <nav class="navbar">
         <a href="../admin/dashboard.php">Trang chủ</a>
         <a href="../admin/products.php">Sản phẩm</a>
         <a href="../admin/placed_orders.php">Đơn hàng</a> 
         <a href="../admin/messages.php">Tin nhắn</a> 
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$seller_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="../seller/update_profile.php" class="btn-admin">Cập nhật</a>
         <!-- <div class="flex-btn">
            <a href="../admin/register_admin.php" class="option-btn">register</a>
            <a href="../admin/admin_login.php" class="option-btn">Đăng nhập</a>
         </div> -->
         <a href="../components/seller_logout.php" class="delete-btn" onclick="return confirm('Bạn muốn đăng xuất?');">Đăng xuất</a> 
      </div>

   </section>

</header>