<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $delivery_id = $_POST['delivery_id'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? , delivery_id= ? WHERE id = ?");
   $update_payment->execute([$payment_status, $delivery_id, $order_id]);
   $message[] = create_message('success', 'Cập nhật trạng thái đơn hàng thành công!');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Đơn hàng</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="orders">

      <h1 class="heading">Đơn hàng</h1>

      <div class="order-box-container">
         <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders`");
         $select_orders->execute();
         if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
               $total_price = (float)$fetch_orders['total_price'];
               $total_price = number_format($total_price, 0, ',', '.');

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
               <div class="order-box">
                  <div class="order-details">
                     <p>Thời gian: <span><?= $fetch_orders['placed_on']; ?></span></p>
                     <p>Tên khách hàng: <span><?= $fetch_orders['name']; ?></span></p>
                     <p>Số điện thoại: (+84)<span><?= $fetch_orders['number']; ?></span></p>
                     <p>Địa chỉ: <span><?= $address . ', ' . $ward . ', ' . $district . ', ' . $province; ?></span></p>
                     <p>Sản phẩm mua: <span><?= $fetch_orders['total_products']; ?></span></p>
                     <p>Tổng thanh toán: <span><?= $total_price; ?> vnđ</span></p>
                     <p>Phương thức thanh toán: <span><?= $fetch_orders['method']; ?></span></p>
                     <form action="" method="post">
                        <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                        <select name="payment_status" class="select">
                           <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
                           <option value="Đang xử lý">Đang xử lý</option>
                           <option value="Đã hoàn thành">Đã hoàn thành</option>
                        </select>

                        <!-- Đơn vị vận chuyển -->
                        <label for="delivery_id">Chọn đơn vị vận chuyển:</label>
                        <select name="delivery_id" class="select">
                           <option selected disabled>Chọn đơn vị vận chuyển</option>
                           <?php
                           $select_delivery = $conn->prepare("SELECT * FROM delivery");
                           $select_delivery->execute();
                           while ($fetch_delivery = $select_delivery->fetch(PDO::FETCH_ASSOC)) {
                              $selected = ($fetch_orders['delivery_id'] == $fetch_delivery['id']) ? 'selected' : '';
                              echo '<option value="' . $fetch_delivery['id'] . '" ' . $selected . '>' . htmlspecialchars($fetch_delivery['name'], ENT_QUOTES) . '</option>';
                           }
                           ?>
                        </select>
                        <div class="flex-btn">
                           <input type="submit" value="Cập nhật" class="option-btn" name="update_payment">
                           <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Bạn chắc chứ?');">Xóa</a>
                        </div>
                     </form>
                  </div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Chưa có đơn hàng nào!</p>';
         }
         ?>
      </div>


   </section>

   </section>



   <script src="../js/admin_script.js"></script>

</body>

</html>