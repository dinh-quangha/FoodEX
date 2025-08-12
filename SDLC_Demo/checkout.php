<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asm2";
$connect = mysqli_connect($servername, $username, $password, $dbname);

// Nếu chưa đăng nhập, chuyển hướng login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Lấy userID
$user_query = mysqli_query($connect, "SELECT UserID FROM users WHERE Username='" . mysqli_real_escape_string($connect, $_SESSION['user']) . "'");
$user_row = mysqli_fetch_assoc($user_query);
$user_id = $user_row['UserID'];

// Lấy giỏ hàng
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
}

// Xử lý đặt hàng
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($cart)) {
    $status = 'Pending';
    $order_date = date('Y-m-d H:i:s');
    $total_amount = 0;
    foreach ($cart as $id => $item) {
        $quantity = is_array($item) ? $item['quantity'] : $item;
        $total_amount += $dishes[$id]['Price'] * $quantity;
    }

    // Thêm vào orders
    $sql = "INSERT INTO orders (UserID, Status, OrderDate, TotalAmount, DeliveryStatus) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    $delivery_status = 'Processing';
    mysqli_stmt_bind_param($stmt, "issds", $user_id, $status, $order_date, $total_amount, $delivery_status);
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($connect);

        // Thêm vào orderdetail
        foreach ($cart as $id => $item) {
            $quantity = is_array($item) ? $item['quantity'] : $item;
            $dish = $dishes[$id];
            $sql_detail = "INSERT INTO orderdetail (OrderID, DishID, Quantity, Price) VALUES (?, ?, ?, ?)";
            $stmt_detail = mysqli_prepare($connect, $sql_detail);
            mysqli_stmt_bind_param($stmt_detail, "iiid", $order_id, $id, $quantity, $dish['Price']);
            mysqli_stmt_execute($stmt_detail);
        }

        unset($_SESSION['cart']);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - FoodX Delivery</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .checkout-container { max-width: 1000px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 40px; }
    .checkout-title { color: #00b14f; text-align: center; margin-bottom: 30px; font-size: 2rem; }
    .checkout-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .checkout-table th, .checkout-table td { padding: 14px; border-bottom: 1px solid #eee; text-align: center; }
    .checkout-table th { background: #00b14f; color: #fff; }
    .checkout-img { width: 80px; border-radius: 8px; }
    .checkout-total { text-align: right; font-size: 20px; font-weight: bold; margin: 20px 0; }
    .placeorder-btn { background: #00b14f; color: #fff; border: none; padding: 14px 32px; border-radius: 8px; font-size: 18px; font-weight: 600; cursor: pointer; float: right; margin-top: 10px; }
    .placeorder-btn:hover { background: #008f3a; }
    .success-msg { text-align: center; color: #00b14f; font-size: 22px; margin: 60px 0; }
    @media (max-width: 700px) {
      .checkout-container { padding: 10px; }
      .checkout-table th, .checkout-table td { padding: 7px; font-size: 14px; }
      .checkout-title { font-size: 1.2rem; }
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
          <li><a href="cart.php">Shopping Cart <span class="cart-count"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></a></li>
          <li><span>Welcome, <b><?php echo htmlspecialchars($_SESSION['user']); ?></b></span></li>
          <li><a href="logout.php">Log out</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main>
    <div class="checkout-container">
      <h2 class="checkout-title">🧾 Checkout</h2>
      <?php if ($success): ?>
        <div class="success-msg">Order placed successfully! <a href="orders.php">View your orders</a></div>
      <?php elseif (empty($cart)): ?>
        <div class="success-msg">Your cart is empty. <a href="index.php">Go shopping!</a></div>
      <?php else: ?>
        <form method="post">
          <table class="checkout-table">
            <tr>
              <th>Image</th>
              <th>Dish</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Subtotal</th>
            </tr>
            <?php foreach ($cart as $id => $item):
              $dish = $dishes[$id];
              $quantity = is_array($item) ? $item['quantity'] : $item;
              $subtotal = $dish['Price'] * $quantity;
              $total += $subtotal;
            ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($dish['Image']); ?>" class="checkout-img"></td>
              <td><?php echo htmlspecialchars($dish['Name']); ?></td>
              <td><?php echo number_format($dish['Price'], 0, ',', '.'); ?>đ</td>
              <td><?php echo $quantity; ?></td>
              <td><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</td>
            </tr>
            <?php endforeach; ?>
          </table>
          <div class="checkout-total">Total: <?php echo number_format($total, 0, ',', '.'); ?>đ</div>
          <button type="submit" class="placeorder-btn">Place Order</button>
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
            <a href="https://facebook.com/" target="_blank" title="Facebook" aria-label="Facebook">
              <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/facebook.svg" alt="Facebook" class="footer-icon">
            </a>
            <a href="https://zalo.me/" target="_blank" title="Zalo" aria-label="Zalo">
              <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/zalo.svg" alt="Zalo" class="footer-icon">
            </a>
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
