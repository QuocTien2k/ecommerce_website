<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
};

include 'components/wishlist_cart.php';

$query = "SELECT option_name, option_value, display_name FROM options WHERE section = 'slider'";
$stmt = $conn->prepare($query);
$stmt->execute();
$sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// In mảng sliders để kiểm tra dữ liệu
// echo '<pre>';
// print_r($sliders);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Trang chủ</title>

   <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css?version=<?php echo rand(); ?>">

</head>

<body>
   <!-- Header -->
   <?php include 'components/user_header.php'; ?>

   <!-- Background -->
   <div class="home-bg">

      <section class="home">

         <div class="swiper home-slider">
            <div class="swiper-wrapper">
               <?php
               foreach ($sliders as $slider):
               ?>
                  <div class="swiper-slide slide">
                     <div class="image">
                        <img src="uploaded_img/<?php echo $slider['display_name']; ?>" alt="">
                     </div>
                     <div class="content">
                        <span>upto <?php echo $slider['option_value']; ?>% off</span>
                        <h3><?php echo $slider['option_name']; ?></h3>
                        <a href="shop.php" class="btn">shop now</a>
                     </div>
                  </div>
               <?php endforeach; ?>
            </div>

            <div class="swiper-pagination"></div>

         </div>

      </section>

   </div>

   <!-- Category -->
   <section class="category">

      <h1 class="heading">Dang mục sản phẩm</h1>

      <div class="swiper category-slider">

         <div class="swiper-wrapper">
            <?php
            $category= [];
            $select_category = $conn->prepare("SELECT * FROM product_categories");
            $select_category->execute();

            // if ($select_category->rowCount() > 0) {
            //    $category = $select_category->fetchAll(PDO::FETCH_ASSOC);
            // }

            // echo '<pre>';
            // print_r($category);
            // echo '</pre>';

            // die();
            if ($select_category->rowCount() > 0) {
               while ($fetch_category = $select_category->fetch(PDO::FETCH_ASSOC)) {
            ?>
                  <a href="category.php?category=<?php echo $fetch_category['id']; ?>" class="swiper-slide slide">
                     <img src="images/<?php echo $fetch_category['image']; ?>" alt="">
                     <h3><?php echo $fetch_category['name']; ?></h3>
                  </a>
            <?php
               }
            } else {
               echo '<p class="empty">Sản phẩm không tìm thấy!</p>';
            }
            ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>

   </section>

   <!-- Products -->
   <section class="home-products">
      <h1 class="heading">Các sản phẩm mới</h1>
      <div class="swiper products-slider">
         <div class="swiper-wrapper">
            <?php
            $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 4");
            $select_products->execute();
            if ($select_products->rowCount() > 0) {
               while ($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)) {
                  $price = (float)$fetch_product['price'];
                  $price = number_format($price, 0, ',', '.');
            ?>
                  <form action="" method="post" class="swiper-slide slide">
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
                        <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
                     </div>
                     <div style="display: flex; justify-content: center; align-items: center;">
                        <input type="submit" value="Thêm vào giỏ hàng" class="btn" name="add_to_cart">
                     </div>
                  </form>
            <?php
               }
            } else {
               echo '<p class="empty">no products added yet!</p>';
            }
            ?>
         </div>
         <div class="swiper-pagination"></div>
      </div>
      <div class="flex-btn" style="justify-content: center;">
         <a href="shop.php" class="btn" style="color: white; background: #cccccc;">Xem thêm</a>
      </div>

   </section>

   <!-- Delivery -->
   <section class="home-delivery">
      <h1 class="heading">Đối tác vận chuyển</h1>
      <div class="delivery-grid">
         <?php
         $select_partners = $conn->prepare("SELECT * FROM `delivery`");
         $select_partners->execute();
         if ($select_partners->rowCount() > 0) {
            while ($fetch_partner = $select_partners->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="delivery-partner">
                  <div class="partner-logo">
                     <img src="uploaded_img/<?= $fetch_partner['logo']; ?>" alt="logo">
                  </div>
                  <div class="partner-info">
                     <div class="partner-name"><?= $fetch_partner['name']; ?></div>
                     <div class="partner-website">
                        <a href="<?= $fetch_partner['website'] ?>" target="_blank">Chi tiết</a>
                     </div>
                  </div>
               </div>
         <?php
            }
         } else {
            echo '<p class="empty">Không có đối tác nào!</p>';
         }
         ?>
      </div>
   </section>



   <!-- Footer -->
   <?php include 'components/footer.php'; ?>

   <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
   <script src="js/script.js?version=<?php echo rand(); ?>"></script>
   <script>
      var swiper = new Swiper(".home-slider", {
         // Cho phép lặp lại slide
         loop: true,
         // Khoảng cách giữa các slide
         spaceBetween: 20,
         // Tự động chạy slider
         autoplay: {
            // Thời gian delay giữa các slide
            delay: 5000,
            // Tắt autoplay khi người dùng tương tác với slider
            disableOnInteraction: false,
         },
         // Cấu hình phân trang
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         // Cấu hình điều khiển (nút trước và sau)
         navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
         },
      });


      var swiper = new Swiper(".category-slider", {
         loop: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            0: {
               slidesPerView: 2,
            },
            650: {
               slidesPerView: 3,
            },
            768: {
               slidesPerView: 4,
            },
            1024: {
               slidesPerView: 5,
            },
         },
      });

      var swiper = new Swiper(".products-slider", {
         loop: true,
         spaceBetween: 20,
         pagination: {
            el: ".swiper-pagination",
            clickable: true,
         },
         breakpoints: {
            550: {
               slidesPerView: 2,
            },
            768: {
               slidesPerView: 2,
            },
            1024: {
               slidesPerView: 3,
            },
         },
      });
   </script>

</body>

</html>