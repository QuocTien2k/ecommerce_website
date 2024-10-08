<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="orders">

      <h1 class="heading">Đơn hàng của bạn</h1>

      <div class="box-container">

         <?php
         if ($user_id == '') {
            echo '<p class="empty">Vui lòng đăng nhập!</p>';
         } else {
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
            $select_orders->execute([$user_id]);
            if ($select_orders->rowCount() > 0) {
               while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  $price = (float)$fetch_orders['total_price'];
                  $price = number_format($price, 0, ',', '.');

                  // Giải mã chuỗi JSON
                  $address_info = json_decode($fetch_orders['address'], true);

                  // Kiểm tra nếu giải mã JSON thành công
                  if (json_last_error() === JSON_ERROR_NONE) {
                     $address = isset($address_info['address']) ? htmlspecialchars($address_info['address']) : '';
                     $ward = isset($address_info['ward']) ? htmlspecialchars($address_info['ward']) : '';
                     $district = isset($address_info['district']) ? htmlspecialchars($address_info['district']) : '';
                     $province = isset($address_info['province']) ? htmlspecialchars($address_info['province']) : '';
                  } else {
                     $address = $fetch_orders['address'];
                     $ward = $district = $province = '';
                  }
         ?>
                  <div class="box">
                     <p>Thời gian đặt hàng: <span><?= $fetch_orders['placed_on']; ?></span></p>
                     <p>Tên : <span><?= $fetch_orders['name']; ?></span></p>
                     <p>email : <span><?= $fetch_orders['email']; ?></span></p>
                     <p>Số điện thoại : (+84)<span><?= $fetch_orders['number']; ?></span></p>
                     <p>Địa chỉ : <span><?= $address . ', ' . $ward . ', ' . $district . ', ' . $province; ?></span></p>
                     <p>Phương thức thanh toán : <span><?= $fetch_orders['method']; ?></span></p>
                     <p>Sản phẩm đã đặt : <span><?= $fetch_orders['total_products']; ?></span></p>
                     <p>Tổng tiền : 
                        <span><?= $price; ?> vnđ</span> 
                        <?php if($fetch_orders['discount_code'] != ''){ echo '<span style="color: red"> (đã áp dụng mã giảm giá)</span>';} ?>
                     </p>
                     <?php 
                        if($fetch_orders['delivery_id'] != Null ){
                           $select_delivery_name= $conn->prepare("SELECT delivery.name FROM orders INNER JOIN delivery ON orders.delivery_id = delivery.id");
                           $select_delivery_name->execute([]);

                           if($select_delivery_name->rowCount() >0){
                              $delivery_name= $select_delivery_name->fetch(PDO::FETCH_ASSOC);
                              echo '<p>Đơn vị vận chuyển: '.htmlspecialchars($delivery_name['name'], ENT_QUOTES).'</p>';
                           }
                        }
                     ?>

                     <p>Trạng thới đơn hàng :
                        <?php 
                           if($fetch_orders['payment_status'] == 'đang chờ'){
                              echo '<span style="red">'.$fetch_orders['payment_status'].'</span>';
                           }else{
                              echo '<span style="green">'.$fetch_orders['payment_status'].'</span>';
                           }
                        ?>
                     </p>
                  </div>
         <?php
               }
            } else {
               echo '<p class="empty">Bạn chưa có đơn hàng nào!</p>';
            }
         }
         ?>

      </div>

   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>