<?php
require 'config.php';

// المعرّف يأتي من الرابط عند العرض، ومن حقل مخفي عند الحفظ
$source = ($_SERVER['REQUEST_METHOD'] === 'POST') ? $_POST : $_GET;
$id = filter_var(input_text($source, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$note = null;
if ($id !== false) {
    $stmt = mysqli_prepare($conn, 'SELECT id, title, content, category FROM notes WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $note = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

$errors = [];

if ($note && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // نضع القيم الجديدة مكان القديمة حتى تبقى في النموذج إذا ظهر خطأ
    $note['title']    = input_text($_POST, 'title');
    $note['content']  = input_text($_POST, 'content');
    $note['category'] = input_text($_POST, 'category');

    $errors = validate_note($note['title'], $note['content'], $note['category'], $categories);

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'UPDATE notes SET title = ?, content = ?, category = ? WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'sssi', $note['title'], $note['content'], $note['category'], $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header('Location: index.php?msg=updated');
        exit;
    }
}

if (!$note) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل ملاحظة - دفتر الملاحظات</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <h1><a href="index.php">دفتر الملاحظات</a></h1>
        <p>تعديل ملاحظة</p>
    </div>
</header>

<main class="container">
    <?php if (!$note): ?>
        <div class="box empty-state">
            <p>الملاحظة المطلوبة غير موجودة، ربما حُذفت أو أن الرابط غير صحيح.</p>
            <a class="btn" href="index.php">العودة إلى الصفحة الرئيسية</a>
        </div>
    <?php else: ?>
        <section class="box">
            <h2>تعديل: <?= e($note['title']) ?></h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="edit.php">
                <input type="hidden" name="id" value="<?= (int) $note['id'] ?>">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="title">العنوان</label>
                        <input type="text" id="title" name="title" maxlength="<?= TITLE_MAX ?>"
                               value="<?= e($note['title']) ?>" required>
                    </div>
                    <div class="form-row">
                        <label for="category">التصنيف</label>
                        <select id="category" name="category">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= $cat === $note['category'] ? 'selected' : '' ?>>
                                    <?= e($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <label for="content">نص الملاحظة</label>
                    <textarea id="content" name="content" maxlength="<?= CONTENT_MAX ?>" required><?= e($note['content']) ?></textarea>
                </div>
                <button type="submit" class="btn">حفظ التعديلات</button>
                <a class="btn btn-light" href="index.php">إلغاء</a>
            </form>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
