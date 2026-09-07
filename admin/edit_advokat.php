<?php
require_once __DIR__ . '/partials/admin_auth.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: data_advokat.php');
    exit;
}

$d = null;
if ($st = mysqli_prepare($conn, 'SELECT * FROM data_advokat WHERE id_advokat = ? LIMIT 1')) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    $d = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
}
if (!$d) {
    header('Location: data_advokat.php?pesan=gagal');
    exit;
}

/* jika ada error dari proses_edit, timpa dengan input sebelumnya */
if (!empty($_SESSION['adv_errors'])) {
    $errors = $_SESSION['adv_errors'];
    $d = array_merge($d, $_SESSION['adv_old'] ?? []);
    unset($_SESSION['adv_errors'], $_SESSION['adv_old']);
} else {
    $errors = [];
}

$mode   = 'edit';
$action = 'proses_edit.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data Advokat - PERADI Pontianak</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/partials/form_advokat.php'; ?>
</body>
</html>
