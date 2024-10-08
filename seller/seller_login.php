<?php

include '../components/connect.php';
include '../components/function.php';

session_start();

$errors = [];
if (isset($_POST['submit'])) {

    // Lấy và xử lý dữ liệu đầu vào
    $email = trim($_POST['email'] ?? '');
    $email = filter_var($email, FILTER_SANITIZE_STRING);

    $pass = trim($_POST['pass'] ?? '');
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);

    // Validate email, quy tắc: required, định dạng hợp lệ
    if (empty($email)) {
        $errors['email']['required'] = 'Vui lòng nhập email!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email']['isEmail'] = 'Email không hợp lệ!';
    }

    // Validate password, quy tắc: required
    if (empty($pass)) {
        $errors['pass']['required'] = 'Vui lòng nhập mật khẩu!';
    }

    // Kiểm tra nếu không có lỗi
    if (empty($errors)) {
        // Truy vấn cơ sở dữ liệu dựa trên tên tài khoản
        $user_type= 'seller';
        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND user_type= ?");
        $select_user->execute([$email, $user_type]);
        $row = $select_user->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra kết quả truy vấn
        if ($row) {
            // Sử dụng password_verify() để kiểm tra mật khẩu
            if (password_verify($pass, $row['password'])) {
                $_SESSION['seller_id'] = $row['id'];
                header('location: seller_dashboard.php');
                exit(); // Dừng script sau khi chuyển hướng
            } else {
                $message[] = create_message('failed', 'Mật khẩu không đúng!');
            }
        } else {
            $message[] = create_message('failed', 'Email không có trong hệ thống!');
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
    <title>Đăng nhập</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css?version=<?php echo rand(); ?>">

</head>

<body>

    <?php
    if (isset($message)) {
        foreach ($message as $msg) {
            echo '<div class="message ' . $msg['type'] . '">
            <div class="toast">
               <i class="' . $msg['icon'] . '"></i>
               <p>' . $msg['text'] . '</p>
            </div>
          </div>';
        }
    }
    ?>

    <section class="form-container">

        <form action="" method="post">
            <h3>Đăng nhập</h3>
            <input type="email" name="email" placeholder="Nhập email của bạn..." class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>">
            <?php echo form_error('email', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            <input type="password" name="pass" placeholder="Mật khẩu..." class="box" oninput="this.value = this.value.replace(/\s/g, '')">
            <?php echo form_error('pass', $errors, '<span class="error" style="font-size: 16px; color: red;">', '</span>'); ?>
            <div style="display: flex; align-items: center; justify-content: center;">
                <input type="submit" value="đăng nhập" class="btn" name="submit">
            </div>
            <a href="register_seller.php" class="btn-seller">Đăng ký bán hàng</a>
        </form>

    </section>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const messages = document.querySelectorAll('.message');
            let topOffset = 12; // Bắt đầu từ 10%

            // Hàm để ẩn thông báo với hiệu ứng slideOut
            function hideMessage(message) {
                message.style.animation = 'slideOut 1.2s ease forwards'; // Thêm animation slideOut
                setTimeout(() => {
                    message.remove(); // Xóa thông báo sau khi animation kết thúc
                }, 1200); // Thời gian chờ phải bằng thời gian của animation
            }

            // Lặp qua từng thông báo và đặt thời gian để ẩn nó sau 5 giây
            messages.forEach((message, index) => {
                // Điều chỉnh top để các thông báo không chồng lên nhau
                message.style.top = `${topOffset}%`;
                topOffset += 10; // Tăng giá trị top cho thông báo tiếp theo

                setTimeout(() => {
                    hideMessage(message);
                }, 4500);
            });
        });
    </script>
</body>

</html>