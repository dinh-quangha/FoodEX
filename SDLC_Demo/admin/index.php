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
  <title>Admin Dashboard - FoodX Delivery</title>
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
      .stat-sidebar { width: 100%; min-height: unset; border-radius: 0 0 24px 24px; }
      .stat-content { padding: 20px 5vw; }
    }
    .dashboard-center {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 80vh;
    }
    .dashboard-title {
      color: #00b14f;
      font-size: 2.4rem;
      font-weight: bold;
      margin-bottom: 38px;
      letter-spacing: 1px;
      text-align: center;
      text-shadow: 0 2px 12px #00ff9955;
    }
    .dashboard-links {
      display: flex;
      flex-wrap: wrap;
      gap: 32px;
      justify-content: center;
    }
    .dashboard-card {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #00c3ff 0%, #00ff99 100%);
      color: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10);
      padding: 38px 44px;
      min-width: 210px;
      min-height: 140px;
      font-size: 1.25rem;
      font-weight: 600;
      text-decoration: none;
      transition: transform 0.18s, box-shadow 0.18s, background 0.18s;
      position: relative;
    }
    .dashboard-card:hover {
      transform: translateY(-6px) scale(1.04);
      box-shadow: 0 8px 32px 0 rgba(0,255,153,0.18);
      background: linear-gradient(135deg, #00ff99 0%, #00c3ff 100%);
      color: #ffe082;
    }
    .dashboard-icon {
      font-size: 2.5rem;
      margin-bottom: 16px;
      filter: drop-shadow(0 2px 8px #fff8);
    }
    @media (max-width: 900px) {
      .dashboard-links { flex-direction: column; gap: 22px; }
      .dashboard-card { min-width: 180px; padding: 28px 18px; font-size: 1.1rem; }
    }
    .dashboard-card.card-green {
      background: linear-gradient(135deg, #00ff99 0%, #00c3ff 100%);
    }
    .dashboard-card.card-blue {
      background: linear-gradient(135deg, #00c3ff 0%, #4e54c8 100%);
    }
    .dashboard-card.card-cyan {
      background: linear-gradient(135deg, #00c3ff 0%, #39ff14 100%);
    }
    .dashboard-card.card-yellow {
      background: linear-gradient(135deg, #ffe082 0%, #00ff99 100%);
      color: #333;
    }
    .dashboard-card.card-yellow:hover {
      color: #00c3ff;
    }
  </style>
</head>
<body>
<div class="stat-main">
  <aside class="stat-sidebar">
    <img src="../logo-grabfood2.svg" alt="FoodX Delivery" style="display:block;margin:0 auto 10px auto;width:54px;">
    <div class="brand">FoodX Delivery</div>
    <ul class="stat-menu">
      <li><a href="index.php" class="active">Dashboard</a></li>
      <li><a href="manage_restaurants.php">Quản lý nhà hàng</a></li>

      <li><a href="manage_dishes.php">Quản lý món ăn</a></li>
      <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
      <li><a href="manage_users.php">Quản lý người dùng</a></li>
      <li><a href="statistic.php">Thống kê</a></li>
      <li><a href="../logout.php">Đăng xuất</a></li>
    </ul>
  </aside>
  <section class="stat-content">
    <div class="dashboard-center">
      <h1 class="dashboard-title">FoodX Delivery Admin Dashboard</h1>
      <div class="dashboard-links">
        <a href="manage_dishes.php" class="dashboard-card card-green">
          <span class="dashboard-icon">🍔</span>
          <span>Quản lý món ăn</span>
        </a>
        <a href="manage_orders.php" class="dashboard-card card-blue">
          <span class="dashboard-icon">🧾</span>
          <span>Quản lý đơn hàng</span>
        </a>
        <a href="manage_users.php" class="dashboard-card card-cyan">
          <span class="dashboard-icon">👤</span>
          <span>Quản lý người dùng</span>
        </a>
        <a href="statistic.php" class="dashboard-card card-yellow">
          <span class="dashboard-icon">📊</span>
          <span>Thống kê</span>
        </a>
        <!-- Thêm mục Quản lý nhà hàng -->
        <a href="manage_restaurants.php" class="dashboard-card card-orange">
          <span class="dashboard-icon">🏢</span>
          <span>Quản lý nhà hàng</span>
        </a>
      </div>
    </div>
  </section>
</div>
</body>
</html> 