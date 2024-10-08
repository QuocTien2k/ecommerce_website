<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

$errors = [];
if (isset($_POST['add_product_category'])) {

    $name = trim($_POST['name']);
    $name = filter_var($name, FILTER_SANITIZE_STRING);

    // Validate name
    if (empty($name)) {
        $errors['name']['required'] = 'Vui lòng nhập tên danh mục!';
    } elseif (strlen($name) < 2) {
        $errors['name']['min'] = 'Tên danh mục phải có ít nhất 2 ký tự!';
    }

    // Validate and upload images
    $image = '';
    if (validate_image($_FILES['image'], $errors, 'image')) {
        $image = upload_image($_FILES['image'], '../uploaded_img/');
    }

    if (empty($errors)) {
        //check name category
        $check_name = $conn->prepare('SELECT * FROM product_categories WHERE name=?');
        $check_name->execute([$name]);

        if ($check_name->rowCount() > 0) {
            $message[] = create_message('warning', 'Tên danh mục đã tồn tại!');
        } else {
            $create_at = date('Y-m-d h:i:s');
            $insert_category = $conn->prepare(" INSERT INTO product_categories (name, image, create_at) VALUES (?, ?, ?)");
            $insert_category->execute([$name, $image, $create_at]);

            if ($insert_category) {
                $message[] = create_message('success', 'Thêm danh mục thành công!');
            } else {
                $message[] = create_message('failed', 'Hệ thống đan gặp sự cố. Vui lòng thử lại');
            }
        }
    }
}

if (isset($_GET['delete'])) {

    $delete_id = $_GET['delete'];

    // Truy vấn để lấy hình ảnh trước khi xóa danh mục
    $select_product_image = $conn->prepare("SELECT * FROM `product_categories` WHERE id = ?");
    $select_product_image->execute([$delete_id]);

    if ($select_product_image->rowCount() > 0) {
        $fetch_delete_image = $select_product_image->fetch(PDO::FETCH_ASSOC);
        $image_path = '../uploaded_img/' . $fetch_delete_image['image'];

        // Xóa sản phẩm thuộc danh mục
        $delete_product = $conn->prepare("DELETE FROM `products` WHERE category_id = ?");
        $delete_product->execute([$delete_id]);
        if ($delete_product) {
            $message[] = create_message('success', 'Đã xóa danh mục ra khỏi sản phẩm!');
        }

        // Xóa danh mục
        $delete_product_category = $conn->prepare("DELETE FROM `product_categories` WHERE id = ?");
        $delete_product_category->execute([$delete_id]);
        if ($delete_product_category) {
            $message[] = create_message('success', 'Đã xóa danh mục thành công!');

            // Sau khi xóa danh mục, xóa ảnh liên quan
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    } else {
        $message[] = create_message('error', 'Danh mục không tồn tại!');
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="add-products">

        <h1 class="heading">Thêm danh mục</h1>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="flex">
                <div class="inputBox">
                    <span>Tên danh mục</span>
                    <input type="text" class="box" maxlength="100" placeholder="nhập tên..." name="name">
                    <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="inputBox">
                    <span>Hình ảnh đại diện</span>
                    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
                    <?php echo form_error('image', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
            </div>

            <input type="submit" value="thêm danh mục" class="btn" name="add_product_category">
        </form>
    </section>

    <section class="show-products">
        <h1 class="heading">Danh mục hiện có</h1>
        <div class="box-container">
            <?php
            $select_categories = $conn->prepare("SELECT pc.*, COUNT(p.id) AS product_count FROM product_categories pc LEFT JOIN products p ON p.category_id = pc.id GROUP BY pc.id");
            $select_categories->execute();

            if ($select_categories->rowCount() > 0) {
                while ($fetch_categories = $select_categories->fetch(PDO::FETCH_ASSOC)) {
            ?>
                    <div class="box">
                        <div class="name">
                            <p><?php echo $fetch_categories['name']; ?></p>
                            <span class="product-count">Số lượng sản phẩm: <?php echo $fetch_categories['product_count']; ?></span>
                        </div>
                        <div class="flex-btn">
                            <a href="update_category.php?update=<?= $fetch_categories['id']; ?>" class="option-btn">Cập nhật</a>
                            <a href="category.php?delete=<?= $fetch_categories['id']; ?>" class="delete-btn" onclick="return confirmDeleteCategory(<?= $fetch_categories['id']; ?>)">Xóa</a>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">Chưa có danh mục nào!</p>';
            }
            ?>
        </div>
    </section>
    <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>
    <script>
        function confirmDeleteCategory(categoryId) {
            // Thông báo cảnh báo cho Admin về hành động xóa
            let message = "CẢNH BÁO: Bạn đang chuẩn bị xóa danh mục này!\n\n" +
                "Nếu bạn xóa danh mục này, tất cả các sản phẩm liên quan đến danh mục sẽ bị xóa vĩnh viễn.\n\n" +
                "Hãy chắc chắn rằng bạn thực sự muốn thực hiện hành động này.\n\n" +
                "Nhấn OK để tiếp tục, hoặc Cancel để hủy.";

            // Hiển thị hộp thoại cảnh báo
            let confirmation = confirm(message);

            if (confirmation) {
                // Nếu người dùng xác nhận, chuyển hướng đến liên kết xóa
                window.location.href = "category.php?delete=" + categoryId;
            } else {
                // Hủy thao tác nếu người dùng không xác nhận
                return false;
            }
        }
    </script>

</body>

</html>