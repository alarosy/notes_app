<?php
require 'config.php';

// الحذف يغيّر البيانات، لذلك نقبله عبر POST فقط وليس من رابط عادي
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    die('هذه الصفحة تقبل طلبات POST فقط.');
}

$id = filter_var(input_text($_POST, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false) {
    header('Location: index.php?msg=notfound');
    exit;
}

$stmt = mysqli_prepare($conn, 'DELETE FROM notes WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$deleted = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

// إذا لم يُحذف أي سطر فالمعرّف غير موجود أصلًا
header('Location: index.php?msg=' . ($deleted > 0 ? 'deleted' : 'notfound'));
exit;
