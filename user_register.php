<?php

include 'components/connect.php';
include 'components/function.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

$errors = []; // Khởi tạo mảng lỗi
if (isset($_POST['submit'])) {

   // Lấy dữ liệu từ form và lọc
   $name = trim($_POST['name'] ?? '');

   $email = trim($_POST['email'] ?? '');
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);

   $pass = trim($_POST['pass'] ?? '');
   $cpass = trim($_POST['cpass'] ?? '');

   // Kiểm tra tên
   if (empty($name)) {
      $errors['name']['required'] = 'Vui lòng nhập tên!';
   } elseif (strlen($name) < 3) {
      $errors['name']['min'] = 'Tên phải hơn 3 ký tự!';
   }

   // Kiểm tra email
   if (empty($email)) {
      $errors['email']['required'] = 'Vui lòng nhập email!';
   } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email']['isEmail'] = 'Email không hợp lệ!';
   }

   // Kiểm tra mật khẩu
   if (empty($pass)) {
      $errors['pass']['required'] = 'Vui lòng nhập mật khẩu!';
   } elseif ($pass !== $cpass) {
      $errors['cpass']['match'] = 'Mật khẩu không trùng khớp!';
   } else {
      $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
   }

   // Nếu không có lỗi, tiếp tục xử lý
   if (empty($errors)) {
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select_user->execute([$email]);

      if ($select_user->rowCount() > 0) {
         $message[] = create_message('warning', 'Email đã tồn tại!');
      } else {
         $create_at = date('Y-m-d H:i:s');
         $user_type= 'customer';
         $insert_user = $conn->prepare("INSERT INTO `users` (name, email, password, create_at) VALUES (?, ?, ?, ?, ?)");
         $insert_user->execute([$name, $email, $hashed_pass, $user_type, $create_at]);

         $message[] = create_message('success', 'Đăng ký thành công, bạn sẽ được chuyển tới trang đăng nhập!');

         if ($insert_user) {
            echo '<script>
               setTimeout(function(){
                  window.location.href= "user_login.php";
               }, 4500);
         </script>';
            exit();
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
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>Đăng ký</h3>
         <input type="text" name="name" placeholder="Nhập tên của bạn..." class="box" value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>">
         <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="email" name="email" placeholder="Nhập email của bạn..." class="box" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('email', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="password" name="pass" placeholder="Nhập mật khẩu của bạn..." class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="password" name="cpass" placeholder="Xác nhận mật khẩu..." class="box" oninput="this.value = this.value.replace(/\s/g, '')">
         <?php echo form_error('cpass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="submit" value="đăng ký" class="btn-shopping" name="submit">
         <p>Đã có tài khoản?</p>
         <a href="user_login.php" class="option-btn">đăng nhập ngay</a>
      </form>


   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>