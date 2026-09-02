<?php
if (ob_get_level() == 0) ob_start();
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/permissions.php';
requireLogin();

$currentUser = getCurrentUser();
$roleStr = '';
if ($currentUser['role'] === 'admin') $roleStr = 'ผู้ดูแลระบบ (Admin)';
else if ($currentUser['role'] === 'teacher') $roleStr = 'ครูที่ปรึกษา (Teacher)';
else if ($currentUser['role'] === 'student') $roleStr = 'นักศึกษา (Student)';

// Determine unread notification count
$db = getDB();
$unreadStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
$unreadStmt->execute([':uid' => $currentUser['id']]);
$unreadCount = $unreadStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Leaflet CSS for Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= $basePath ?? '' ?>assets/css/style.css?v=<?= time() ?>">
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar Backdrop for Mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
