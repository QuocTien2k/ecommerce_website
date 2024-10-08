<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

// Xử lý yêu cầu GET để lấy thông tin hiện tại
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slider = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM options WHERE section = 'slider' AND id = ?");
    $stmt->execute([$id]);
    $slider = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$slider) {
        $message[] = create_message('warning', 'Slider không tồn tại!');
        echo '<script>
        setTimeout(function(){
            window.location.href= "options.php";
            }, 4500);
            </script>';
    }
} else {
    $message[] = create_message('warning', 'ID không hợp lệ!');
    echo '<script>
        setTimeout(function(){
            window.location.href= "options.php";
            }, 4500);
            </script>';
}

$errors = [];
// Xử lý yêu cầu POST để cập nhật slider
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $product_name = htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');

    $discount = $_POST['discount'];
    $discount = (int)$_POST['discount'];

    $update_at = date('Y-m-d H:i:s'); // Thời gian cập nhật

    if (empty($product_name)) {
        $errors['product_name']['required'] = 'Vui lòng nhập tên sản phẩm!';
    } elseif (strlen($product_name) < 2) {
        $errors['product_name']['min'] = 'Tên sản phẩm phải có ít nhất 4 ký tự!';
    }

    if ($discount < 0 || $discount > 90) {
        $errors['discount']['required'] = 'Giảm giá phải nằm trong khoảng từ 0% đến 90%';
    }

    $old_image = $_POST['old_image'] ?? '';
    // Xử lý ảnh mới nếu có
    if (!empty($_FILES['image']['name'])) {
        if (validate_image($_FILES['image'], $errors, 'image')) {
            // Xóa ảnh cũ
            $old_image_path = '../uploaded_img/' . $old_image;
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
            // Tải ảnh mới lên
            $new_image = upload_image($_FILES['image'], '../uploaded_img/');

            // Cập nhật đường dẫn ảnh mới trong cơ sở dữ liệu
            $update_image = $conn->prepare("UPDATE `options` SET display_name = ? WHERE id = ?");
            $update_image->execute([$new_image, $id]);
        }
    }

    // Cập nhật thông tin slider
    if (empty($errors)) {
        $update_slider = $conn->prepare("UPDATE `options` SET option_name = ?, option_value = ?, update_at= ? WHERE id = ?");
        $update_slider->execute([$product_name, $discount, $update_at, $id]);

        $message[] = create_message('success', 'Cập nhật slider thành công!');
        if ($update_slider) {
            echo '<script>
                // Đợi 1.8 giây trước khi load lại trang update_slider.php
                setTimeout(function(){
                    window.location.href = "update_slider.php?id=' . $id . '";
                }, 200);    
            </script>';
        }
    } else {
        $message[] = create_message('failed', 'Đã xảy ra lỗi!');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật Slider</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>
    <?php include '../components/admin_header.php'; ?>
    <div class="update-slider-form">
        <h2>Cập nhật Slider</h2>
        <?php if ($slider): ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($slider['display_name']); ?>">

                <div class="input-box">
                    <label for="product_name">Tên sản phẩm:</label>
                    <input type="text" name="product_name" id="product_name" value="<?php echo htmlspecialchars($slider['option_name']); ?>">
                    <?php echo form_error('product_name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>

                <div class="input-box">
                    <label for="discount">Giảm giá (%):</label>
                    <input type="number" name="discount" id="discount" value="<?php echo htmlspecialchars($slider['option_value']); ?>">
                    <?php echo form_error('discount', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>

                <div class="input-box">
                    <label for="image">Hình ảnh:</label>
                    <input type="file" name="image" id="image">
                    <img src="../uploaded_img/<?php echo htmlspecialchars($slider['display_name']); ?>" alt="Hình sản phẩm hiện tại">
                </div>

                <input type="submit" value="Cập nhật Slider">
            </form>
        <?php endif; ?>
        <!-- Nút Quay lại -->
        <div class="go-back">
            <a href="options.php" class="btn-back">Quay lại</a>

        </div>
    </div>

    <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>

</body>

</html>