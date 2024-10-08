<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>dashboard</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

      <div class="box">
         <h3>welcome!</h3>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="update_profile.php" class="btn-admin">Cập nhật</a>
      </div>

      <div class="box">
         <?php
            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_pendings->execute(['pending']);
            if($select_pendings->rowCount() > 0){
               while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
                  $total_pendings += $fetch_pendings['total_price'];
                  $total_pendings = number_format($total_pendings, 0 , ',' , '.');
               }
            }
         ?>
         <h3><span></span><?= $total_pendings; ?><span>vnđ</span></h3>
         <p>Số đơn đang chờ</p>
         <a href="placed_orders.php" class="btn-admin">xem</a>
      </div>

      <div class="box">
         <?php
            $total_completes = 0;
            $select_completes = $conn->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
            $select_completes->execute(['completed']);
            if($select_completes->rowCount() > 0){
               while($fetch_completes = $select_completes->fetch(PDO::FETCH_ASSOC)){
                  $total_completes += $fetch_completes['total_price'];
                  $total_completes = number_format($total_completes, 0 , ',' , '.');
               }
            }
         ?>
         <h3><span></span><?= $total_completes; ?><span>vnđ</span></h3>
         <p>Trạng thái đơn hàng</p>
         <a href="placed_orders.php" class="btn-admin">xem</a>
      </div>

      <div class="box">
         <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $number_of_orders = $select_orders->rowCount()
         ?>
         <h3><?= $number_of_orders; ?></h3>
         <p>Đơn đã đặt</p>
         <a href="placed_orders.php" class="btn-admin">xem</a>
      </div>

      <div class="box">
         <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $number_of_products = $select_products->rowCount()
         ?>
         <h3><?= $number_of_products; ?></h3>
         <p>Sản phẩm</p>
         <a href="products.php" class="btn-admin">Xem</a>
      </div>

      <div class="box">
         <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $number_of_users = $select_users->rowCount()
         ?>
         <h3><?= $number_of_users; ?></h3>
         <p>Tài khoản khách hàng</p>
         <a href="users_accounts.php" class="btn-admin">xem</a>
      </div>

      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `messages`");
            $select_messages->execute();
            $number_of_messages = $select_messages->rowCount()
         ?>
         <h3><?= $number_of_messages; ?></h3>
         <p>Tin nhắn</p>
         <a href="messagess.php" class="btn-admin">Xem</a>
      </div>

      <div class="box">
      <?php
            $select_products_categories = $conn->prepare("SELECT * FROM `product_categories`");
            $select_products_categories->execute();
            $number_of_products_categories = $select_products_categories->rowCount()
         ?>
         <h3><?= $number_of_products_categories; ?></h3>
         <p>Danh mục</p>
         <a href="category.php" class="btn-admin">Xem</a>
      </div>

      <div class="box">
         <p>Thiết lập trang</p>
         <a href="options.php" class="btn-admin">thiết lập</a>
      </div>

      <div class="box">
         <?php
            $select_messages = $conn->prepare("SELECT * FROM `delivery`");
            $select_messages->execute();
            $number_of_messages = $select_messages->rowCount()
         ?>
         <h3><?= $number_of_messages; ?></h3>
         <p>Đối tác</p>
         <a href="delivery.php" class="btn-admin">Xem</a>
      </div>

      <div class="box">
         <?php
            $select_discount = $conn->prepare("SELECT * FROM `discount_codes`");
            $select_discount->execute();
            $number_of_discount = $select_discount->rowCount()
         ?>
         <h3><?= $number_of_discount; ?></h3>
         <p>mã giảm giá</p>
         <a href="discount.php" class="btn-admin">Xem</a>
      </div>

   </div>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>