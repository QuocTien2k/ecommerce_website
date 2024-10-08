<?php
include './connect.php'; // Kết nối đến cơ sở dữ liệu

$district_id = $_GET['district_id']; // Lấy district_id từ request

// Truy vấn dữ liệu phường/xã từ bảng wards
$wards_sql = "SELECT * FROM wards WHERE district_id = :district_id";
$stmt = $conn->prepare($wards_sql);
$stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
$stmt->execute();

$wards = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($wards); // Trả về dữ liệu dưới dạng JSON
?>
