<?php
session_start();

// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "asm2");
if (!$conn) {
    die("Lỗi kết nối: " . mysqli_connect_error());
}

// Thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $foodId = (int)$_POST['food_id'];
    if (!isset($_SESSION['cart'][$foodId])) {
        $_SESSION['cart'][$foodId] = 1;
    } else {
        $_SESSION['cart'][$foodId]++;
    }
    $message = "✅ Món ăn đã được thêm vào giỏ!";
}

// Xử lý tìm kiếm
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$sql = "SELECT * FROM dish";
if ($keyword !== '') {
    $keyword_safe = mysqli_real_escape_string($conn, $keyword);
    $sql .= " WHERE Name LIKE '%$keyword_safe%'";
}
$result = mysqli_query($conn, $sql);

$foods = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $foods[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>🍽️ Danh sách món ăn</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    .food-card {
      border: 1px solid #ddd;
      padding: 12px;
      border-radius: 8px;
      margin: 10px;
      width: 240px;
      display: inline-block;
      vertical-align: top;
      background: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .food-card img {
      width: 100%;
      border-radius: 6px;
      height: 150px;
      object-fit: cover;
    }
    .food-card h4 { margin: 10px 0 5px; }
    .food-card p { margin: 0 0 5px; color: #555; }
    .price { color: #c00; font-weight: bold; }
    .search-form input {
      padding: 7px;
      font-size: 16px;
    }
    .search-form button {
      padding: 7px 12px;
      font-size: 16px;
      background: #00b14f;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .add-btn {
      margin-top: 8px;
      background: #00b14f;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .cart-box {
      background: #fff;
      padding: 10px;
      border-radius: 6px;
      display: inline-block;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .message { color: green; margin-bottom: 15px; }
  </style>
</head>
<body>

  <h2>🍱 Danh sách món ăn </h2>

  <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

  <div class="cart-box">
    🧺 Giỏ hàng: <strong><?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?></strong> món
    | <a href="cart.php">Xem giỏ</a>
  </div>

  <form method="GET" class="search-form">
    <input type="text" name="keyword" placeholder="Tìm món ăn..." value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit">Tìm kiếm</button>
  </form>

  <div style="margin-top: 20px;">
    <?php if (empty($foods)): ?>
      <p>❌ Không tìm thấy món ăn phù hợp.</p>
    <?php else: ?>
      <?php foreach ($foods as $f): ?>
        <div class="food-card">
          <img src="<?= htmlspecialchars($f['Image']) ?>" alt="<?= htmlspecialchars($f['Name']) ?>">
          <h4><?= htmlspecialchars($f['Name']) ?></h4>
          <p><?= htmlspecialchars($f['Description']) ?></p>
          <p class="price"><?= number_format($f['Price'], 0, ',', '.') ?>đ</p>
          <form method="POST">
            <input type="hidden" name="food_id" value="<?= $f['DishID'] ?>">
            <button type="submit" name="add_to_cart" class="add-btn">➕ Thêm vào giỏ</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>
