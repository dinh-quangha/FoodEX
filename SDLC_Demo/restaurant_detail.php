<?php
session_start();
$connect = mysqli_connect("localhost", "root", "", "asm2");
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID nhà hàng không hợp lệ!");
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM restaurant WHERE RestaurantID = $id";
$result = mysqli_query($connect, $sql);
$restaurant = mysqli_fetch_assoc($result);

if (!$restaurant) {
    die("Không tìm thấy nhà hàng!");
}

// Lấy món nổi bật
$featuredFoods = mysqli_query($connect, "SELECT * FROM dish WHERE RestaurantID = $id AND IsFeatured = 1");

// Lấy các món khác
$otherFoods = mysqli_query($connect, "SELECT * FROM dish WHERE RestaurantID = $id AND IsFeatured = 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($restaurant['Name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
    function addToCart(foodId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'type=food&id=' + encodeURIComponent(foodId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'not_logged_in') {
                window.location.href = 'login.php';
            } else if (data.status === 'success') {
                alert('✅ Đã thêm món vào giỏ hàng!');
            } else {
                alert('❌ Có lỗi xảy ra, vui lòng thử lại.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</head>
<body class="container mt-4">

    <h2><?= htmlspecialchars($restaurant['Name']) ?></h2>
    <img src="<?= htmlspecialchars($restaurant['Image']) ?>" class="img-fluid mb-3" style="max-height:400px; object-fit:cover;">
    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($restaurant['Address']) ?></p>
    <p><?= nl2br(htmlspecialchars($restaurant['Description'])) ?></p>

   <!-- Món ăn nổi bật -->
<h3 class="mt-5">🍽 Món ăn nổi bật</h3>
<div class="row">
    <?php while ($food = mysqli_fetch_assoc($featuredFoods)): ?>
    <div class="col-md-4">
        <div class="card mb-4">
            <img src="<?= htmlspecialchars($food['Image']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($food['Name']) ?></h5>
                <p class="card-text"><?= number_format($food['Price'], 0) ?> VNĐ</p>
                <p><?= htmlspecialchars($food['Description']) ?></p>
                <button onclick="addToCart(<?= $food['DishID'] ?>)" class="btn btn-success w-100 mb-2">🛒 Thêm vào giỏ</button>
                <a href="food_detail.php?id=<?= $food['DishID'] ?>" class="btn btn-outline-primary w-100">🔍 Xem chi tiết</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Các món khác -->
<h3 class="mt-5">🥢 Các món khác</h3>
<div class="row">
    <?php while ($food = mysqli_fetch_assoc($otherFoods)): ?>
    <div class="col-md-3">
        <div class="card mb-4">
            <img src="<?= htmlspecialchars($food['Image']) ?>" class="card-img-top" style="height:150px; object-fit:cover;">
            <div class="card-body">
                <h6 class="card-title"><?= htmlspecialchars($food['Name']) ?></h6>
                <p class="card-text"><?= number_format($food['Price'], 0) ?> VNĐ</p>
                <button onclick="addToCart(<?= $food['DishID'] ?>)" class="btn btn-success w-100 mb-2">🛒 Thêm vào giỏ</button>
                <a href="food_detail.php?id=<?= $food['DishID'] ?>" class="btn btn-outline-primary w-100">🔍 Xem chi tiết</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
    <a href="restaurants.php" class="btn btn-secondary">⬅ Quay lại danh sách</a>
    <a href="index.php" class="btn btn-primary">🏠 Trang chủ</a>

</body>
</html>
