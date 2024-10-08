<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

$errors = [];
if (isset($_POST['add_partner'])) {
    $name = trim($_POST['name']);
    
    $website = trim($_POST['website']);
    $website = filter_var($website, FILTER_SANITIZE_STRING);

    // Validate name
    if (empty($name)) {
        $errors['name']['required'] = 'Vui lòng nhập tên đối tác!';
    } elseif (strlen($name) < 2) {
        $errors['name']['min'] = 'Tên đối tác phải có ít nhất 2 ký tự!';
    }

    // Validate website
    if (empty($website)) {
        $errors['website']['required'] = 'Vui lòng nhập đường dẫn!';
    }

    // Validate and upload images
    $logo = '';
    if (validate_image($_FILES['logo'], $errors, 'logo')) {
        $logo = upload_image($_FILES['logo'], '../uploaded_img/');
    }

    // Check if there are any validation errors
    if (empty($errors)) {
        // Check if the product already exists
        $select_partner = $conn->prepare("SELECT * FROM `delivery` WHERE name = ?");
        $select_partner->execute([$name]);

        if ($select_partner->rowCount() > 0) {
            $message[] = create_message('warning', 'Tên đối tác đã tồn tại!');
        } else {
            // Insert product into the database
            $create_at = date('Y-m-d H:i:s');

            $insert_partner = $conn->prepare("INSERT INTO `delivery`(name, logo, website, create_at) VALUES(?,?,?,?)");
            $insert_sucess = $insert_partner->execute([$name, $logo, $website, $create_at]);

            if ($insert_sucess) {
                $message[] = create_message('success', 'Thêm đối tác thành công!');
            } else {
                $message[] = create_message('failed', 'Thêm đối tác thất bại!');
            }
        }
    }
}

if(isset($_GET['partner_id'])){
    $partner_id = $_GET['partner_id'];

    //k.tra đối tác giữa 2 bảng orders - delivery
    $check_orders= $conn->prepare("SELECT * FROM orders WHERE delivery_id= ?");
    $check_orders->execute([$partner_id]);

    if($check_orders->rowCount() >0){
        $message[] = create_message('warning', 'Không thể xóa đối tác này, vẫn còn đơn hàng liên quan!!');
    }else{
        // Truy vấn để lấy logo đối tác từ cơ sở dữ liệu
        $select_logo = $conn->prepare("SELECT logo FROM `delivery` WHERE id = ?");
        $select_logo->execute([$partner_id]);
        $partner = $select_logo->fetch(PDO::FETCH_ASSOC);    
    
        if ($partner) {
            // Xóa hình ảnh logo từ thư mục
            $logo_path = '../uploaded_img/' . $partner['logo'];
            if (file_exists($logo_path)) {
                unlink($logo_path); // Xóa file hình ảnh
            }
    
            // Xóa đối tác khỏi cơ sở dữ liệu
            $delete_partner = $conn->prepare("DELETE FROM `delivery` WHERE id = ?");
            $delete_partner->execute([$partner_id]);
    
            if ($delete_partner) {
                $message[] = create_message('success', 'Xóa đối tác thành công!');
    
            } else {
                echo "Có lỗi xảy ra khi xóa đối tác!";
                $message[] = create_message('failed', 'Xóa đối tác thất bại!');
            }
        } else {
            $message[] = create_message('warning', 'Không tìm thấy đối tác!');
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
    <title>Đối tác</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="../css/swiper.css?version=<?php echo rand(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="add-partners">

        <h1 class="heading">Thêm đối tác</h1>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="flex">
                <div class="inputBox">
                    <span>Tên đối tác</span>
                    <input type="text" class="box" placeholder="nhập tên..." name="name">
                    <?php echo form_error('name', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>

                <div class="inputBox">
                    <span>Logo</span>
                    <input type="file" name="logo" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
                    <?php echo form_error('logo', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="inputBox">
                    <span>Đường dẫn</span>
                    <input type="text" class="box" placeholder="nhập tên..." name="website">
                    <?php echo form_error('website', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
            </div>
            <div style="width: 100%; display: flex; justify-content: center;">
                <input type="submit" value="thêm đối tác" class="btn" name="add_partner">
            </div>
        </form>

    </section>

    <section class="show-products">
        <h1 class="heading">Đối tác đã thêm</h1>
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php
                // Truy vấn tất cả các đối tác vận chuyển từ bảng `delivery`
                $select_partners = $conn->prepare("SELECT * FROM `delivery`");
                $select_partners->execute();

                // Lấy tất cả kết quả dưới dạng mảng
                $partners = $select_partners->fetchAll(PDO::FETCH_ASSOC);

                // Sử dụng vòng lặp foreach để hiển thị từng logo
                foreach ($partners as $partner) {
                    echo '<div class="swiper-slide">';
                    echo '<img src="../uploaded_img/' . $partner['logo'] . '" alt="Logo">';
                    echo '<a href="delete_partner.php?partner_id=' . $partner['id'] . '" class="delete-btn" onclick="return confirm(\'Bạn có chắc muốn xóa đối tác này?\');">Xóa</a>';
                    echo '</div>';
                }

                // Trường hợp không có đối tác nào
                if (empty($partners)) {
                    echo '<div class="swiper-slide">Không có đối tác nào.</div>';
                }
                ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>
    <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            loop: true, // Lặp lại các slide
        });
    </script>
</body>

</html>