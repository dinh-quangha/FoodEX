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
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($order_id <= 0) {
    echo 'Invalid order ID.';
    exit();
}
// Lấy userID
$user_query = mysqli_query($connect, "SELECT UserID FROM users WHERE Username='" . mysqli_real_escape_string($connect, $_SESSION['user']) . "'");
$user_row = mysqli_fetch_assoc($user_query);
$user_id = $user_row['UserID'];
// Lấy thông tin đơn hàng
$order_sql = "SELECT * FROM orders WHERE OrderID = $order_id AND UserID = $user_id";
$order_result = mysqli_query($connect, $order_sql);
$order = mysqli_fetch_assoc($order_result);
if (!$order) {
    echo 'Order not found.';
    exit();
}
// Lấy chi tiết món ăn trong đơn
$detail_sql = "SELECT od.*, d.Name, d.Image FROM orderdetail od JOIN dish d ON od.DishID = d.DishID WHERE od.OrderID = $order_id";
$detail_result = mysqli_query($connect, $detail_sql);
$details = [];
while ($row = mysqli_fetch_assoc($detail_result)) {
    $details[] = $row;
}
// Lấy feedback cho từng món
$feedbacks = [];
$fb_sql = "SELECT * FROM feedback WHERE OrderDetailID IN (SELECT OrderDetailID FROM orderdetail WHERE OrderID = $order_id)";
$fb_result = mysqli_query($connect, $fb_sql);
while ($fb = mysqli_fetch_assoc($fb_result)) {
    $feedbacks[$fb['OrderDetailID']] = $fb;
}
// Xử lý gửi feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_detail_id'], $_POST['content'])) {
    $odid = intval($_POST['order_detail_id']);
    $content = trim($_POST['content']);
    if ($content !== '' && !isset($feedbacks[$odid])) {
        $stmt = mysqli_prepare($connect, "INSERT INTO feedback (OrderDetailID, Content, CreatedAt) VALUES (?, ?, NOW())");
        mysqli_stmt_bind_param($stmt, "is", $odid, $content);
        mysqli_stmt_execute($stmt);
        header("Location: order_detail.php?id=$order_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Details - FoodX Delivery</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .detail-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .detail-table th, .detail-table td { padding: 14px; border-bottom: 1px solid #eee; text-align: center; }
    .detail-table th { background: #00b14f; color: #fff; }
    .detail-img { width: 80px; border-radius: 8px; }
    .feedback-form textarea { width: 90%; min-height: 50px; border-radius: 6px; border: 1px solid #ccc; padding: 8px; }
    .feedback-form button { background: #00b14f; color: #fff; border: none; padding: 7px 18px; border-radius: 5px; cursor: pointer; font-size: 15px; margin-top: 6px; }
    .feedback-form button:hover { background: #008f3a; }
    .feedback-content { color: #008f3a; font-style: italic; }
  </style>
</head>
<body>
  <header>
    <div class="container header-flex">
      <img src="logo-grabfood2.svg" alt="Logo" class="logo">
      <nav>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="orders.php">Your Orders</a></li>
          <li><span>Welcome, <b><?php echo htmlspecialchars($_SESSION['user']); ?></b></span></li>
          <li><a href="logout.php">Log out</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main>
    <h2 style="margin-top:40px; text-align:center;">Order #<?php echo $order_id; ?> Details</h2>
    <div style="max-width:900px;margin:0 auto;">
      <p><b>Date:</b> <?php echo $order['OrderDate']; ?> | <b>Status:</b> <?php echo htmlspecialchars($order['Status']); ?> | <b>Delivery:</b> <?php echo htmlspecialchars($order['DeliveryStatus']); ?> | <b>Total:</b> <?php echo number_format($order['TotalAmount'], 0, ',', '.'); ?>đ</p>
      <table class="detail-table">
        <tr>
          <th>Image</th>
          <th>Dish</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
          <th>Feedback</th>
        </tr>
        <?php foreach ($details as $item): ?>
        <tr>
          <td><img src="<?php echo htmlspecialchars($item['Image']); ?>" class="detail-img"></td>
          <td><?php echo htmlspecialchars($item['Name']); ?></td>
          <td><?php echo number_format($item['Price'], 0, ',', '.'); ?>đ</td>
          <td><?php echo $item['Quantity']; ?></td>
          <td><?php echo number_format($item['Price'] * $item['Quantity'], 0, ',', '.'); ?>đ</td>
          <td>
            <?php if ($order['DeliveryStatus'] === 'Delivered'): ?>
              <?php if (isset($feedbacks[$item['OrderDetailID']])): ?>
                <div class="feedback-content">Feedback: <?php echo htmlspecialchars($feedbacks[$item['OrderDetailID']]['Content']); ?></div>
              <?php else: ?>
                <form method="post" class="feedback-form">
                  <input type="hidden" name="order_detail_id" value="<?php echo $item['OrderDetailID']; ?>">
                  <textarea name="content" required placeholder="Write your feedback..."></textarea><br>
                  <button type="submit">Send</button>
                </form>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:#888;">Feedback available after delivery</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <a href="orders.php" style="display:inline-block;margin-top:20px;color:#00b14f;font-weight:600;">&larr; Back to Orders</a>
    </div>
  </main>
</body>
</html> 