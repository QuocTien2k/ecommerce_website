<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:admin_login.php');
}

$errors = [];
if (isset($_POST['update'])) {

   $name = trim($_POST['name']);
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $new_pass = trim($_POST['new_pass']);
   $confirm_pass = trim($_POST['confirm_pass']);
   $old_pass = trim($_POST['old_pass']);
   $prev_pass = $_POST['prev_pass'];  // The hashed password from the database

   // Validate name
   if (empty($name)) {
      $errors['name']['required'] = 'Vui lòng nhập tên!';
   } elseif (strlen($name) < 3) {
      $errors['name']['min'] = 'Tên phải hơn 3 ký tự!';
   }

   // Validate new password
   if (empty($new_pass)) {
      $errors['new_pass']['required'] = 'Vui lòng nhập mật khẩu mới!';
   } elseif (strlen($new_pass) < 6) {
      $errors['new_pass']['min'] = 'Mật khẩu phải có ít nhất 6 ký tự!';
   }

   // Validate confirmation password
   if ($new_pass !== $confirm_pass) {
      $errors['confirm_pass']['required'] = 'Mật khẩu không trùng khớp!';
   }

   // Validate old password
   if (empty($old_pass)) {
      $errors['old_pass']['required'] = 'Vui lòng nhập mật khẩu cũ!';
   } elseif (!password_verify($old_pass, $prev_pass)) {
      $errors['old_pass']['invalid'] = 'Mật khẩu cũ không đúng!';
   }

   // If no errors, proceed with password update
   if (empty($errors)) {
      $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
      $update_at = date('Y-m-d H:i:s');

      // Update admin details in the database (e.g., name and password)
      $update_admin = $conn->prepare("UPDATE `admins` SET name = ?, password = ?, update_at = ? WHERE id = ?");
      $update_admin->execute([$name, $hashed_pass, $update_at, $admin_id]);

      if ($update_admin) {
         $message[] = create_message('success', 'Cập nhật thông tin thành công!');
      } else {
         $message[] = create_message('failed', 'Cập nhật thông tin thất bại!');
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
   <title>Cập nhật thông tin</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <h3>Cập nhật thông tin</h3>
      <input type="hidden" name="prev_pass" value="<?= $fetch_profile['password']; ?>">

      <input type="text" name="name" value="<?= $fetch_profile['name']; ?>" placeholder="nhập tên tài khoản..." class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
      
      <input type="password" name="old_pass" placeholder="nhập mật khẩu cũ..."  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <?php echo form_error('old_pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
      
      <input type="password" name="new_pass" placeholder="nhập mật khẩu mới"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">      
      <?php echo form_error('new_pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

      <input type="password" name="confirm_pass" placeholder="nhập lai mật khẩu mới"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <?php echo form_error('confirm_pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
      
      <div style="display: flex; justify-content: center;">
         <input type="submit" value="cập nhật" class="btn" name="update">
      </div>
   </form>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>