<?php

include 'components/connect.php';
include 'components/function.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

$errors = []; // Khởi tạo mảng lưu lỗi
if (isset($_POST['submit'])) {

   // Lấy và xử lý dữ liệu đầu vào
   $email = trim($_POST['email'] ?? '');
   $email = filter_var($email, FILTER_SANITIZE_STRING);

   $pass = trim($_POST['pass'] ?? '');
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);

   // Validate email, quy tắc: required, định dạng hợp lệ
   if (empty($email)) {
      $errors['email']['required'] = 'Vui lòng nhập email!';
   } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors['email']['isEmail'] = 'Email không hợp lệ!';
   }

   // Validate password, quy tắc: required
   if (empty($pass)) {
      $errors['pass']['required'] = 'Vui lòng nhập mật khẩu!';
   }

   // Nếu không có lỗi, thực hiện kiểm tra người dùng trong cơ sở dữ liệu
   if (empty($errors)) {
      // Bước 1: Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
      $check_email = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $check_email->execute([$email]);

      if ($check_email->rowCount() > 0) {
         // Bước 2: Nếu email tồn tại, kiểm tra mật khẩu
         $row = $check_email->fetch(PDO::FETCH_ASSOC);

         if (password_verify($pass, $row['password'])) {
            // Đăng nhập thành công
            $_SESSION['user_id'] = $row['id'];
            header('location:home.php');
            exit();
         } else {
            // Mật khẩu không đúng
            $message[] = create_message('failed', 'Sai mật khẩu!');
         }
      } else {
         // Email chưa được đăng ký
         $message[] = create_message('warning', 'Email chưa đăng ký, vui lòng đăng ký!');
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
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="form-container">

      <form action="" method="post">
         <h3>Đăng nhập</h3>
         <input type="email" name="email" placeholder="Nhập email của bạn..." class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
         <?php echo form_error('email', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <input type="password" name="pass" placeholder="Nhập mật khẩu của bạn..." class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?php echo htmlspecialchars($_POST['pass'] ?? '', ENT_QUOTES); ?>">
         <?php echo form_error('pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         <input type="submit" value="Đăng nhập" class="btn-shopping" name="submit">
         <p>Chưa có tài khoản?</p>
         <a href="user_register.php" class="option-btn">đăng ký</a>
      </form>
   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>