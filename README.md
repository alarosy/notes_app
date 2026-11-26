# دفتر الملاحظات الشخصي

مشروع مقرر برمجة الويب: موقع لتسجيل الملاحظات اليومية وتصنيفها، مبني بـ PHP (دوال mysqli الإجرائية) وMySQL بجدول واحد.

إعداد الطالب: [اسم الطالب]

## المتطلبات

- PHP 8.0 أو أحدث، مع الإضافتين `mysqli` و`mbstring`
- MySQL 8 أو MariaDB 10.4 أو أحدث
- Apache (أو XAMPP على Windows)

## التشغيل

1. انسخ مجلد `notes_app` إلى مجلد الويب (`/var/www/html` على Ubuntu، أو `C:\xampp\htdocs` في XAMPP).
2. أنشئ قاعدة البيانات بترميز utf8mb4 ثم استورد الجدول والبيانات التجريبية:

   ```
   mysql -u student -p -e "CREATE DATABASE IF NOT EXISTS notes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u student -p notes_db < database.sql
   ```

   أو من phpMyAdmin: أنشئ القاعدة `notes_db` ثم استخدم تبويب "استيراد" واختر `database.sql`.
3. عدّل بيانات الاتصال في أعلى `config.php` إذا كانت مختلفة عن الافتراضية (`student` / `Student@2026`). في XAMPP غالبًا تكون `root` بدون كلمة مرور.
4. افتح المتصفح على: `http://localhost/notes_app`

## الملفات

| الملف | الدور |
|---|---|
| `config.php` | الاتصال بقاعدة البيانات، قائمة التصنيفات، دوال مساعدة |
| `index.php` | الإضافة، والعرض، والبحث، والفلترة |
| `edit.php` | تعديل ملاحظة |
| `delete.php` | حذف ملاحظة (POST فقط) |
| `style.css` | التنسيق |
| `database.sql` | إنشاء الجدول والبيانات التجريبية |

شرح تفصيلي للكود وأسئلة المناقشة موجود في `دليل_المناقشة.md`.
