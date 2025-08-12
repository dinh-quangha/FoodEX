<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../index.php');
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asm2";
$connect = mysqli_connect($servername, $username, $password, $dbname);

// Cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $delivery = $_POST['delivery'];
    $stmt = mysqli_prepare($connect, "UPDATE orders SET Status=?, DeliveryStatus=? WHERE OrderID=?");
    mysqli_stmt_bind_param($stmt, "ssi", $status, $delivery, $order_id);
    mysqli_stmt_execute($stmt);
}
// Lấy danh sách đơn hàng
$sql = "SELECT o.*, u.Name as UserName FROM orders o JOIN users u ON o.UserID = u.UserID ORDER BY o.OrderDate DESC";
$result = mysqli_query($connect, $sql);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý đơn hàng - Admin</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    body { background: #f7f7f7; }
    .stat-main { display: flex; }
    .stat-sidebar {
      width: 230px;
      background: linear-gradient(160deg, #4e54c8 0%, #00c3ff 50%, #00ff99 100%);
      min-height: 100vh;
      color: #fff;
      padding-top: 30px;
      border-top-right-radius: 24px;
      border-bottom-right-radius: 24px;
      box-shadow: 4px 0 24px 0 rgba(0,0,0,0.12);
      position: relative;
      z-index: 2;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-sidebar .brand {
      text-align: center;
      margin-bottom: 28px;
      font-weight: bold;
      font-size: 26px;
      color: #39ff14;
      letter-spacing: 2px;
      text-shadow: 0 0 8px #39ff14, 0 0 2px #fff;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-menu { list-style: none; padding: 0; margin: 0; }
    .stat-menu li { margin: 18px 0; }
    .stat-menu a {
      color: #fff;
      text-decoration: none;
      font-size: 19px;
      padding: 13px 32px;
      display: block;
      border-radius: 12px 0 0 12px;
      border-left: 5px solid transparent;
      transition: background 0.18s, border-color 0.18s, color 0.18s, box-shadow 0.18s;
      font-weight: 500;
      box-shadow: none;
      font-family: 'Segoe UI', Arial, sans-serif;
    }
    .stat-menu a.active, .stat-menu a:hover {
      background: rgba(255,255,255,0.18);
      border-left: 5px solid #ffe082;
      color: #ffe082;
      box-shadow: 0 2px 16px 0 rgba(0,255,153,0.12);
      font-weight: bold;
      text-shadow: 0 0 6px #fff, 0 0 2px #ffe082;
    }
    .stat-content { flex: 1; padding: 40px 50px; }
    @media (max-width: 900px) {
      .stat-main { flex-direction: column; }
      .stat-sidebar { width: 100%; min-height: unset; }
      .stat-content { padding: 20px 5vw; }
    }
    .orders-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .orders-table th, .orders-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
    .orders-table th { background: #00b14f; color: #fff; }
    .orders-table tr:hover { background: #f6fff8; }
    .update-form select { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }
    .update-form button { background: #00b14f; color: #fff; border: none; padding: 7px 16px; border-radius: 5px; cursor: pointer; font-size: 15px; margin-left: 6px; }
    .update-form button:hover { background: #008f3a; }
    .view-btn { background: #00b14f; color: #fff; border: none; padding: 7px 18px; border-radius: 5px; cursor: pointer; font-size: 15px; }
    .view-btn:hover { background: #008f3a; }
  </style>
</head>
<body>
<div class="stat-main">
  <aside class="stat-sidebar">
    <img src="../logo-grabfood2.svg" alt="FoodX Delivery" style="display:block;margin:0 auto 10px auto;width:54px;">
    <div class="brand">FoodX Delivery</div>
    <ul class="stat-menu">
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="manage_restaurants.php">Quản lý nhà hàng</a></li>
      <li><a href="manage_dishes.php">Quản lý món ăn</a></li>
      <li><a href="manage_orders.php" class="active">Quản lý đơn hàng</a></li>
      <li><a href="manage_users.php">Quản lý người dùng</a></li>
      <li><a href="statistic.php">Thống kê</a></li>
      <li><a href="../logout.php">Đăng xuất</a></li>
    </ul>
  </aside>
  <section class="stat-content">
    <h2 style="margin-top:40px; text-align:center;">Quản lý đơn hàng</h2>
    <table class="orders-table">
      <tr>
        <th>Order ID</th>
        <th>User</th>
        <th>Date</th>
        <th>Status</th>
        <th>Delivery</th>
        <th>Total</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($orders as $order): ?>
      <tr>
        <td><?php echo $order['OrderID']; ?></td>
        <td><?php echo htmlspecialchars($order['UserName']); ?></td>
        <td><?php echo $order['OrderDate']; ?></td>
        <td>
          <form method="post" class="update-form">
            <input type="hidden" name="order_id" value="<?php echo $order['OrderID']; ?>">
            <select name="status">
              <option value="Pending" <?php if($order['Status']==='Pending') echo 'selected'; ?>>Pending</option>
              <option value="Confirmed" <?php if($order['Status']==='Confirmed') echo 'selected'; ?>>Confirmed</option>
              <option value="Cancelled" <?php if($order['Status']==='Cancelled') echo 'selected'; ?>>Cancelled</option>
              <option value="Completed" <?php if($order['Status']==='Completed') echo 'selected'; ?>>Completed</option>
            </select>
        </td>
        <td>
            <select name="delivery">
              <option value="Processing" <?php if($order['DeliveryStatus']==='Processing') echo 'selected'; ?>>Processing</option>
              <option value="Delivering" <?php if($order['DeliveryStatus']==='Delivering') echo 'selected'; ?>>Delivering</option>
              <option value="Delivered" <?php if($order['DeliveryStatus']==='Delivered') echo 'selected'; ?>>Delivered</option>
              <option value="Failed" <?php if($order['DeliveryStatus']==='Failed') echo 'selected'; ?>>Failed</option>
            </select>
            <button type="submit" name="update_status">Update</button>
          </form>
        </td>
        <td><?php echo number_format($order['TotalAmount'], 0, ',', '.'); ?>đ</td>
        <td><a href="../order_detail.php?id=<?php echo $order['OrderID']; ?>" class="view-btn">View</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </section>
</div>
</body>
</html> 