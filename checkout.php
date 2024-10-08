<?php

include 'components/connect.php';
include 'components/function.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   header('location:user_login.php');
   exit();
}

// Truy vấn người dùng
$select_user = $conn->prepare("SELECT users.name, users.email FROM users INNER JOIN cart ON cart.user_id = users.id WHERE user_id=?");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

$errors = [];
$values = [
   'name' => '',
   'number' => '',
   'email' => '',
   'method' => '',
   'province' => '',
   'district' => '',
   'ward' => '',
   'address' => '',
   'discount_code' => '',
   'total_products' => '',
   'total_price' => '',
];

// Khởi tạo $provinces
$provinces = [];
$sql = "SELECT * FROM province";
$result = $conn->query($sql);
if ($result->rowCount() > 0) {
   $provinces = $result->fetchAll(PDO::FETCH_ASSOC);
}

// Xử lý form khi gửi dữ liệu
if (isset($_POST['order'])) {
   // Lấy dữ liệu từ form và lưu vào mảng values
   $values['name'] = trim($_POST['name']);
   $values['number'] = trim($_POST['number']);
   $values['email'] = trim($_POST['email']);
   $values['method'] = trim($_POST['method']);
   $values['province'] = trim($_POST['province']);
   $values['district'] = trim($_POST['district']);
   $values['ward'] = trim($_POST['ward']);
   $values['address'] = trim($_POST['address']);
   $values['discount_code'] = isset($_POST['discount_code']) ? trim($_POST['discount_code']) : ''; // Nếu không có mã giảm giá, mặc định là 0
   $values['total_products'] = trim($_POST['total_products']);
   $values['total_price'] = trim($_POST['total_price']);



   // Validate dữ liệu
   if (empty($values['name'])) {
      $errors['name']['required'] = 'Vui lòng nhập tên tài khoản!';
   } elseif (strlen($values['name']) > 30) {
      $errors['name']['max'] = 'Tên tài khoản không được vượt quá 30 ký tự!';
   }

   if (empty($values['number'])) {
      $errors['number']['required'] = 'Vui lòng nhập số điện thoại!';
   } elseif (!preg_match('/^\d{10}$/', $values['number'])) {
      $errors['number']['format'] = 'Số điện thoại phải gồm 10 chữ số!';
   }

   if (empty($values['method']) || !in_array($values['method'], ['Thanh toán khi nhận hàng', 'Thẻ tín dụng'])) {
      $errors['method']['required'] = 'Vui lòng chọn phương thức thanh toán!';
   }

   if (empty($values['province'])) {
      $errors['province']['required'] = 'Vui lòng chọn tỉnh/thành phố!';
   }

   if (empty($values['district'])) {
      $errors['district']['required'] = 'Vui lòng chọn quận/huyện!';
   }

   if (empty($values['ward'])) {
      $errors['ward']['required'] = 'Vui lòng chọn phường/xã!';
   }

   if (empty($values['email'])) {
      $errors['email']['required'] = 'Vui lòng nhập email!';
   } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['email']['format'] = 'Email không hợp lệ!';
   }

   if (empty($values['address'])) {
      $errors['address']['required'] = 'Vui lòng nhập địa chỉ giao hàng!';
   }

   //check mã giảm giá và tính lại tổng tiền
   $grand_total = 0;
   $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $select_cart->execute([$user_id]);

   if ($select_cart->rowCount() > 0) {
      while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
         $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
      }
   }

   if (isset($values['discount_code'])) {
      // Truy vấn mã giảm giá từ cơ sở dữ liệu
      $select_discount = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND is_active = 1");
      $select_discount->execute([$values['discount_code']]);

      if ($select_discount->rowCount() > 0) {
         $discount = $select_discount->fetch(PDO::FETCH_ASSOC);

         // Kiểm tra xem mã giảm giá còn khả dụng không
         if ($discount['usage_count'] < $discount['max_usage']) {
            // Áp dụng giảm giá
            $discount_amount = $discount['discount_amount']; // Tỷ lệ phần trăm giảm giá
            $discount_value = ($grand_total * $discount_amount) / 100;
            $grand_total -= $discount_value; // Cập nhật tổng tiền sau khi giảm
         }
      } else {
         $errors['discount_code']['invalid'] = 'Mã giảm giá không hợp lệ hoặc đã hết hạn.';
      }
   }

   if (empty($errors)) {
      // Lấy tên của province
      $stmt_province = $conn->prepare("SELECT name FROM province WHERE province_id = ?");
      $stmt_province->execute([$values['province']]);
      $province_name = $stmt_province->fetchColumn();

      // Lấy tên của district
      $stmt_district = $conn->prepare("SELECT name FROM district WHERE district_id = ?");
      $stmt_district->execute([$values['district']]);
      $district_name = $stmt_district->fetchColumn();

      // Lấy tên của ward
      $stmt_ward = $conn->prepare("SELECT name FROM wards WHERE wards_id = ?");
      $stmt_ward->execute([$values['ward']]);
      $ward_name = $stmt_ward->fetchColumn();



      $address_info = [
         'address' => $values['address'],
         'ward' => $ward_name,
         'district' => $district_name,
         'province' => $province_name
      ];
      $json_address_info = json_encode($address_info);

      $placed_on = date('Y-m-d H:i:s');

      // echo "Tổng tiền sau khi giảm: " . $grand_total;
      // echo '<pre>';
      // print_r($values);
      // echo '</pre>';
      // die();

      // Thêm vào cơ sở dữ liệu
      $insert_order = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, discount_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      $insert_order->execute([
         $user_id,
         $values['name'],
         $values['number'],
         $values['email'],
         $values['method'],
         $json_address_info,
         $values['total_products'],
         $grand_total, // Sử dụng tổng tiền sau khi giảm giá
         $placed_on,
         $values['discount_code']
      ]);

      if ($insert_order) {
         // Xóa giỏ hàng sau khi đặt hàng thành công
         $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         if ($delete_cart) {
            // Cập nhật mã giảm giá nếu có
            if (!empty($values['discount_code'])) {
               // Truy vấn thông tin mã giảm giá hiện tại
               $select_discount = $conn->prepare("SELECT max_usage, usage_count FROM discount_codes WHERE code = ?");
               $select_discount->execute([$values['discount_code']]);
               $discount_info = $select_discount->fetch(PDO::FETCH_ASSOC);

               if ($discount_info) {
                  // Cập nhật usage_count
                  $new_usage_count = $discount_info['usage_count'] + 1;
                  $is_active = ($new_usage_count < $discount_info['max_usage']) ? 1 : 0;

                  $update_discount = $conn->prepare("UPDATE discount_codes SET usage_count = ?, is_active = ? WHERE code = ?");
                  $update_discount->execute([$new_usage_count, $is_active, $values['discount_code']]);
               }
            }

            $message[] = create_message('success', 'Đơn hàng đã đặt thành công.');
         } else {
            $message[] = create_message('failed', 'Có lỗi xảy ra khi xóa giỏ hàng.');
         }
      } else {
         $message[] = create_message('failed', 'Mua hàng không thành công. Xin vui lòng thử lại!');
      }
   }
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
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">
</head>

