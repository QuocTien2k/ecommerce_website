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
    $cate_id = $_POST['cate_id'];

    $name = trim($_POST['name']);
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    // Validate name
    if (empty($name)) {
        $errors['name']['required'] = 'Vui lòng nhập tên danh mục!';
    } elseif (strlen($name) < 4) {
        $errors['name']['min'] = 'Tên danh mục phải có ít nhất 4 ký tự!';
    }

    if (empty($errors)) {
        $update_at = date('Y-m-d h:i:s');
        $update_product_category = $conn->prepare("UPDATE `product_categories` SET name = ?, update_at= ? WHERE id = ?");
        $update_success = $update_product_category->execute([$name, $update_at, $cate_id]);

        if ($update_success) {
            $message[] = create_message('success', 'Tên danh mục đã cập nhật!');
        } else {
            $message[] = create_message('failed', 'Cập nhật tên danh mục thất bại!');
        }
    }

    // Handle image updates
    // Image 01
    $old_image = $_POST['old_image'];
    if (!empty($_FILES['image']['name'])) {
        if (validate_image($_FILES['image'], $errors, 'image')) {
            // Delete old image
            $old_image_path = '../uploaded_img/' . $old_image;
            if (file_exists($old_image_path)) {
                unlink($old_image_path);
            }
            // Upload new image
            $new_image = upload_image($_FILES['image'], '../uploaded_img/');

            // Update the new image path in the database
            $update_at = date('Y-m-d h:i:s');
            $update_image = $conn->prepare("UPDATE `product_categories` SET image = ?, update_at= ? WHERE id = ?");
            $update_image->execute([$new_image, $update_at, $cate_id]);

            if($update_image){
                $message[] = create_message('success', 'Danh mục đã cập nhật hình ảnh!');
            }else{
                $message[] = create_message('failed', 'Cập nhật hình ảnh danh mục thất bại!');
            }
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
        <h1 class="heading">cập nhật danh mục</h1>
        <?php
        $update_id = $_GET['update'];
        $select_category = $conn->prepare("SELECT * FROM `product_categories` WHERE id = ?");
        $select_category->execute([$update_id]);
        if ($select_category->rowCount() > 0) {
            while ($fetch_category = $select_category->fetch(PDO::FETCH_ASSOC)) {
        ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="cate_id" value="<?= $fetch_category['id']; ?>">
                    <input type="hidden" name="old_image" value="<?= $fetch_category['image']; ?>">
                    <div class="image-container">
                        <div class="main-image">
                            <img src="../uploaded_img/<?= $fetch_category['image']; ?>" alt="">
                        </div>
                    </div>
                    <p>Tên danh mục</p>
                    <input type="text" name="name" class="box" placeholder="nhập tên danh mục..." value="<?= $fetch_category['name']; ?>">
                    <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red; ">', '</span>'); ?>

                    <p>Ảnh đại diện</p>
                    <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
                    <?php echo form_error('image', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>

                    <div class="flex-btn">
                        <input type="submit" name="update" class="btn" value="update">
                        <a href="category.php" class="option-btn">go back</a>
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