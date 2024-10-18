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
if (isset($_POST['add_to_cart'])) {

   if ($user_id == '') {
      header('location:user_login.php');
      exit();
   } else {
      $qty = $_POST['qty'];
      //check
      if ($qty <= 0 || $qty > 50) {
         $errors['qty'] = 'Số lượng lớn hơn 0 và nhỏ hơn 50!';
      }
      if (empty($errors)) {
         include 'components/add_to_cart.php';
      }
   }
}
if (isset($_POST['add_to_wishlist'])) {
   if ($user_id == '') {
      header('location:user_login.php');
      exit();
   } else {
      include 'components/wishlist_cart.php';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Mua sắm</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="products">

      <h1 class="heading">Mua sắm</h1>

      <?php
      // Nhận số trang từ yêu cầu AJAX hoặc từ URL khi tải trang lần đầu
      $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
      $products_per_page = 6; // Số sản phẩm trên mỗi trang
      $offset = ($page - 1) * $products_per_page;

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
      ?>

      <div class="box-container">
         <?php
         if ($select_products->rowCount() > 0) {
            while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
               $price = (float)$fetch_product['price'];
               $price = number_format($price, 0, ',', '.');
         ?>
               <form action="" method="post" class="box">
                  <input type="hidden" name="pid" value="<?= $fetch_product['id']; ?>">
                  <input type="hidden" name="name" value="<?= $fetch_product['name']; ?>">
                  <input type="hidden" name="price" value="<?= $fetch_product['price']; ?>">
                  <input type="hidden" name="image" value="<?= $fetch_product['image_01']; ?>">
                  <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
                  <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>" class="fas fa-eye"></a>
                  <img src="uploaded_img/<?= $fetch_product['image_01']; ?>" alt="">
                  <div class="name"><?= $fetch_product['name']; ?></div>
                  <div class="flex">
                     <div class="price"><?= $price; ?><span> vnđ</span></div>
                     <input type="number" name="qty" class="qty" onkeypress="if(this.value.length == 2) return false;" value="1">
                  </div>
                  <input type="submit" value="Thêm vào giỏ hàng" class="btn-shopping" name="add_to_cart">
               </form>
         <?php
            }
         } else {
            echo '<p class="empty">Không có sản phẩm nào!</p>';
         }
         ?>
      </div>

      <hr>

      <!-- Phân trang -->
      <div class="pagination">
         <?php
         // Hiển thị nút "Previous" nếu không ở trang đầu
         if ($page > 1) {
            echo '<a href="javascript:void(0)" data-page="' . ($page - 1) . '" class="prev-btn pagination-link">Trang trước</a>';
         }

         // Hiển thị số trang
         for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a href="javascript:void(0)" data-page="' . $i . '" class="pagination-link ' . ($i == $page ? 'active' : '') . '">' . $i . '</a>';
         }

         // Hiển thị nút "Next" nếu không ở trang cuối
         if ($page < $total_pages) {
            echo '<a href="javascript:void(0)" data-page="' . ($page + 1) . '" class="next-btn pagination-link">Trang sau</a>';
         }
         ?>
      </div>


   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js?version=<?php echo rand(); ?>"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
   <script>
      $(document).ready(function() {
         // Bắt sự kiện click vào các liên kết phân trang
         $('.pagination').on('click', '.pagination-link', function(e) {
            e.preventDefault(); // Ngăn chặn load lại trang

            var page = $(this).data('page'); // Lấy số trang từ thuộc tính data-page

            // Gọi AJAX để lấy dữ liệu của trang tương ứng
            $.ajax({
               url: window.location.href, // Gọi lại chính trang hiện tại
               type: 'GET',
               data: {
                  page: page
               }, // Gửi số trang lên server
               success: function(response) {
                  // Chỉ lấy phần nội dung chính, không tải lại toàn bộ trang
                  var newContent = $(response).find('.box-container').html();
                  $('.box-container').html(newContent); // Cập nhật nội dung

                  // Cập nhật phần phân trang mới nếu có
                  var newPagination = $(response).find('.pagination').html();
                  $('.pagination').html(newPagination);
               },
               error: function(xhr, status, error) {
                  console.error("Lỗi khi tải dữ liệu: " + error);
               }
            });
         });
      });
   </script>
</body>

</html>