<body>
   <?php include 'components/user_header.php'; ?>

   <section class="checkout-orders">
      <form action="" method="POST">
         <h3>Đơn hàng của bạn</h3>

         <div class="display-orders">
            <?php
            $grand_total = 0;
            $cart_items = [];
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);

            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  $price = number_format((float)$fetch_cart['price'], 0, ',', '.');
                  $cart_items[] = $fetch_cart['name'] . ' (' . $price . ' x ' . $fetch_cart['quantity'] . ')';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
            ?>
                  <p> <?= $fetch_cart['name']; ?> <span>(<?= $price . 'vnđ /- x ' . $fetch_cart['quantity']; ?>)</span> </p>
            <?php
               }
            } else {
               echo '<p class="empty">Giỏ hàng đang trống!</p>';
            }
            ?>

            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" id="total_price" value="<?= $grand_total; ?>">

            <div class="grand-total">
               Tổng tiền tất cả: <span id="grand_total_display"><?= number_format($grand_total, 0, ',', '.'); ?> vnđ</span>
            </div>
         </div>

         <h3>Đặt hàng</h3>

         <div class="flex">
            <div class="inputBox">
               <span>Tên của bạn :</span>
               <input type="text" name="name" placeholder="nhập tên..." class="box" value="<?php echo htmlspecialchars($fetch_user['name'] ?? $values['name']); ?>">
               <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Số điện thoại :</span>
               <input type="number" name="number" placeholder="nhập số điện thoại..." class="box" onkeypress="if(this.value.length == 10) return false;" value="<?php echo htmlspecialchars($values['number']); ?>">
               <?php echo form_error('number', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Email :</span>
               <input type="email" name="email" placeholder="nhập email..." class="box" value="<?php echo htmlspecialchars($fetch_user['email'] ?? $values['email']); ?>">
               <?php echo form_error('email', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Thanh toán :</span>
               <select name="method" class="box">
                  <option value="">Chọn phương thức thanh toán</option>
                  <option value="Thanh toán khi nhận hàng" <?php echo isset($_POST['method']) && $_POST['method'] == 'Thanh toán khi nhận hàng' ? 'selected' : ''; ?>>Thanh toán khi nhận hàng</option>
                  <option value="Thẻ tín dụng" <?php echo isset($_POST['method']) && $_POST['method'] == 'Thẻ tín dụng' ? 'selected' : ''; ?>>Thẻ tín dụng</option>
               </select>
               <?php echo form_error('method', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>

            <div class="inputBox">
               <span>Thành phố :</span>
               <select name="province" id="province" class="box">
                  <option value="">Chọn tỉnh/thành phố</option>
                  <?php foreach ($provinces as $province): ?>
                     <option value="<?php echo $province['province_id']; ?>" <?php echo isset($_POST['province']) && $_POST['province'] == $province['province_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($province['name']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <?php echo form_error('province', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>

            <div class="inputBox">
               <span>Quận/huyện :</span>
               <select name="district" id="district" class="box">
                  <option value="">Chọn quận/huyện</option>
               </select>
               <?php echo form_error('district', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>

            <div class="inputBox">
               <span>Phường/xã :</span>
               <select name="ward" id="ward" class="box">
                  <option value="">Chọn phường/xã</option>
               </select>
               <?php echo form_error('ward', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>

            <div class="inputBox">
               <span>Địa chỉ giao hàng:</span>
               <input type="text" name="address" placeholder="địa chỉ giao hàng..." class="box" value="<?php echo htmlspecialchars($values['address']); ?>">
               <?php echo form_error('address', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>

            <!-- Thêm thẻ select để chọn mã giảm giá -->
            <div class="discount-select-container">
               <label for="discount_code">Chọn mã giảm giá:</label>
               <select name="discount_code" id="discount_code" class="discount-select">
                  <option value="">Chọn mã giảm giá</option>
                  <?php
                  // Truy vấn mã giảm giá từ cơ sở dữ liệu
                  $select_discounts = $conn->prepare("SELECT * FROM discount_codes");
                  $select_discounts->execute();
                  while ($fetch_discount = $select_discounts->fetch(PDO::FETCH_ASSOC)) {
                     $is_active = $fetch_discount['is_active'] == 1;
                     $option_class = $is_active ? '' : 'disabled-option';
                     $disabled_attr = $is_active ? '' : 'disabled';

                     echo '<option value="' . htmlspecialchars($fetch_discount['code']) . '" data-discount="' . htmlspecialchars($fetch_discount['discount_amount']) . '" ' . $disabled_attr . ' class="' . $option_class . '">' . htmlspecialchars($fetch_discount['code']) . '</option>';
                  }
                  ?>
               </select>
               <?php echo form_error('discount_code', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
         </div>

         <input type="submit" name="order" class="btn-shopping <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="Đặt hàng">
      </form>
   </section>

   <?php include 'components/footer.php'; ?>

   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script src="js/script.js?version=<?php echo rand(); ?>"></script>
   <script>
      document.getElementById('discount_code').addEventListener('change', function() {
         // Lấy tổng tiền hiện tại từ HTML
         let grandTotal = parseFloat(document.getElementById('total_price').value);
         let selectedOption = this.options[this.selectedIndex];

         // Lấy discount_amount từ thuộc tính data-discount
         let discountAmount = selectedOption.getAttribute('data-discount');

         // Lấy phần tử để cập nhật tổng tiền
         let grandTotalDisplay = document.getElementById('grand_total_display');

         // Kiểm tra nếu phần tử tồn tại
         if (grandTotalDisplay) {
            // Nếu có mã giảm giá được chọn
            if (discountAmount) {
               discountAmount = parseFloat(discountAmount); // Chuyển thành số
               let discountValue = (grandTotal * discountAmount) / 100; // Tính giá trị giảm
               let newTotal = grandTotal - discountValue; // Tính tổng tiền sau giảm

               // Cập nhật tổng tiền trên giao diện
               grandTotalDisplay.textContent = newTotal.toLocaleString('vi-VN') + ' vnđ';
            } else {
               // Nếu không chọn mã giảm giá, hiển thị lại tổng tiền ban đầu
               grandTotalDisplay.textContent = grandTotal.toLocaleString('vi-VN') + ' vnđ';
            }
         } else {
            console.error('Không tìm thấy phần tử với id "grand_total_display".');
         }
      });

      //xử lý các tỉnh thành/quận-huyện/phường-xã
      $(document).ready(function() {
         $('#province').change(function() {
            var provinceId = $(this).val();
            if (provinceId) {
               $.ajax({
                  url: 'components/get_districts.php',
                  method: 'GET',
                  data: {
                     province_id: provinceId
                  },
                  success: function(response) {
                     var districts = JSON.parse(response);
                     var districtSelect = $('#district');
                     districtSelect.empty().append('<option value="">Chọn quận/huyện</option>');
                     districts.forEach(function(district) {
                        districtSelect.append(`<option value="${district.district_id}">${district.name}</option>`);
                     });
                     $('#ward').empty().append('<option value="">Chọn phường/xã</option>');
                  },
                  error: function() {
                     alert('Có lỗi xảy ra khi tải danh sách quận/huyện.');
                  }
               });
            } else {
               $('#district').empty().append('<option value="">Chọn quận/huyện</option>');
               $('#ward').empty().append('<option value="">Chọn phường/xã</option>');
            }
         });

         $('#district').change(function() {
            var districtId = $(this).val();
            if (districtId) {
               $.ajax({
                  url: 'components/get_wards.php',
                  method: 'GET',
                  data: {
                     district_id: districtId
                  },
                  success: function(response) {
                     var wards = JSON.parse(response);
                     var wardSelect = $('#ward');
                     wardSelect.empty().append('<option value="">Chọn phường/xã</option>');
                     wards.forEach(function(ward) {
                        wardSelect.append(`<option value="${ward.wards_id}">${ward.name}</option>`);
                     });
                  },
                  error: function() {
                     alert('Có lỗi xảy ra khi tải danh sách phường/xã.');
                  }
               });
            } else {
               $('#ward').empty().append('<option value="">Chọn phường/xã</option>');
            }
         });
      });
   </script>
</body>

</html>