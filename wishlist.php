<?php

include 'components/connect.php';
include 'components/function.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
};


$errors = [];
if (isset($_POST['add_to_cart'])) {

   $qty = $_POST['qty'];
   //check
   if ($qty <= 0 || $qty > 50) {
      $errors['qty'] = 'Số lượng lớn hơn 0 và nhỏ hơn 50!';
   }
   if (empty($errors)) {
      include 'components/add_to_cart.php';
   }
}

if (isset($_POST['delete'])) {
   $wishlist_id = $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$wishlist_id]);
}

if (isset($_GET['delete_all'])) {
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>wishlist</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="products">

      <h3 class="heading">your wishlist</h3>

      <div class="box-container">

         <?php
         $grand_total = 0;
         $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
         $select_wishlist->execute([$user_id]);
         if ($select_wishlist->rowCount() > 0) {
            while ($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)) {
               // echo $fetch_wishlist['price'];
               $price = (float)$fetch_wishlist['price'];
               $grand_total += $price;
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
                  <input type="hidden" name="wishlist_id" value="<?= $fetch_wishlist['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_wishlist['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_wishlist['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_wishlist['image']; ?>">
                  <a href="quick_view.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
                  <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="">
                  <div class="name"><?= $fetch_wishlist['name']; ?></div>
                  <div class="flex">
                     <div class="price"><?= number_format($price, 0, ',', '.'); ?> vnđ</div>
                     <input type="number" name="qty" class="qty" onkeypress="if(this.value.length == 2) return false;" value="1">
                     <?php echo form_error('qty', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                  </div>
                  <input type="submit" value="Thêm vào giỏ hàng" class="btn-shopping" name="add_to_cart">
                  <input type="submit" value="Xóa sản phẩm" onclick="return confirm('Xóa khỏi mục này?');" class="delete-btn" name="delete">
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">Mục yêu thích trống!</p>';
         }
         ?>
      </div>

      <div class="wishlist-total">
         <p>Tổng tiền : <span><?= number_format($grand_total, 0, ',', '.'); ?> vnđ</span></p>
         <a href="shop.php" class="option-btn">Tiếp tục mua sắm</a>
         <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="return confirm('Bạn muốn xóa hết?');">Xóa tất cả</a>
      </div>

   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>