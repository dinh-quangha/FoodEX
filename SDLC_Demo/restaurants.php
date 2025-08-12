<?php
// Kết nối CSDL asm2
$connect = mysqli_connect("localhost", "root", "", "asm2");
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Lấy tất cả nhà hàng
$query = mysqli_query($connect, "SELECT * FROM restaurant");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Danh sách Nhà hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2 class="mb-4">Danh sách Nhà hàng</h2>
    <div class="row">
        <?php while ($row = mysqli_fetch_assoc($query)): ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <img src="<?= htmlspecialchars($row['Image']) ?>" 
                     class="card-img-top" 
                     style="height:200px; object-fit:cover;">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($row['Name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($row['Address']) ?></p>
                    <a href="restaurant_detail.php?id=<?= $row['RestaurantID'] ?>" 
                       class="btn btn-primary">Xem chi tiết</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <div class="text-center mt-4">
    <a href="index.php" class="btn btn-primary">🏠 Quay lại trang chủ</a>
</div>

</body>
</html>
