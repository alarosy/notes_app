-- قاعدة بيانات دفتر الملاحظات
-- الاستيراد: mysql -u student -p notes_db < database.sql

SET NAMES utf8mb4;

DROP TABLE IF EXISTS notes;

CREATE TABLE notes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(150) NOT NULL,
    content    TEXT NOT NULL,
    category   VARCHAR(30) NOT NULL DEFAULT 'أخرى',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- بيانات تجريبية
INSERT INTO notes (title, content, category, created_at) VALUES
('مراجعة محاضرة قواعد البيانات', 'مراجعة الفرق بين INNER JOIN و LEFT JOIN، وحل تمارين الفصل الرابع قبل يوم الخميس.', 'دراسة', '2026-11-18 09:30:00'),
('موعد طبيب الأسنان', 'الموعد يوم الأحد الساعة الرابعة عصرًا، لا تنسَ إحضار بطاقة التأمين.', 'شخصي', '2026-11-19 14:10:00'),
('فكرة مشروع التخرج', 'تطبيق لحجز قاعات المذاكرة في المكتبة الجامعية، مع إشعار قبل انتهاء الحجز بعشر دقائق.', 'أفكار', '2026-11-20 22:45:00'),
('اجتماع فريق العمل الأسبوعي', 'مناقشة توزيع المهام في المشروع الجماعي وتحديد موعد تسليم المسودة الأولى.', 'عمل', '2026-11-22 11:00:00'),
('قائمة مشتريات', 'دفتر محاضرات، أقلام تظليل، فلاش ميموري 32 جيجا، وشاحن للحاسوب.', 'أخرى', '2026-11-23 18:20:00'),
('ملخص فصل البرمجة الكائنية', 'الوراثة تسمح للكلاس الابن باستخدام خصائص الكلاس الأب، والتغليف يعني إخفاء التفاصيل الداخلية.', 'دراسة', '2026-11-25 20:05:00');
