<?php
if (isset($message)) {
   foreach ($message as $msg) {
      echo '<div class="message ' . $msg['type'] . '">
         <div class="toast ">
            <i class="' . $msg['icon'] . '"></i>
            <p>' . $msg['text'] . '</p>            
         </div>
       </div>';
   }
}

$query = "SELECT option_name, option_value, display_name FROM options WHERE section = 'header'";
$stmt = $conn->prepare($query);
$stmt->execute();
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<header class="header">

   <section class="flex">
      <?php
      foreach ($options as $option) {
         if ($option['option_name'] == 'logo') {
            echo '<a href=' . $option['option_value'] . ' class="logo">' . $option['display_name'] . '<span>.</span></a>';
            break;
         }
      }
      ?>
      <nav class="navbar">
         <?php
         // Vòng lặp để hiển thị các liên kết menu
         foreach ($options as $option) {
            if ($option['option_name'] != 'logo') { // Bỏ qua logo
               echo '<a href="' . $option['option_value'] . '">' . $option['display_name'] . '</a>';
            }
         }
         ?>
      </nav>

      <div class="icons">
         <?php
         $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
         $count_wishlist_items->execute([$user_id]);
         $total_wishlist_counts = $count_wishlist_items->rowCount();

         $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $count_cart_items->execute([$user_id]);
         $total_cart_counts = $count_cart_items->rowCount();
         ?>
         <div id="menu-btn" class="fas fa-bars"></div>
         <button id="openModal" class="btn-search"><i class="fas fa-search"></i></button>
         <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $total_wishlist_counts; ?>)</span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_counts; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
         <!-- Modal -->
         <div id="searchModal" class="modal" style="margin-left: 0;">
            <div class="modal-content">
               <section class="search-form">
                  <form action="search_page.php" method="post">
                     <input type="text" name="search_box" placeholder="tìm kiếm vd: OppoA71..." maxlength="100" class="box">
                     <button type="submit" class="fas fa-search" name="search_btn"></button>
                  </form>
               </section>
            </div>
         </div>
      </div>

      <div class="profile">
         <?php
         $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
         $select_profile->execute([$user_id]);
         if ($select_profile->rowCount() > 0) {
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
            <p><?= $fetch_profile["name"]; ?></p>
            <a href="update_user.php" class="btn-shopping">cập nhật</a>

            <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('bạn muốn đăng xuất?');">đăng xuất</a>
         <?php
         } else {
         ?>
            <p>Vui lòng đăng ký hoặc đăng nhập!</p>
            <div class="flex-btn">
               <a href="user_register.php" class="option-btn">đăng ký</a>
               <a href="user_login.php" class="option-btn">đăng nhập</a>
            </div>
            <!-- <a href="seller/seller_login.php" class="option-btn">Bạn là nhà bán hàng?</a> -->
         <?php
         }
         ?>
      </div>

   </section>

</header>