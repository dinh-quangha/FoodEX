<?php
session_start();
require_once "connect.php";

// --- Lấy món ăn ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filtered_dishes = [];

if ($keyword !== '') {
    $stmt = $connect->prepare("
        SELECT d.*, r.Name AS RestaurantName, r.Address, r.Image AS RestaurantImage
        FROM dish d
        INNER JOIN restaurant r ON d.RestaurantID = r.RestaurantID
        WHERE d.Name LIKE ?");
    $searchTerm = '%' . $keyword . '%';
    $stmt->bind_param("s", $searchTerm);
} else {
    $stmt = $connect->prepare("
        SELECT d.*, r.Name AS RestaurantName, r.Address, r.Image AS RestaurantImage
        FROM dish d
        INNER JOIN restaurant r ON d.RestaurantID = r.RestaurantID
    ");
}


$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $filtered_dishes[] = $row;
}
$stmt->close();

// --- Lấy danh sách nhà hàng ---
$restaurants = [];
$restaurantQuery = $connect->query("SELECT * FROM restaurant LIMIT 6");
while ($row = $restaurantQuery->fetch_assoc()) {
    $restaurants[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>FoodX Delivery</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Quicksand', sans-serif; background: #fdfdfd; color: #333; }
    header { background: #ffffff; border-bottom: 1px solid #eee; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
    .container { max-width: 1200px; margin: auto; padding: 15px 20px; }
    nav ul { display: flex; justify-content: space-between; align-items: center; list-style: none; flex-wrap: wrap; }
    nav a { color: #2E7D32; text-decoration: none; font-weight: 600; font-size: 15px; display: flex; align-items: center; gap: 6px; transition: color 0.2s ease; }
    nav a:hover { color: #FF9800; }
    .search-bar { display: flex; justify-content: center; margin-top: 10px; }
    .search-bar input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; width: 240px; font-size: 14px; }
    .search-bar button { background-color: #FF9800; border: none; padding: 8px 14px; color: white; border-radius: 6px; cursor: pointer; margin-left: 6px; transition: background-color 0.3s ease; }
    .search-bar button:hover { background-color: #F57C00; }
    .cart-count { background: red; border-radius: 50%; padding: 2px 6px; font-size: 12px; color: white; font-weight: bold; }

    /* Slideshow */
    .banner-slideshow { position: relative; width: 100%; height: 380px; overflow: hidden; margin-bottom: 30px; }
    .banner-slideshow .slides { display: flex; transition: transform 1s ease-in-out; }
    .banner-slideshow img { width: 100%; object-fit: cover; height: 380px; flex-shrink: 0; }
    .banner-text { position: absolute; bottom: 40px; left: 50px; background: rgba(0,0,0,0.5); padding: 20px 25px; border-radius: 8px; color: white; }
    .banner-text h1 { font-size: 38px; margin-bottom: 8px; }
    .banner-text a { background: #FF9800; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; }
    .banner-text a:hover { background: #F57C00; }

    .foods, .restaurants { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
    .food-card, .restaurant-card { background: white; width: 270px; padding: 15px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; }
    .food-card:hover, .restaurant-card:hover { transform: translateY(-5px); box-shadow: 0 6px 15px rgba(0,0,0,0.15); }
    .food-card img, .restaurant-card img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; }
    .food-card h3, .restaurant-card h3 { margin: 10px 0 5px; font-size: 18px; color: #333; }
    .food-card p, .restaurant-card p { font-size: 14px; color: #666; min-height: 40px; }
    .price { color: #E91E63; font-weight: bold; margin-top: 8px; font-size: 16px; }
    .old-price { text-decoration: line-through; color: #aaa; font-size: 14px; margin-left: 6px; }
    .food-card button { margin-top: 10px; padding: 10px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-weight: 600; font-size: 14px; }
    .food-card button:hover { background-color: #388E3C; }
    .food-card a, .restaurant-card a { display: block; text-align: center; margin-top: 8px; color: #FF9800; text-decoration: none; font-weight: 600; }
    footer { background-color: #2E7D32; color: white; text-align: center; padding: 15px; margin-top: 40px; }

    /* Responsive */
    @media (max-width: 768px) {
      .banner-slideshow { height: 260px; }
      .banner-slideshow img { height: 260px; }
      .banner-text h1 { font-size: 26px; }
    }
  </style>
</head>
<body>

<header>
  <div class="container">
    <nav>
      <ul>
        <div class="nav-left">
          <?php if (isset($_SESSION['user'])): ?>
            <li><a><i class="fa-solid fa-user"></i> Hello, <b><?= htmlspecialchars($_SESSION['user']); ?></b></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
          <?php else: ?>
            <li><a href="login.php"><i class="fa-solid fa-right-to-bracket"></i> Log in</a></li>
            <li><a href="register.php"><i class="fa-solid fa-user-plus"></i> Register</a></li>
          <?php endif; ?>
        </div>
        <div class="nav-center">
          <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
        </div>
        <div class="nav-right">
          <li>
            <a href="cart.php">
              <i class="fa-solid fa-cart-shopping"></i> Cart
              <span class="cart-count"><?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?></span>
            </a>
          </li>
        </div>
      </ul>
      <div class="search-bar">
        <form method="GET">
          <input type="text" name="keyword" placeholder="Search food..." value="<?= htmlspecialchars($keyword); ?>">
          <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
        </form>
      </div>
    </nav>
  </div>
</header>

<!-- Slideshow -->
<div class="banner-slideshow">
  <div class="slides" id="slides">
    <img src="https://images.unsplash.com/photo-1600891964599-f61ba0e24092" alt="Slide 1">
    <img src="https://inan2h.vn/wp-content/uploads/2022/12/in-banner-quang-cao-do-an-4-1.jpg" alt="Slide 2">
    <img src="https://d3design.vn/uploads/anh-bia-Summer%20drink%20menu%20promotion%20banner%20template.jpg" alt="Slide 3">
  </div>
  <div class="banner-text">
    <h1>Giao đồ ăn siêu tốc 🚀</h1>
    <a href="#menu">Đặt món ngay</a>
  </div>
</div>

<!-- Danh sách nhà hàng -->
<section class="container" id="restaurants">
  <h2 style="text-align:center; margin: 50px 0 30px;">Nhà hàng nổi bật</h2>
  <div class="restaurants">
    <?php foreach ($restaurants as $res): ?>
      <div class="restaurant-card">
        <img src="<?= htmlspecialchars($res['Image']); ?>" alt="<?= htmlspecialchars($res['Name']); ?>">
        <h3><?= htmlspecialchars($res['Name']); ?></h3>
        <p><?= htmlspecialchars($res['Address']); ?></p>

        <!-- Hiển thị sao đánh giá -->
        <div class="rating">
          <?php
            $rating = isset($res['Rating']) ? (float)$res['Rating'] : 0;
            $fullStars = floor($rating);
            $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
            $emptyStars = 5 - $fullStars - $halfStar;

            for ($i = 0; $i < $fullStars; $i++) {
              echo '<i class="fa-solid fa-star" style="color:#FFD700;"></i>';
            }
            if ($halfStar) {
              echo '<i class="fa-solid fa-star-half-stroke" style="color:#FFD700;"></i>';
            }
            for ($i = 0; $i < $emptyStars; $i++) {
              echo '<i class="fa-regular fa-star" style="color:#FFD700;"></i>';
            }
          ?>
          <span style="font-size: 14px; color: #666;">
            (<?= number_format($rating, 1) ?>/5)
          </span>
        </div>

        <a href="restaurant_detail.php?id=<?= $res['RestaurantID']; ?>">Xem chi tiết</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>


<!-- Món ăn nổi bật -->
<main class="container" id="menu">
  <h2 style="text-align:center; margin: 30px 0;">Món ăn nổi bật</h2>
  <div class="foods">
    <?php if (count($filtered_dishes) > 0): ?>
      <?php foreach ($filtered_dishes as $dish): ?>
        <div class="food-card">
          <img src="<?= htmlspecialchars($dish['Image']); ?>" alt="<?= htmlspecialchars($dish['Name']); ?>">
          <h3><?= htmlspecialchars($dish['Name']); ?></h3>
          <p><?= htmlspecialchars($dish['Description']); ?></p>
          <p class="price">
  <?= number_format($dish['Price'], 0, ',', '.'); ?>đ
  <?php if (!empty($dish['OldPrice']) && $dish['OldPrice'] > $dish['Price']): ?>
    <span class="old-price"><?= number_format($dish['OldPrice'], 0, ',', '.'); ?>đ</span>
  <?php endif; ?>
</p>

          <button onclick="addToCart(<?= $dish['DishID']; ?>)">🛒 Thêm vào giỏ</button>
          <a href="product_detal.php?id=<?= $dish['DishID']; ?>">Xem chi tiết</a>

          <!-- Thông tin nhà hàng -->
          <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #ccc; font-size: 14px; color: #555;">
            <strong>Nhà hàng:</strong> <?= htmlspecialchars($dish['RestaurantName']); ?><br>
            <strong>Địa chỉ:</strong> <?= htmlspecialchars($dish['Address']); ?><br>
            
          </div>

        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align: center;">Không tìm thấy món ăn với từ khóa "<strong><?= htmlspecialchars($keyword); ?></strong>"</p>
    <?php endif; ?>
  </div>
</main>



<footer>
  &copy; 2025 FoodX Delivery - Demo project using PHP & MySQL
</footer>

<script>
  function addToCart(dishId) {
    fetch('add_to_cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'dish_id=' + dishId
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'not_logged_in') {
        if (confirm('Bạn cần đăng nhập để thêm món vào giỏ hàng. Chuyển đến trang đăng nhập?')) {
          window.location.href = 'login.php';
        }
      } else if (data.status === 'success') {
        alert('Đã thêm món vào giỏ hàng!');
        location.reload();
      } else {
        alert('Lỗi! Không thể thêm món vào giỏ.');
      }
    });
  }

  // Slideshow JS
  const slides = document.getElementById('slides');
  const total = slides.children.length;
  let index = 0;
  setInterval(() => {
    index = (index + 1) % total;
    slides.style.transform = `translateX(-${index * 100}%)`;
  }, 3000);
</script>

</body>
</html>
