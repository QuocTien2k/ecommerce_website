<?php
// Hàm form_error để hiển thị lỗi
function form_error($fieldName, $errors, $beforeHtml = '', $afterHtml = '')
{
    // Kiểm tra xem $errors[$fieldName] có phải là mảng không
    if (!empty($errors[$fieldName]) && is_array($errors[$fieldName])) {
        return $beforeHtml . reset($errors[$fieldName]) . $afterHtml;
    }
    return null;
}


function create_message($type, $text)
{
    $icons = [
        'success' => 'fa-solid fa-circle-check',
        'failed' => 'fa-solid fa-circle-xmark',
        'warning' => 'fa-solid fa-triangle-exclamation',
    ];

    // Kiểm tra xem $type có trong danh sách icons không
    $icon = $icons[$type] ?? 'fa-solid fa-info-circle'; // Biểu tượng mặc định nếu không có $type phù hợp

    return [
        'type' => $type,
        'text' => $text,
        'icon' => $icon,
    ];
}
//hàm validate hình ảnh
function validate_image($image, &$errors, $field_name)
{
    $image_name = $image['name'];
    $image_size = $image['size'];
    $image_tmp_name = $image['tmp_name'];

    // Kiểm tra xem file có được chọn không
    if (empty($image_name)) {
        $errors[$field_name]['required'] = 'Vui lòng chọn một hình ảnh!';
        return false;
    }

    // Kiểm tra loại file
    $allowed_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
    $image_type = mime_content_type($image_tmp_name);

    if (!in_array($image_type, $allowed_types)) {
        $errors[$field_name]['type'] = 'Chỉ chấp nhận các định dạng JPG, JPEG, PNG, hoặc WEBP!';
        return false;
    }

    // Kiểm tra kích thước file (ví dụ: 2MB)
    if ($image_size > 2 * 1024 * 1024) {
        $errors[$field_name]['size'] = 'Kích thước hình ảnh không được vượt quá 2MB!';
        return false;
    }

    return true; // Nếu tất cả các kiểm tra đều hợp lệ
}

//hàm upload hình ảnh
function upload_image($image, $destination_folder)
{
    $image_name = filter_var($image['name'], FILTER_SANITIZE_STRING);
    $image_tmp_name = $image['tmp_name'];
    $image_folder = $destination_folder . $image_name;

    // Di chuyển file từ thư mục tạm đến thư mục đích
    if (move_uploaded_file($image_tmp_name, $image_folder)) {
        return $image_name;
    } else {
        return false; // Trả về false nếu upload không thành công
    }
}

// Hàm để thêm dữ liệu vào bảng options
function insert_option($section, $option_name, $option_value, $display_name, $icon_class = '', $title='')
{
    global $conn;
    $update_at = date('Y-m-d h:i:s');

    // Kiểm tra nếu section là footer, mới thêm icon_class
    if ($section === 'footer') {
        $query = "INSERT INTO options (section, option_name, option_value, display_name, icon_class, title, update_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$section, $option_name, $option_value, $display_name, $icon_class, $title, $update_at]);
    } else {
        // Nếu là header hoặc slider, không thêm icon_class
        $query = "INSERT INTO options (section, option_name, option_value, display_name, update_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$section, $option_name, $option_value, $display_name, $update_at]);
    }
}

