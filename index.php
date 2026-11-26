<?php
require 'config.php';

$errors = [];
$old = ['title' => '', 'content' => '', 'category' => 'أخرى'];

// معالجة نموذج الإضافة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['title']    = input_text($_POST, 'title');
    $old['content']  = input_text($_POST, 'content');
    $old['category'] = input_text($_POST, 'category');

    $errors = validate_note($old['title'], $old['content'], $old['category'], $categories);

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO notes (title, content, category) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sss', $old['title'], $old['content'], $old['category']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // إعادة التوجيه (PRG) تمنع تكرار الإضافة إذا حدّث المستخدم الصفحة
        header('Location: index.php?msg=added');
        exit;
    }
}

// رسائل النجاح تأتي من الرابط بعد إعادة التوجيه
$messages = [
    'added'    => 'تمت إضافة الملاحظة بنجاح.',
    'updated'  => 'تم حفظ التعديلات بنجاح.',
    'deleted'  => 'تم حذف الملاحظة.',
    'notfound' => 'الملاحظة المطلوبة غير موجودة.',
];
$msg = input_text($_GET, 'msg');

// البحث في العناوين وفلتر التصنيف (يمكن استخدامهما معًا)
$search = input_text($_GET, 'q');
$filter_category = input_text($_GET, 'category');
if (!in_array($filter_category, $categories, true)) {
    $filter_category = '';
}

$sql = 'SELECT id, title, content, category, created_at, updated_at FROM notes';
$conditions = [];
$types = '';
$params = [];

if ($search !== '') {
    $conditions[] = 'title LIKE ?';
    // نهرّب % و _ حتى يُبحث عنهما كحروف عادية
    $params[] = '%' . addcslashes($search, '%_\\') . '%';
    $types .= 's';
}
if ($filter_category !== '') {
    $conditions[] = 'category = ?';
    $params[] = $filter_category;
    $types .= 's';
}
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY created_at DESC, id DESC';

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$notes = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$is_filtered = ($search !== '' || $filter_category !== '');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دفتر الملاحظات</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <h1><a href="index.php">دفتر الملاحظات</a></h1>
        <p>سجّل ملاحظاتك اليومية وصنّفها وارجع إليها بسهولة</p>
    </div>
</header>

<main class="container">

    <?php if (isset($messages[$msg])): ?>
        <div class="alert <?= $msg === 'notfound' ? 'alert-error' : 'alert-success' ?>">
            <?= e($messages[$msg]) ?>
        </div>
    <?php endif; ?>

    <section class="box">
        <h2>إضافة ملاحظة جديدة</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php">
            <div class="form-grid">
                <div class="form-row">
                    <label for="title">العنوان</label>
                    <input type="text" id="title" name="title" maxlength="<?= TITLE_MAX ?>"
                           value="<?= e($old['title']) ?>" required>
                </div>
                <div class="form-row">
                    <label for="category">التصنيف</label>
                    <select id="category" name="category">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $cat === $old['category'] ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <label for="content">نص الملاحظة</label>
                <textarea id="content" name="content" maxlength="<?= CONTENT_MAX ?>" required><?= e($old['content']) ?></textarea>
            </div>
            <button type="submit" class="btn">حفظ الملاحظة</button>
        </form>
    </section>

    <section class="box">
        <form method="get" action="index.php" class="search-bar">
            <input type="search" name="q" placeholder="ابحث في عناوين الملاحظات..."
                   value="<?= e($search) ?>" aria-label="البحث في العناوين">
            <select name="category" aria-label="التصنيف">
                <option value="">كل التصنيفات</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $cat === $filter_category ? 'selected' : '' ?>>
                        <?= e($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">بحث</button>
            <?php if ($is_filtered): ?>
                <a href="index.php" class="btn btn-light">مسح البحث</a>
            <?php endif; ?>
        </form>
    </section>

    <section>
        <div class="list-header">
            <h2>ملاحظاتي</h2>
            <span class="counter">عدد الملاحظات: <?= count($notes) ?></span>
        </div>

        <?php if (empty($notes) && $is_filtered): ?>
            <div class="box empty-state">
                لا توجد ملاحظات مطابقة للبحث. جرّب كلمة أخرى أو اختر "كل التصنيفات".
            </div>
        <?php elseif (empty($notes)): ?>
            <div class="box empty-state">
                لا توجد ملاحظات بعد. ابدأ بإضافة أول ملاحظة من النموذج أعلاه.
            </div>
        <?php else: ?>
            <div class="cards">
                <?php foreach ($notes as $note): ?>
                    <article class="card">
                        <div class="card-head">
                            <h3><?= e($note['title']) ?></h3>
                            <span class="badge <?= $category_classes[$note['category']] ?? 'cat-other' ?>">
                                <?= e($note['category']) ?>
                            </span>
                        </div>
                        <p class="card-text"><?= e(excerpt($note['content'])) ?></p>
                        <div class="card-foot">
                            <span>
                                <?= e(format_date($note['created_at'])) ?>
                                <?php if ($note['updated_at']): ?>
                                    <span class="muted">(معدّلة)</span>
                                <?php endif; ?>
                            </span>
                            <div class="actions">
                                <a class="btn btn-light btn-small" href="edit.php?id=<?= (int) $note['id'] ?>">تعديل</a>
                                <form method="post" action="delete.php"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الملاحظة؟');">
                                    <input type="hidden" name="id" value="<?= (int) $note['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-small">حذف</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
