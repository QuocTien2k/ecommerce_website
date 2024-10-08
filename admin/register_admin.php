<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$errors = [];
if (isset($_POST['submit'])) {

   $name = trim($_POST['name']);
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $pass = trim($_POST['pass']);
   $cpass = trim($_POST['cpass']);

   // Validate tên tài khoản, quy tắc: required, max length
   if (empty($name)) {
      $errors['name']['required'] = 'Vui lòng nhập tên tài khoản!';
   } elseif (strlen($name) > 20) { // Giới hạn độ dài tối đa (20 ký tự)
      $errors['name']['max'] = 'Tên tài khoản không được vượt quá 20 ký tự!';
   }

   // Validate mật khẩu, quy tắc: required, min length
   if (empty($pass)) {
      $errors['pass']['required'] = 'Vui lòng nhập mật khẩu!';
   } elseif (strlen($pass) < 6) { // Đảm bảo mật khẩu có ít nhất 6 ký tự
      $errors['pass']['min'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
   }
   // Kiểm tra mật khẩu khớp
   if ($pass != $cpass) {
      $errors['cpass']['required'] = 'Mật khẩu không trùng khớp!';
   } else {
      // Mã hóa mật khẩu bằng password_hash() thay vì SHA-1
      $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
   }

   // Kiểm tra nếu không có lỗi
   if (empty($errors)) {
      // Kiểm tra tên tài khoản trùng lặp
      $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
      $select_admin->execute([$name]);

      if ($select_admin->rowCount() > 0) {
         $message[] = create_message('warning', 'Tên tài khoản đã tồn tại!');
      } else {
         // Thêm tài khoản mới vào cơ sở dữ liệu
         $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?, ?)");
         $insert_admin->execute([$name, $hashed_pass]);
         $message[] = create_message('success', 'Bạn đã đăng ký Admin thành công!');

         if($insert_admin){
            echo '<script>
               setTimeout(function(){
                  window.location.href= "admin_login.php";
               }, 4500);
         </script>';
         }
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
   <title>register admin</title>

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
         <h3>register now</h3>
         <input type="text" name="name" placeholder="enter your username" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <input type="password" name="pass" placeholder="enter your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <input type="password" name="cpass" placeholder="confirm your password" class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('cpass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?> 
         <input type="submit" value="register now" class="btn" name="submit">
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