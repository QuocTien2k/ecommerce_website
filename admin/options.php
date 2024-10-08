<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};
$errors = [];
// Xử lý form submit header và footer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['header_submit'])) {

        $header_option_name = trim($_POST['header_option_name']);
        $header_option_value = trim($_POST['header_option_value']);
        $header_display_name = trim($_POST['header_display_name']);

        if (empty($header_option_name)) {
            $errors['header_option_name']['required'] = 'Vui lòng nhập!';
        }
        if (empty($header_option_value)) {
            $errors['header_option_value']['required'] = 'Vui lòng nhập đường dẫn!';
        }
        if (empty($header_display_name)) {
            $errors['header_display_name']['required'] = 'Vui lòng nhập!';
        }

        if (empty($errors)) {
            insert_option('header', $header_option_name, $header_option_value, $header_display_name);
            $message[] = create_message('success', 'Thiết lập ' . $_POST['header_option_name'] . ' thành công!');
        }
    } elseif (isset($_POST['footer_submit'])) {
        $footer_option_name = trim($_POST['footer_option_name']);
        $footer_option_title = trim($_POST['footer_option_title']);
        $footer_option_value = trim($_POST['footer_option_value']);
        $footer_display_name = trim($_POST['footer_display_name']);
        $footer_option_icon = trim($_POST['footer_option_icon']);

        if (empty($footer_option_name)) {
            $errors['footer_option_name']['required'] = 'Vui lòng nhập!';
        }
        if (empty($footer_option_value)) {
            $errors['footer_option_value']['required'] = 'Vui lòng nhập!';
        }
        if (empty($footer_display_name)) {
            $errors['footer_display_name']['required'] = 'Vui lòng nhập!';
        }
        if (empty($footer_option_icon)) {
            $errors['footer_option_icon']['required'] = 'Vui lòng nhập!';
        }
        if (empty($footer_option_title)) {
            $errors['footer_option_title']['required'] = 'Vui lòng nhập!';
        }

        if (empty($errors)) {
            insert_option('footer', $footer_option_name, $footer_option_value, $footer_display_name, $footer_option_icon, $footer_option_title);
            $message[] = create_message('success', 'Thiết lập ' . $_POST['footer_option_name'] . ' thành công!');
        }
    }

    if(empty($errors)){
        if (isset($_POST['header_submit']) || isset($_POST['footer_submit'])) {
            echo '<script>
               setTimeout(function(){
                  window.location.href= "options.php";
               }, 4500);
         </script>';
        }
    }
}

