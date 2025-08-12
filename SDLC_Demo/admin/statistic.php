<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thống kê - Admin Dashboard | FoodX Delivery</title>
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
    .stat-cards { display: flex; gap: 22px; margin-bottom: 30px; flex-wrap: wrap; }
    .stat-card {
      flex: 1 1 200px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07);
      padding: 28px 22px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      min-width: 200px;
      min-height: 110px;
      position: relative;
    }
    .stat-card .stat-label { font-size: 15px; color: #888; margin-bottom: 8px; }
    .stat-card .stat-value { font-size: 28px; font-weight: bold; color: #00b14f; }
    .stat-card .stat-btn {
      margin-top: 12px;
      background: #00b14f;
      color: #fff;
      border: none;
      padding: 7px 18px;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
    }
    .stat-card.yellow { background: #fffbe7; border-left: 6px solid #ffe082; }
    .stat-card.blue { background: #e7f3ff; border-left: 6px solid #42a5f5; }
    .stat-card.purple { background: #ede7f6; border-left: 6px solid #7e57c2; }
    .stat-card.green { background: #e8f5e9; border-left: 6px solid #00b14f; }
    .stat-card.red { background: #ffebee; border-left: 6px solid #ff424e; }
    .stat-section-title { font-size: 20px; color: #00b14f; margin: 30px 0 18px 0; font-weight: 600; }
    .stat-flex { display: flex; gap: 30px; flex-wrap: wrap; }
    .stat-pie { width: 320px; height: 320px; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); display: flex; align-items: center; justify-content: center; }
    .stat-table-wrap { flex: 1; min-width: 320px; }
    .stat-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); overflow: hidden; }
    .stat-table th, .stat-table td { padding: 14px; border-bottom: 1px solid #eee; text-align: left; }
    .stat-table th { background: #00b14f; color: #fff; }
    .stat-table tr:last-child td { border-bottom: none; }
    @media (max-width: 900px) {
      .stat-main { flex-direction: column; }
      .stat-sidebar { width: 100%; min-height: unset; }
      .stat-content { padding: 20px 5vw; }
      .stat-cards { flex-direction: column; gap: 14px; }
      .stat-flex { flex-direction: column; gap: 18px; }
      .stat-pie { width: 100%; height: 220px; }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
      <li><a href="manage_users.php">Quản lý người dùng</a></li>
      <li><a href="statistic.php" class="active">Thống kê</a></li>
      <li><a href="../logout.php">Đăng xuất</a></li>
    </ul>
  </aside>
  <section class="stat-content">
    <h1 style="color:#00b14f; font-size:2rem; margin-bottom:18px;">Thống kê tổng quan</h1>
    <div class="stat-cards">
      <div class="stat-card">
        <div class="stat-label">Tổng doanh thu</div>
        <div class="stat-value">69.927.500 đ</div>
        <button class="stat-btn">Xem chi tiết</button>
      </div>
      <div class="stat-card yellow">
        <div class="stat-label">Đơn hàng chờ xử lý</div>
        <div class="stat-value">8</div>
        <button class="stat-btn">Chi tiết</button>
      </div>
      <div class="stat-card blue">
        <div class="stat-label">Đơn hàng mới hôm nay</div>
        <div class="stat-value">75</div>
        <button class="stat-btn">Chi tiết</button>
      </div>
      <div class="stat-card purple">
        <div class="stat-label">Khách hàng mới hôm nay</div>
        <div class="stat-value">12</div>
        <button class="stat-btn">Chi tiết</button>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Món sắp hết hàng</div>
        <div class="stat-value">3</div>
        <button class="stat-btn">Chi tiết</button>
      </div>
      <div class="stat-card red">
        <div class="stat-label">Món đã hết hàng</div>
        <div class="stat-value">5</div>
        <button class="stat-btn">Chi tiết</button>
      </div>
    </div>
    <div class="stat-section-title">Thống kê doanh thu & đơn hàng</div>
    <div class="stat-flex">
      <div class="stat-pie">
        <canvas id="pieChart"></canvas>
      </div>
      <div class="stat-table-wrap">
        <table class="stat-table">
          <tr><th>Loại</th><th>Tổng cộng</th></tr>
          <tr><td>Tổng doanh thu</td><td>69.927.500 đ</td></tr>
          <tr><td>Đơn hàng thành công</td><td>120</td></tr>
          <tr><td>Đơn hàng bị hủy</td><td>8</td></tr>
          <tr><td>Khách hàng mới</td><td>12</td></tr>
          <tr><td>Món bán chạy nhất</td><td>Bánh mì đặc biệt</td></tr>
          <tr><td>Món bán nhiều nhất</td><td>Pizza hải sản</td></tr>
        </table>
      </div>
    </div>
  </section>
</div>
<script>
const ctx = document.getElementById('pieChart').getContext('2d');
new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['Doanh thu', 'Đơn thành công', 'Đơn hủy', 'Khách mới'],
    datasets: [{
      data: [69927500, 120, 8, 12],
      backgroundColor: ['#00b14f', '#42a5f5', '#ff424e', '#ffe082'],
      borderWidth: 1
    }]
  },
  options: {
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});
</script>
</body>
</html> 