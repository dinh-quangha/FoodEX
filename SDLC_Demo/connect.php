<?php
$connect = new mysqli("localhost", "root", "", "asm2"); // Thay "asm2" bằng tên CSDL thật của bạn

if ($connect->connect_error) {
    die("Kết nối thất bại: " . $connect->connect_error);
}
?>
