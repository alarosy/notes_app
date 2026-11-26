<?php
// إعدادات الاتصال بقاعدة البيانات
$db_host = 'localhost';
$db_user = 'student';
$db_pass = 'Student@2026';
$db_name = 'notes_db';

// نجعل mysqli يرمي استثناءً عند الخطأ بدل أن يفشل بصمت
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $ex) {
    http_response_code(500);
    die('تعذّر الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL ومن بيانات الاتصال في config.php');
}

// قائمة التصنيفات الثابتة (التصنيف عمود في جدول notes نفسه)
$categories = ['شخصي', 'دراسة', 'عمل', 'أفكار', 'أخرى'];

// كلاس CSS لكل تصنيف حتى تظهر الشارة بلون مختلف
$category_classes = [
    'شخصي'  => 'cat-personal',
    'دراسة' => 'cat-study',
    'عمل'   => 'cat-work',
    'أفكار' => 'cat-ideas',
    'أخرى'  => 'cat-other',
];

define('TITLE_MAX', 150);
define('CONTENT_MAX', 5000);

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// نستخدم دوال mb_ لأن النص عربي (الحرف الواحد أكثر من بايت)
function excerpt($text, $length = 140)
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function validate_note($title, $content, $category, $categories)
{
    $errors = [];

    if ($title === '') {
        $errors[] = 'العنوان مطلوب.';
    } elseif (mb_strlen($title) > TITLE_MAX) {
        $errors[] = 'العنوان يجب ألا يزيد عن ' . TITLE_MAX . ' حرفًا.';
    }

    if ($content === '') {
        $errors[] = 'نص الملاحظة مطلوب.';
    } elseif (mb_strlen($content) > CONTENT_MAX) {
        $errors[] = 'نص الملاحظة يجب ألا يزيد عن ' . CONTENT_MAX . ' حرف.';
    }

    if (!in_array($category, $categories, true)) {
        $errors[] = 'اختر تصنيفًا صحيحًا من القائمة.';
    }

    return $errors;
}

function format_date($datetime)
{
    return date('Y/m/d - H:i', strtotime($datetime));
}
