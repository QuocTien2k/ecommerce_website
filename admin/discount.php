<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
};

$errors = [];
if (isset($_POST['add_discount'])) {

    $code = trim($_POST['code']);
    $discount_amount = trim($_POST['discount_amount']);
    $max_usage = trim($_POST['max_usage']);
    $is_active = isset($_POST['is_active']) ? 1 : 0; // Xử lý checkbox
    $create_at = date('Y-m-d H:i:s');

    // Validate
    if (empty($code)) {
        $errors['code']['required'] = 'Vui lòng ấn tạo mã!';
    }

    if (!is_numeric($discount_amount) || $discount_amount < 0 || $discount_amount > 99) {
        $errors['discount_amount']['invalid'] = 'Giá trị giảm phải nằm trong khoảng từ 0-99!';
    }

    if (!is_numeric($max_usage) || $max_usage < 1) {
        $errors['max_usage']['invalid'] = 'Số lần sử dụng tối thiểu là 1!';
    }

    if (empty($errors)) {
        $check_code = $conn->prepare("SELECT * FROM discount_codes WHERE code=?");
        $check_code->execute([$code]);

        if ($check_code->rowCount() > 0) {
            $message[] = create_message('warning', 'Mã giảm giá đã tồn tại!');
        } else {
            $insert_code = $conn->prepare("INSERT INTO discount_codes (code, discount_amount, max_usage, is_active, create_at) VALUES (?, ?, ?, ?, ?)");
            $insert_code->execute([$code, $discount_amount, $max_usage, $is_active, $create_at]);

            if ($insert_code) {
                $message[] = create_message('success', 'Thêm mã giảm giá thành công!');
            } else {
                $message[] = create_message('failed', 'Thêm mã giảm giá thất bại!');
            }
        }
    } else {
        $message[] = create_message('warning', 'Vui lòng nhập đủ thông tin để tạo mã!');
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã giảm giá</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <section class="discount-section">

        <h1 class="heading">Thêm mã giảm giá</h1>

        <form action="" method="post" enctype="multipart/form-data">
            <div class="discount-flex">
                <div class="discount-inputBox">
                    <span>Mã giảm</span>
                    <input type="text" class="discount-box" id="discountCode" placeholder="nhập mã giảm..." name="code">
                    <button type="button" class="generate-btn">Tạo mã</button>
                    <?php echo form_error('code', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>

                <div class="discount-inputBox">
                    <span>Số phần trăm giảm</span>
                    <input type="number" min="0" max="99" class="discount-box" placeholder="nhập số % giảm..." name="discount_amount" value="0">
                    <?php echo form_error('discount_amount', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="discount-inputBox">
                    <span>Số lần sử dụng</span>
                    <input type="number" min="1" class="discount-box" placeholder="số lần sử dụng..." name="max_usage">
                    <?php echo form_error('max_usage', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
                <div class="discount-inputBox">
                    <span>Hoạt động</span>
                    <div class="discount-checkbox-container">
                        <input type="checkbox" class="discount-checkbox" name="is_active" value="1">
                        <label for="is_active">Có</label>
                    </div>
                    <?php echo form_error('is_active', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
                </div>
            </div>
            <input type="submit" value="Tạo mã giảm giá" class="discount-btn" name="add_discount">
        </form>

    </section>

    <script src="../js/admin_script.js?version=<?php echo rand(); ?>"></script>
    <script>
        //random mã code
        function generateDiscountCode() {            
            //tạo tỷ lệ %
            const discountPercent=Math.floor(Math.random() * 79) + 1;

            //tạo mã giảm giá
            const discountCode= `FREESHIP ${discountPercent}%`;

            // Gán mã tạo ra vào input field
            document.getElementById('discountCode').value = discountCode;
        }
        document.querySelector('.generate-btn').addEventListener('click', generateDiscountCode);
    </script>

</body>

</html>