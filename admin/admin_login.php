<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$errors=[];
if (isset($_POST['submit'])) {

   $name = trim($_POST['name'] ?? '');
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $pass = trim($_POST['pass'] ?? '');
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // Validate email, quy tắc: required, định dạng hợp lệ
   if (empty($name)) {
      $errors['name']['required'] = 'Vui lòng nhập tên tài khoản!';
   } elseif (strlen($name) > 10) {
      $errors['name']['max'] = 'Tên tài khoản không được vượt quá 20 ký tự!';
   } elseif (strlen($name) < 3 && !in_array(strtolower($name), ['admin'])) {
      // Nếu độ dài nhỏ hơn 3 ký tự và không phải 'Admin' hoặc 'admin'
      $errors['name']['min'] = 'Tên tài khoản phải có 6 ký tự!';
   }

   // Validate password, quy tắc: required
   if (empty($pass)) {
      $errors['pass']['required'] = 'Vui lòng nhập mật khẩu!';
   }

   // Kiểm tra nếu không có lỗi
   if (empty($errors)) {
      // Truy vấn cơ sở dữ liệu dựa trên tên tài khoản
      $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
      $select_admin->execute([$name]);
      $row = $select_admin->fetch(PDO::FETCH_ASSOC);

      // Kiểm tra kết quả truy vấn
      if ($row) {
         // Sử dụng password_verify() để kiểm tra mật khẩu
         if (password_verify($pass, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header('location: dashboard.php');
            exit(); // Dừng script sau khi chuyển hướng
         } else {
            $message[] = create_message('failed', 'Mật khẩu không đúng!');
         }
      } else {
         $message[] = create_message('failed', 'Tên tài khoản không tồn tại!');
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
   <title>Đăng nhập</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php
   if (isset($message)) {
      foreach ($message as $msg) {
         echo '<div class="message ' . $msg['type'] . '">
            <div class="toast">
               <i class="' . $msg['icon'] . '"></i>
               <p>' . $msg['text'] . '</p>
            </div>
          </div>';
      }
   }
   ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>Đăng nhập</h3>
         <p>Tài khoản mặc định = <span>admin</span> & password = <span>123456</span></p>
         <p>Lưu ý: vui lòng không cập nhật mật khẩu khi không cần thiết</p>
         <input type="text" name="name" placeholder="Tài khoản..." maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <input type="password" name="pass" placeholder="Mật khẩu..." maxlength="20" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <div style="display: flex; align-items: center; justify-content: center;">
            <input type="submit" value="đăng nhập" class="btn" name="submit">
         </div>
         <!-- <a href="register_admin.php" class="option-btn">register now</a> -->
      </form>

   </section>
   <script>
      document.addEventListener('DOMContentLoaded', () => {
         const messages = document.querySelectorAll('.message');
         let topOffset = 12; // Bắt đầu từ 10%

         // Hàm để ẩn thông báo với hiệu ứng slideOut
         function hideMessage(message) {
            message.style.animation = 'slideOut 1.2s ease forwards'; // Thêm animation slideOut
            setTimeout(() => {
               message.remove(); // Xóa thông báo sau khi animation kết thúc
            }, 1200); // Thời gian chờ phải bằng thời gian của animation
         }

         // Lặp qua từng thông báo và đặt thời gian để ẩn nó sau 5 giây
         messages.forEach((message, index) => {
            // Điều chỉnh top để các thông báo không chồng lên nhau
            message.style.top = `${topOffset}%`;
            topOffset += 10; // Tăng giá trị top cho thông báo tiếp theo

            setTimeout(() => {
               hideMessage(message);
            }, 4500);
         });
      });
   </script>
</body>

</html>