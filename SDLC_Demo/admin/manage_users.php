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

// Đổi quyền user
if (isset($_POST['change_role'])) {
    $uid = intval($_POST['user_id']);
    $role = intval($_POST['role']);
    if ($uid != 1) { // Không cho đổi quyền user admin gốc
        $stmt = mysqli_prepare($connect, "UPDATE users SET RoleID=? WHERE UserID=?");
        mysqli_stmt_bind_param($stmt, "ii", $role, $uid);
        mysqli_stmt_execute($stmt);
    }
}
// Xóa user
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid != 1) { // Không cho xóa user admin gốc
        mysqli_query($connect, "DELETE FROM users WHERE UserID = $uid");
    }
}
// Lấy danh sách user
$sql = "SELECT u.*, r.Rolename FROM users u JOIN role r ON u.RoleID = r.RoleID ORDER BY u.UserID DESC";
$result = mysqli_query($connect, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng - Admin</title>
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
    .users-table { width: 100%; border-collapse: collapse; margin: 30px 0; }
    .users-table th, .users-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
    .users-table th { background: #00b14f; color: #fff; }
    .users-table tr:hover { background: #f6fff8; }
    .role-form select { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }
    .role-form button { background: #00b14f; color: #fff; border: none; padding: 7px 16px; border-radius: 5px; cursor: pointer; font-size: 15px; margin-left: 6px; }
    .role-form button:hover { background: #008f3a; }
    .delete-btn { background: #ff424e; color: #fff; border: none; padding: 7px 16px; border-radius: 5px; cursor: pointer; font-size: 15px; }
    .delete-btn:hover { opacity: 0.85; }
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
      <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
      <li><a href="manage_users.php" class="active">Quản lý người dùng</a></li>
      <li><a href="statistic.php">Thống kê</a></li>
      <li><a href="../logout.php">Đăng xuất</a></li>
    </ul>
  </aside>
  <section class="stat-content">
    <h2 style="margin-top:40px; text-align:center;">Quản lý người dùng</h2>
    <table class="users-table">
      <tr>
        <th>ID</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
        <th>Username</th>
        <th>Role</th>
        <th>Change Role</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($users as $user): ?>
      <tr>
        <td><?php echo $user['UserID']; ?></td>
        <td><?php echo htmlspecialchars($user['Name']); ?></td>
        <td><?php echo htmlspecialchars($user['Email']); ?></td>
        <td><?php echo htmlspecialchars($user['Phone']); ?></td>
        <td><?php echo htmlspecialchars($user['Address']); ?></td>
        <td><?php echo htmlspecialchars($user['Username']); ?></td>
        <td><?php echo htmlspecialchars($user['Rolename']); ?></td>
        <td>
          <?php if ($user['UserID'] != 3): ?>
          <form method="post" class="role-form">
            <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
            <select name="role">
              <option value="1" <?php if($user['RoleID']==1) echo 'selected'; ?>>Admin</option>
              <option value="2" <?php if($user['RoleID']==2) echo 'selected'; ?>>User</option>
            </select>
            <button type="submit" name="change_role">Update</button>
          </form>
          <?php else: ?>
            <span style="color:#888;">Super Admin</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($user['UserID'] != 3): ?>
            <a href="manage_users.php?delete=<?php echo $user['UserID']; ?>" class="delete-btn" onclick="return confirm('Xóa user này?');">Xóa</a>
          <?php else: ?>
            <span style="color:#888;">---</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </section>
</div>
</body>
</html> 