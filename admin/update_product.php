<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

$errors = [];
if (isset($_POST['update'])) {

   $pid = $_POST['pid'];

   $name = trim($_POST['name']);
   $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

   $price = trim($_POST['price']);
   $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

   $details = trim($_POST['details']);
   $details = filter_var($details, FILTER_SANITIZE_STRING);

   $category_id= $_POST['category_id'];
   if(isset($current_category_id) && $current_category_id !=0){
      //Nếu mà dữ liệu cũ còn thì ko validate
      $category_id = $current_category_id;
   }else{
      if($category_id == 0){
         $errors['product_category']['required'] = 'Vui lòng chọn danh mục sản phẩm!';   
      }
   }

   // Validate name
   if (empty($name)) {
      $errors['name']['required'] = 'Vui lòng nhập tên sản phẩm!';
   } elseif (strlen($name) < 2) {
      $errors['name']['min'] = 'Tên sản phẩm phải có ít nhất 2 ký tự!';
   }

   // Validate price
   if (empty($price)) {
      $errors['price']['required'] = 'Vui lòng nhập giá tiền!';
   } elseif ($price > 999999999) {
      $errors['price']['max'] = 'Giá tiền không được vượt quá 999,999,999!';
   }

   // Validate details
   if (empty($details)) {
      $errors['details']['required'] = 'Vui lòng nhập mô tả sản phẩm!';
   }

   if (empty($errors)) {
      $update_product = $conn->prepare("UPDATE `products` SET name = ?, price = ?, details = ? , category_id= ? WHERE id = ?");
      $update_success = $update_product->execute([$name, $price, $details, $category_id, $pid]);

      if ($update_success) {
         $message[] = create_message('success', 'Sản phẩm đã cập nhật!');
      } else {
         $message[] = create_message('failed', 'Cập nhật sản phẩm thất bại!');
      }
   }

   // Handle image updates
   // Image 01
   $old_image_01 = $_POST['old_image_01'];
   if (!empty($_FILES['image_01']['name'])) {
      if (validate_image($_FILES['image_01'], $errors, 'image_01')) {
         // Delete old image
         $old_image_path_01 = '../uploaded_img/' . $old_image_01;
         if (file_exists($old_image_path_01)) {
            unlink($old_image_path_01);
         }
         // Upload new image
         $new_image_01 = upload_image($_FILES['image_01'], '../uploaded_img/');

         // Update the new image path in the database
         $update_image_01 = $conn->prepare("UPDATE `products` SET image_01 = ? WHERE id = ?");
         $update_image_01->execute([$new_image_01, $pid]);
      }
   }

   // Image 02
   $old_image_02 = $_POST['old_image_02'];
   if (!empty($_FILES['image_02']['name'])) {
      if (validate_image($_FILES['image_02'], $errors, 'image_02')) {
         // Delete old image
         $old_image_path_02 = '../uploaded_img/' . $old_image_02;
         if (file_exists($old_image_path_02)) {
            unlink($old_image_path_02);
         }
         // Upload new image
         $new_image_02 = upload_image($_FILES['image_02'], '../uploaded_img/');

         // Update the new image path in the database
         $update_image_02 = $conn->prepare("UPDATE `products` SET image_02 = ? WHERE id = ?");
         $update_image_02->execute([$new_image_02, $pid]);
      }
   }

   // Image 03
   $old_image_03 = $_POST['old_image_03'];
   if (!empty($_FILES['image_03']['name'])) {
      if (validate_image($_FILES['image_03'], $errors, 'image_03')) {
         // Delete old image
         $old_image_path_03 = '../uploaded_img/' . $old_image_03;
         if (file_exists($old_image_path_03)) {
            unlink($old_image_path_03);
         }
         // Upload new image
         $new_image_03 = upload_image($_FILES['image_03'], '../uploaded_img/');

         // Update the new image path in the database
         $update_image_03 = $conn->prepare("UPDATE `products` SET image_03 = ? WHERE id = ?");
         $update_image_03->execute([$new_image_03, $pid]);
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
   <title>Cập nhật</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="update-product">

      <h1 class="heading">cập nhật sản phẩm</h1>

      <?php
      $update_id = $_GET['update'];
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$update_id]);
      if ($select_products->rowCount() > 0) {
         while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
            $current_category_id = $fetch_products['category_id'];
      ?>
            <form action="" method="post" enctype="multipart/form-data">
               <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
               <input type="hidden" name="old_image_01" value="<?= $fetch_products['image_01']; ?>">
               <input type="hidden" name="old_image_02" value="<?= $fetch_products['image_02']; ?>">
               <input type="hidden" name="old_image_03" value="<?= $fetch_products['image_03']; ?>">
               <div class="image-container">
                  <div class="main-image">
                     <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
                  </div>
                  <div class="sub-image">
                     <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
                     <img src="../uploaded_img/<?= $fetch_products['image_02']; ?>" alt="">
                     <img src="../uploaded_img/<?= $fetch_products['image_03']; ?>" alt="">
                  </div>
               </div>
               <span>Tên</span>
               <input type="text" name="name" class="box" placeholder="nhập tên sản phẩm..." value="<?= $fetch_products['name']; ?>">
               <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <span>Giá</span>
               <input type="number" name="price" class="box" placeholder="nhập giá sản phẩm..." onkeypress="if(this.value.length == 10) return false;" value="<?= $fetch_products['price']; ?>">
               <?php echo form_error('price', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <span>Mô tả</span>
               <textarea name="details" class="box" cols="30" rows="10"><?= $fetch_products['details']; ?></textarea>
               <?php echo form_error('details', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <span>Hình 1</span>
               <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_01', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <span>Hình 2</span>
               <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_02', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <span>Hình 03</span>
               <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_03', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

               <div class="box">
                  <span>Danh mục sản phẩm</span>
                  <select name="category_id" class="select">
                     <option value="0">Vui lòng chọn danh mục</option>
                     
                     <?php
                     // echo $current_category_id;
                     // Truy vấn tất cả các danh mục từ bảng product_categories
                     $select_categories = $conn->prepare("SELECT id, name FROM product_categories");
                     $select_categories->execute();

                     if ($select_categories->rowCount() > 0) {
                        // Hiển thị các danh mục dưới dạng các tùy chọn <option>
                        while ($category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                           if ($category['id'] == $current_category_id) {
                              // Hiển thị danh mục đã chọn
                              echo '<option value="' . $category['id'] . '" selected>' . $category['name'] . '</option>';
                          } else {
                              // Hiển thị các danh mục khác
                              echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                          }
                        }
                     } else {
                        echo '<option value="0">Không có danh mục</option>';
                     }
                     ?>
                  </select>
                  <?php echo form_error('product_category', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
               </div>

               <div class="flex-btn">
                  <input type="submit" name="update" class="btn" value="update">
                  <a href="products.php" class="option-btn">go back</a>
               </div>
            </form>

      <?php
         }
      } else {
         echo '<p class="empty">Sản phẩm không tìm thấy!</p>';
      }
      ?>

   </section>

   <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>