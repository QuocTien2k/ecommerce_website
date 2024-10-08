<?php

include 'components/connect.php';
include 'components/function.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

$errors = [];
if (isset($_POST['add_to_cart'])) {

   if ($user_id == '') {
      header('location:user_login.php');
      exit();
   } else {
      $qty = $_POST['qty'];
      //check
      if ($qty <= 0 || $qty > 50) {
         $errors['qty'] = 'Số lượng lớn hơn 0 và nhỏ hơn 50!';
      }
      if (empty($errors)) {
         include 'components/add_to_cart.php';
      }
   }
}
if(isset($_POST['add_to_wishlist'])){
   if ($user_id == '') {
      header('location:user_login.php');
      exit();
   }else{
      include 'components/wishlist_cart.php';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Mua sắm</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="products">

      <h1 class="heading">Mua sắm</h1>

      <div class="box-container">

         <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
               $price = (float)$fetch_product['price'];
               $price = number_format($price, 0, ',', '.');
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
                  <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
                  <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
                  <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
                  <div class="name"><?= $fetch_product['name']; ?></div>
                  <div class="flex">
                     <div class="price"><?= $price; ?><span> vnđ</span></div>
                     <input type="number" name="qty" class="qty" onkeypress="if(this.value.length == 2) return false;" value="1">
                     <?php echo form_error('qty', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                  </div>
                  <input type="submit" value="Thêm vào giỏ hàng" class="btn-shopping" name="add_to_cart">
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">no products found!</p>';
         }
         ?>

      </div>

   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>