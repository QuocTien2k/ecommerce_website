<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
};

$errors = [];
if (isset($_POST['add_product'])) {

   $name = trim($_POST['name']);
   $name = filter_var($name, FILTER_SANITIZE_STRING);

   $price = trim($_POST['price']);
   $price = filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

   $details = trim($_POST['details']);
   $details = filter_var($details, FILTER_SANITIZE_STRING);

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

   // Validate and upload images
   $image_01 = '';
   if (validate_image($_FILES['image_01'], $errors, 'image_01')) {
      $image_01 = upload_image($_FILES['image_01'], '../uploaded_img/');
   }

   $image_02 = '';
   if (validate_image($_FILES['image_02'], $errors, 'image_02')) {
      $image_02 = upload_image($_FILES['image_02'], '../uploaded_img/');
   }

   $image_03 = '';
   if (validate_image($_FILES['image_03'], $errors, 'image_03')) {
      $image_03 = upload_image($_FILES['image_03'], '../uploaded_img/');
   }

   $category_id = $_POST['category_id'];
   if ($category_id == 0) {
      $errors['product_category']['required'] = 'Vui lòng chọn danh mục sản phẩm!';
   }

   // Check if there are any validation errors
   if (empty($errors)) {
      // Check if the product already exists
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
      $select_products->execute([$name]);

      if ($select_products->rowCount() > 0) {
         $message[] = create_message('warning', 'Tên sản phẩm đã tồn tại!');
      } else {
         // Insert product into the database
         $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image_01, image_02, image_03, category_id) VALUES(?,?,?,?,?,?,?)");
         $insert_success = $insert_products->execute([$name, $details, $price, $image_01, $image_02, $image_03, $category_id]);

         if ($insert_success) {
            $message[] = create_message('success', 'Thêm sản phẩm thành công!');
         } else {
            $message[] = create_message('failed', 'Thêm sản phẩm thất bại!');
         }
      }
   }
}

