<?php
session_start();

// Kết nối CSDL
$connect = mysqli_connect("localhost", "root", "", "asm2");
if (!$connect) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Cập nhật số lượng món ăn
if (isset($_POST['update']) && isset($_POST['dish_id']) && isset($_POST['quantity'])) {
    $dish_id = $_POST['dish_id'];
    $quantity = max(1, intval($_POST['quantity'])); // Không cho nhỏ hơn 1
    if (isset($_SESSION['cart'][$dish_id]) && is_array($_SESSION['cart'][$dish_id])) {
        $_SESSION['cart'][$dish_id]['quantity'] = $quantity;
    }
}

// Xóa món khỏi giỏ
if (isset($_POST['remove']) && isset($_POST['dish_id'])) {
    $dish_id = $_POST['dish_id'];
    unset($_SESSION['cart'][$dish_id]);
}

// Lấy danh sách món
$cart = $_SESSION['cart'] ?? [];
$dish_ids = array_keys($cart);
$dishes = [];
$total = 0;

if (!empty($dish_ids)) {
    $ids = implode(',', array_map('intval', $dish_ids));
    $sql = "SELECT * FROM dish WHERE DishID IN ($ids)";
    $result = mysqli_query($connect, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $dishes[$row['DishID']] = $row;
    }

    // Xoá khỏi session món không còn trong CSDL
    foreach ($cart as $id => $item) {
        if (!isset($dishes[$id])) {
            unset($_SESSION['cart'][$id]);
            unset($cart[$id]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Giỏ hàng - FoodX Delivery</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .cart-container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 40px; }
    .cart-title { color: #00b14f; text-align: center; margin-bottom: 30px; font-size: 2rem; }
    .cart-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .cart-table th, .cart-table td { padding: 14px; border-bottom: 1px solid #eee; text-align: center; }
    .cart-table th { background: #00b14f; color: #fff; }
    .cart-img { width: 80px; border-radius: 8px; }
    .cart-actions form { display: inline-block; }
    .cart-actions input[type='number'] { width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #ccc; }
    .cart-actions button { background: #00b14f; color: #fff; border: none; padding: 7px 14px; border-radius: 5px; cursor: pointer; margin-left: 4px; }
    .cart-actions button.remove { background: #ff424e; }
    .cart-actions button:hover { opacity: 0.85; }
    .cart-total { text-align: right; font-size: 20px; font-weight: bold; margin: 20px 0; }
    .checkout-btn { background: #00b14f; color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-size: 18px; font-weight: 600; cursor: pointer; float: right; margin-top: 10px; }
    .checkout-btn:hover { background: #008f3a; }
    .empty-cart { text-align: center; color: #888; font-size: 22px; margin: 60px 0; }
    @media (max-width: 700px) {
      .cart-container { padding: 10px; }
      .cart-table th, .cart-table td { padding: 7px; font-size: 14px; }
      .cart-title { font-size: 1.2rem; }
    }
  </style>
</head>
<body>
  <header>
    <div class="container header-flex">
      <img src="logo-grabfood2.svg" alt="Logo" class="logo">
      <nav>
        <ul>
          <li><a href="index.php">Home</a></li>
          <?php if(isset($_SESSION['user'])): ?>
            <li><span>Welcome, <b><?php echo htmlspecialchars($_SESSION['user']); ?></b></span></li>
            <li><a href="logout.php">Log out</a></li>
          <?php else: ?>
            <li><a href="login.php">Log in</a></li>
            <li><a href="register.php">Register</a></li>
          <?php endif; ?>
          <li><a href="cart.php">Cart <span class="cart-count"><?php echo count($cart); ?></span></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <div class="cart-container">
      <h2 class="cart-title">🛒 Your Shopping Cart</h2>
      <?php if (empty($cart)): ?>
        <div class="empty-cart">Your cart is empty. <a href="index.php">Go shopping!</a></div>
      <?php else: ?>
        <table class="cart-table">
          <tr>
            <th>Image</th>
            <th>Dish</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
            <th>Actions</th>
          </tr>
          <?php foreach ($cart as $id => $item): ?>
            <?php if (!isset($dishes[$id])) continue; ?>
            <?php 
              $dish = $dishes[$id];
              $quantity = is_array($item) && isset($item['quantity']) ? $item['quantity'] : 1;
              $subtotal = $dish['Price'] * $quantity;
              $total += $subtotal;
            ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($dish['Image']); ?>" class="cart-img"></td>
              <td><?php echo htmlspecialchars($dish['Name']); ?></td>
              <td><?php echo number_format($dish['Price'], 0, ',', '.'); ?>đ</td>
              <td>
                <form method="post" class="cart-actions">
                  <input type="hidden" name="dish_id" value="<?php echo $id; ?>">
                  <input type="number" name="quantity" value="<?php echo $quantity; ?>" min="1">
                  <button type="submit" name="update">Update</button>
                </form>
              </td>
              <td><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
              <td>
                <form method="post" class="cart-actions">
                  <input type="hidden" name="dish_id" value="<?php echo $id; ?>">
                  <button type="submit" name="remove" class="remove">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
        <div class="cart-total">Total: <?php echo number_format($total, 0, ',', '.'); ?>đ</div>
        <form action="checkout.php" method="post">
          <button type="submit" class="checkout-btn">Checkout</button>
        </form>
      <?php endif; ?>
    </div>
  </main>

  <footer>
    <div class="footer-main">
      <div class="footer-cols">
        <div class="footer-col footer-col-brand">
          <img src="logo-grabfood2.svg" alt="FoodX Delivery" class="footer-logo">
          <div class="footer-slogan">Deliver delicious food to your door, fast and reputable!</div>
        </div>
        <div class="footer-col">
          <h4>About <span class="brand-green">FoodX</span></h4>
          <ul>
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Use</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Blog</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Connect with us</h4>
          <div class="footer-socials">
            <a href="https://facebook.com/" target="_blank"><img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" class="footer-icon"></a>
            <a href="https://zalo.me/" target="_blank"><img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/zalo.svg" alt="Zalo" class="footer-icon"></a>
          </div>
          <div class="footer-hotline">
            <span>Hotline:</span> <a href="tel:0123456789">0878 922005</a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2025 FoodShop Delivery. Inspired by Group3Food</span>
      </div>
    </div>
  </footer>
</body>
</html>
