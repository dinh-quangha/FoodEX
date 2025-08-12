<?php
session_start();
$connect = mysqli_connect("localhost", "root", "", "asm2");

if (!$connect) {
    die("Database connection error: " . mysqli_connect_error());
}

$dishId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$selected = null;
if ($dishId > 0) {
    $stmt = mysqli_prepare($connect, "
        SELECT d.*, r.Name AS RestaurantName, r.Address AS RestaurantAddress
        FROM dish d
        LEFT JOIN restaurant r ON d.RestaurantID = r.RestaurantID
        WHERE d.DishID = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $dishId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $selected = mysqli_fetch_assoc($result);
}

if (!$selected) {
    echo "<p style='padding: 20px;'>Không tìm thấy sản phẩm.</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($selected['Name']) ?> - FoodX</title>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Quicksand', sans-serif;
      background: linear-gradient(to bottom, #fff, #f4f8fb);
      color: #333;
    }
     header {
        background-color: #ff6f61;
        color: white;
        padding: 15px;
        text-align: center;
        font-size: 24px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .container {
      max-width: 960px;
      margin: 50px auto;
      padding: 25px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
      animation: fadeIn 0.5s ease-in-out;
    }
    h2 {
      margin: 0;
      font-size: 30px;
      color: #222;
    }
    .restaurant-info {
      margin-bottom: 20px;
      font-size: 16px;
      color: #555;
      line-height: 1.5;
    }
    .restaurant-name {
      font-size: 18px;
      font-weight: 600;
      color: #444;
    }
    .restaurant-address {
      font-size: 15px;
      color: #777;
    }
    .detail {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
    }
    .detail img {
      width: 100%;
      max-width: 420px;
      height: auto;
      border-radius: 12px;
      object-fit: cover;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      transition: transform 0.3s ease;
    }
    .detail img:hover {
      transform: scale(1.03);
    }
    .detail-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .detail-info p {
      font-size: 17px;
      line-height: 1.6;
    }
    .price {
      font-size: 24px;
      font-weight: bold;
      color: #e91e63;
      margin-top: 10px;
    }
    button {
      margin-top: 20px;
      padding: 14px 28px;
      font-size: 16px;
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 126, 95, 0.4);
    }
    .back {
      display: inline-block;
      margin-top: 30px;
      text-decoration: none;
      color: #2196f3;
      font-weight: 600;
    }
    .back:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .detail {
        flex-direction: column;
        align-items: center;
      }
      .detail-info {
        text-align: center;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2><?= htmlspecialchars($selected['Name']) ?></h2>
    <div class="restaurant-info">
      <div class="restaurant-name">🍽 Nhà hàng: <?= htmlspecialchars($selected['RestaurantName'] ?? 'Không xác định') ?></div>
      <div class="restaurant-address">📍 Địa chỉ: <?= htmlspecialchars($selected['RestaurantAddress'] ?? 'Không rõ') ?></div>
    </div>
    <div class="detail">
      <img src="<?= htmlspecialchars($selected['Image']) ?>" alt="<?= htmlspecialchars($selected['Name']) ?>">
      <div class="detail-info">
        <p><?= htmlspecialchars($selected['Description']) ?></p>
        <p class="price"><?= number_format($selected['Price'], 0, ',', '.') ?>đ</p>
        <button onclick="addToCart(<?= $selected['DishID'] ?>)">🛒 Thêm vào giỏ hàng</button>
      </div>
    </div>
    <a class="back" href="index.php">← Quay lại trang chủ</a>
  </div>

  <script>
    function addToCart(dishId) {
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'dish_id=' + dishId
      })
      .then(response => response.text())
      .then(data => {
        alert('Đã thêm vào giỏ hàng!');
        window.location.href = 'cart.php';
      });
    }
  </script>
</body>
</html>