if (isset($_GET['delete'])) {

   $delete_id = $_GET['delete'];
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/' . $fetch_delete_image['image_01']);
   unlink('../uploaded_img/' . $fetch_delete_image['image_02']);
   unlink('../uploaded_img/' . $fetch_delete_image['image_03']);
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_product->execute([$delete_id]);
   if ($delete_product) {
      $message[] = create_message('success', 'Đã xóa sản phẩm thành công!');
   }

   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   if ($delete_cart) {
      $message[] = create_message('success', 'Đã xóa sản phẩm ra khỏi giỏ hàng!');
   }

   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   if ($delete_wishlist) {
      $message[] = create_message('success', 'Đã xóa sản phẩm ra khỏi mục yêu thích!');
   }

   if ($delete_product) {
      echo '<script>
         setTimeout(function(){
            window.location.href= "products.php";
         }, 4800);
   </script>';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sản phẩm</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <section class="add-products">

      <h1 class="heading">Thêm sản phẩm</h1>

      <form action="" method="post" enctype="multipart/form-data">
         <div class="flex">
            <div class="inputBox">
               <span>Tên sản phẩm</span>
               <input type="text" class="box" maxlength="100" placeholder="nhập tên..." name="name">
               <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Giá</span>
               <input type="number" min="0" class="box" placeholder="nhập giá..." onkeypress="if(this.value.length == 10) return false;" name="price">
               <?php echo form_error('price', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Hình ảnh 1</span>
               <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_01', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Hình ảnh 2</span>
               <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_02', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Hình ảnh 3</span>
               <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
               <?php echo form_error('image_03', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Mô tả sản phẩm</span>
               <textarea name="details" placeholder="nhập mô tả..." class="box" cols="30" rows="10"></textarea>
               <?php echo form_error('details', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
            <div class="inputBox">
               <span>Danh mục sản phẩm</span>
               <select name="category_id" class="select">
                  <option value="0">Vui lòng chọn danh mục</option>
                  <?php
                  // Truy vấn tất cả các danh mục từ bảng product_categories
                  $select_categories = $conn->prepare("SELECT id, name FROM product_categories");
                  $select_categories->execute();

                  if ($select_categories->rowCount() > 0) {
                     // Hiển thị các danh mục dưới dạng các tùy chọn <option>
                     while ($category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                     }
                  } else {
                     echo '<option value="0">Không có danh mục</option>';
                  }
                  ?>
               </select>
               <?php echo form_error('product_category', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            </div>
         </div>

         <input type="submit" value="thêm sản phẩm" class="btn" name="add_product">
      </form>

   </section>

   <section class="show-products">
      <h1 class="heading">Sản phẩm đã thêm</h1>
      <div class="box-container" id="content-area">
         <?php
         // Thiết lập số sản phẩm mỗi trang
         $products_per_page = 3;

         // Tính trang hiện tại, mặc định là trang 1 nếu không có giá trị `page` trong URL
         $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
         $offset = ($current_page - 1) * $products_per_page;

         // Lấy tổng số sản phẩm từ cơ sở dữ liệu
         $total_products_query = $conn->prepare("SELECT COUNT(*) FROM `products`");
         $total_products_query->execute();
         $total_products = $total_products_query->fetchColumn();
         $total_pages = ceil($total_products / $products_per_page);

         // Truy vấn sản phẩm theo trang hiện tại
         $select_products = $conn->prepare("SELECT product_categories.name as cate_name, products.* FROM `products` 
         INNER JOIN product_categories ON products.category_id = product_categories.id LIMIT :limit OFFSET :offset");

         $select_products->bindParam(':limit', $products_per_page, PDO::PARAM_INT);
         $select_products->bindParam(':offset', $offset, PDO::PARAM_INT);
         $select_products->execute();

         // Hiển thị sản phẩm
         if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
               $price = (float)$fetch_products['price'];
               $price = number_format($price, 0, ',', '.');
         ?>
               <div class="box">
                  <img src="../uploaded_img/<?= $fetch_products['image_01']; ?>" alt="">
                  <div class="name"><?= $fetch_products['name']; ?></div>
                  <div class="price"><span><?= $price; ?></span> vnđ</div>
                  <div class="category"><?= $fetch_products['cate_name']; ?></div>
                  <div class="details"><span class="details-content"><?= $fetch_products['details']; ?></span></div>
                  <div class="flex-btn">
                     <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">Cập nhật</a>
                     <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chứ?');">Xóa</a>
                  </div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Chưa có sản phẩm nào!</p>';
         }
         ?>
      </div>
      <hr>
      <!-- Phân trang -->
      <div class="pagination">
         <?php
         // Hiển thị nút "Previous" nếu không ở trang đầu
         if ($current_page > 1) {
            echo '<a href="?page=' . ($current_page - 1) . '" class="prev-btn pagination-link">Trang trước</a>';
         }

         // Hiển thị số trang
         for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a href="?page=' . $i . '" class="pagination-link ' . ($i == $current_page ? 'active' : '') . '">' . $i . '</a>';
         }

         // Hiển thị nút "Next" nếu không ở trang cuối
         if ($current_page < $total_pages) {
            echo '<a href="?page=' . ($current_page + 1) . '" class="next-btn pagination-link">Trang sau</a>';
         }
         ?>
      </div>
   </section>

   <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script>
      $(document).ready(function() {
         // Khi người dùng nhấp vào bất kỳ liên kết phân trang nào
         $(document).on('click', '.pagination-link', function(e) {
            e.preventDefault(); // Ngăn việc load lại trang khi nhấn vào link

            // Lấy URL từ thuộc tính href của liên kết
            var pageUrl = $(this).attr('href');

            // Gửi yêu cầu AJAX để lấy dữ liệu trang mới
            $.ajax({
               url: pageUrl, // Gửi đến URL của liên kết
               type: 'GET', // Sử dụng phương thức GET
               success: function(response) {
                  // Cập nhật nội dung của phần content-area với dữ liệu từ server
                  $('#content-area').html($(response).find('#content-area').html());

                  // Cập nhật lại phần pagination để duy trì điều hướng phân trang
                  $('.pagination').html($(response).find('.pagination').html());
               },
               error: function() {
                  alert('Có lỗi xảy ra khi tải trang.');
               }
            });
         });
      });
   </script>
</body>

</html>