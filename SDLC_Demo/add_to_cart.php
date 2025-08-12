<?php
session_start();

if (!isset($_SESSION['user'])) {
    // Trả về phản hồi JSON nếu chưa đăng nhập
    echo json_encode(['status' => 'not_logged_in']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['dish_id'])) {
    $dish_id = (int)$_POST['dish_id'];

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Nếu món đã có trong giỏ thì tăng số lượng, ngược lại thêm mới
    if (isset($_SESSION['cart'][$dish_id])) {
        $_SESSION['cart'][$dish_id]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$dish_id] = ['quantity' => 1];
    }

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'invalid_request']);
}
?>
