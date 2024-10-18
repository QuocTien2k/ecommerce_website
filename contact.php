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
if (isset($_POST['send'])) {

   // Lấy dữ liệu từ form và lọc
   $name = trim($_POST['name'] ?? '');

   $email = trim($_POST['email'] ?? '');
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);

   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);

   $msg = $_POST['msg'];
   $msg = filter_var($msg, FILTER_SANITIZE_STRING);

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

   // Kiểm tra số điện thoại
   if (empty($number)) {
      $errors['number']['required'] = 'Vui lòng nhập số điện thoại!';
   } elseif (!preg_match('/^0[0-9]{9}$/', $number)) {
      // Kiểm tra nếu số không bắt đầu bằng 0 hoặc không phải là chuỗi 10 chữ số
      $errors['number']['invalid'] = 'Số điện thoại không hợp lệ!';
   }

   if(empty($msg)){
      $errors['msg']['required']= 'Vui lòng nhập lời nhắn!';
   }

   if(empty($errors)){
      $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
      $select_message->execute([$name, $email, $number, $msg]);
   
      if ($select_message->rowCount() > 0) {
         $message[] = create_message('warning', 'Tin nhắn đã được gửi! Vui lòng không spam!');
      } else {
   
         $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
         $insert_message->execute([$user_id, $name, $email, $number, $msg]);

         $message[] = create_message('success', 'Cảm ơn bạn đã liên hệ. Vui lòng chờ phản hồi!');
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
   <title>Liên hệ</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="contact">

      <form action="" method="post">
         <h3>Gửi liên hệ</h3>
         <input type="text" name="name" placeholder="Họ và tên ..." class="box">
         <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="email" name="email" placeholder="Email của bạn ..." class="box">
         <?php echo form_error('email', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <input type="number" name="number" placeholder="Số điện thoại... " onkeypress="if(this.value.length == 10) return false;" class="box">
         <?php echo form_error('number', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
         
         <textarea name="msg" class="box" placeholder="Lời nhắn ..." cols="30" rows="10"></textarea>
         <?php echo form_error('msg', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

         <div class="flex">
            <input type="submit" value="Gửi" name="send" class="btn-shopping">
         </div>
      </form>

   </section>













   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>