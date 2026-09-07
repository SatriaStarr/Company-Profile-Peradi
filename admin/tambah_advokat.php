<?php
require_once __DIR__ . '/partials/admin_auth.php';

$errors = $_SESSION['adv_errors'] ?? [];
$d      = $_SESSION['adv_old'] ?? [];
unset($_SESSION['adv_errors'], $_SESSION['adv_old']);

$mode   = 'tambah';
$action = 'proses_tambah.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Data Advokat - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/partials/form_advokat.php'; ?>
</body>
</html>
