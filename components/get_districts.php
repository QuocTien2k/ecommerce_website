<?php
include './connect.php';

$province_id = $_GET['province_id']; // Lấy province_id từ request

// Truy vấn dữ liệu quận/huyện từ bảng district
$district_sql = "SELECT * FROM district WHERE province_id = :province_id";
$stmt = $conn->prepare($district_sql);
$stmt->bindParam(':province_id', $province_id, PDO::PARAM_INT);
$stmt->execute();

$districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($districts); // Trả về dữ liệu dưới dạng JSON
?>