if (isset($_POST['slider_submit'])) {
    // Lấy thông tin từ form
    $product_name = trim($_POST['product_name']);
    $product_name = htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');

    $discount = $_POST['discount'];
    $discount = (int)$_POST['discount'];

    if (empty($product_name)) {
        $errors['product_name']['required'] = 'Vui lòng nhập tên sản phẩm!';
    } elseif (strlen($product_name) < 2) {
        $errors['product_name']['min'] = 'Tên sản phẩm phải có ít nhất 4 ký tự!';
    }

    if ($discount < 0 || $discount > 90) {
        $errors['discount']['required'] = 'Giảm giá phải nằm trong khoảng từ 0% đến 90%';
    }

    $update_at = date('Y-m-d H:i:s'); // Thời gian cập nhật

    // Validate hình ảnh
    if (isset($_FILES['slider_image'])) {
        $slider_image = $_FILES['slider_image'];
        if (validate_image($slider_image, $errors, 'slider_image')) {
            // Upload hình ảnh
            $image_name = upload_image($slider_image, '../uploaded_img/');
        }
    }

    // Kiểm tra lỗi và thực hiện thêm dữ liệu vào bảng options
    if (empty($errors)) {
        $update_at = date('Y-m-d H:i:s');

        // Thực hiện thêm dữ liệu vào bảng options
        $stmt = $conn->prepare("INSERT INTO options (option_name, option_value, display_name, section, update_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $discount, $image_name, 'slider', $update_at]);

        // Thông báo thành công
        $message[] = create_message('success', 'Thiết lập Slider thành công!');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>
    <?php include '../components/admin_header.php'; ?>

    <div class="options-container">
        <!-- Cột thiết lập Header -->
        <div class="options-column">
            <h2>Thiết lập Header</h2>
            <form class="options-form" action="" method="POST">
                <div class="input-box">
                    <input type="text" name="header_option_name" placeholder="Tên tùy chọn. Vd: logo_path">
                    <?php echo form_error('header_option_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="header_option_value" placeholder="Đường dẫn đến logo_path...">
                    <?php echo form_error('header_option_value', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="header_display_name" placeholder="Tên hiển thị ở trang khách hàng...">
                    <?php echo form_error('header_display_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <input type="submit" name="header_submit" value="Thiết lập Header">
            </form>
        </div>

        <!-- Cột thiết lập Slider -->
        <div class="options-column">
            <h2>Thiết lập Slider</h2>
            <form class="options-form" action="" method="POST" enctype="multipart/form-data">
                <div class="input-box">
                    <input type="text" name="product_name" placeholder="Tên sản phẩm...">
                    <?php echo form_error('product_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="number" name="discount" placeholder="Giảm giá (%)...">
                    <?php echo form_error('discount', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <label for="slider_image">Chọn hình ảnh:</label>
                    <input type="file" name="slider_image" id="slider_image" accept="image/*">
                    <?php echo form_error('slider_image', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <input type="submit" name="slider_submit" value="Thiết lập Slider">
            </form>
        </div>

        <!-- Cột thiết lập Footer -->
        <div class="options-column">
            <h2>Thiết lập Footer</h2>
            <form class="options-form" action="" method="POST">
                <div class="input-box">
                    <input type="text" name="footer_option_title" placeholder="Tên tiêu đề. Vd: Quick links">
                    <?php echo form_error('footer_option_title', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="footer_option_name" placeholder="Tên tùy chọn. Vd: footer_link">
                    <?php echo form_error('footer_option_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="footer_option_value" placeholder="Đường dẫn đến footer_link">
                    <?php echo form_error('footer_option_value', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="footer_display_name" placeholder="Tên hiển thị footer_link là contact us">
                    <?php echo form_error('footer_display_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="input-box">
                    <input type="text" name="footer_option_icon" placeholder="Nhập icon. Vd: fas fa-angle-right">
                    <?php echo form_error('footer_option_icon', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <input type="submit" name="footer_submit" value="Thiết lập Footer">
            </form>
        </div>
    </div>

    <!-- Slider added -->
    <div class="slider">
        <h1>Slider đã thiết lập</h1>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php
                // Truy vấn để lấy dữ liệu slider từ bảng options
                $query = "SELECT id, option_name, option_value, display_name FROM options WHERE section = 'slider'";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <!-- Duyệt qua các slide -->
                <?php foreach ($sliders as $slider): ?>
                    <div class="swiper-slide">
                        <div class="slide-image">
                            <img src="../uploaded_img/<?php echo $slider['display_name']; ?>" alt="Hình sản phẩm">
                        </div>
                        <div class="slide-content">
                            <h3><?php echo $slider['option_name']; ?></h3>
                            <p>Giảm giá lên đến <?php echo $slider['option_value']; ?>%</p>
                        </div>
                        <!-- Nút cập nhật -->
                        <button class="update-btn" data-id="<?php echo $slider['id']; ?>">Cập nhật</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
    // echo '<pre>';
    // print_r($sliders);
    // echo '</pre>';
    ?>

    <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var swiper = new Swiper(".swiper-container", {
                loop: true,
                spaceBetween: 30,
                // autoplay: {
                //     delay: 5000,
                //     disableOnInteraction: false,
                // },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            let updateButtons = document.querySelectorAll('.update-btn');

            updateButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');
                    // Thực hiện hành động cập nhật với id
                    // Ví dụ: chuyển hướng đến trang cập nhật hoặc mở modal để chỉnh sửa
                    window.location.href = `update_slider.php?id=${id}`;
                });
            });
        });
    </script>
</body>

</html>