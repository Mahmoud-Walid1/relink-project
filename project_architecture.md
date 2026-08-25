# خريطة البنية المعمارية للمشروع (Project Architecture Context Index)

## نظرة عامة (Overview)
موقع إعادة توجيه الروابط وتنظيم الإنتاج المعرفي بروابط ديناميكية ثابتة مع لوحة تحكم شجرية للمجلدات والروابط.
يعتمد المشروع على أسلوب **Clean Architecture** بملفات صغيرة مركزية وحيدة المسؤولية (Single Responsibility Principles).

---

## هيكل المجلدات والملفات (Folder & File Structure)

### 1. الإعدادات والتهيئات (`config/`)
- `config/app.php`: إعدادات التقييد، المسارات الرئيسية، الجلسات ومفاتيح التشفير.
- `config/database.php`: إدارة الاتصال بقاعدة البيانات (SQLite / MySQL) باستخدام PDO.

### 2. قاعدة البيانات (`database/`)
- `database/schema.sql`: الهيكل الأولي للجداول (Folders, Links, Clicks, Admins).
- `database/database.sqlite`: ملف قاعدة البيانات المحلي المستقل (تلقائي الإنشاء بدون ضبط تعقيدات سيرفر).

### 3. البرمجيات والمنطق البرمجي (`src/`)

#### أ. النماذج والبيانات (`src/Models/`)
- `src/Models/Folder.php`: عمليات المجلدات (إنشاء، تعديل، حذف، جلب المجلدات الأب والأبناء).
- `src/Models/Link.php`: عمليات الروابط (إنشاء، تعديل، تغيير الوجهة، جلب الروابط حسب المجلد).
- `src/Models/ClickLog.php`: تسجيل قراءات الإحصائيات (عدد الضغطات، تاريخ وتوقيت آخر زيارة).
- `src/Models/User.php`: التحقق من الحساب وكلمة مرور المدير.

#### ب. المتحكمات (`src/Controllers/`)
- `src/Controllers/RedirectController.php`: معالجة الروابط الموجهة، التحقق من حالة الرابط (مفعل/معطل) والتوجيه المؤقت HTTP 302.
- `src/Controllers/AdminController.php`: عرض لوحة التحكم الرئيسية وتنسيق الصفحة.
- `src/Controllers/FolderApiController.php`: واجهة API لإدارة عمليات المجلدات عبر AJAX.
- `src/Controllers/LinkApiController.php`: واجهة API لإدارة الروابط والخيارات المتقدمة (تفعيل/تعطيل، إحصائيات).
- `src/Controllers/AuthController.php`: تسجيل الدخول والخروج للوحة التحكم.

#### ج. الخدمات والوظائف المساندة (`src/Services/`)
- `src/Services/SlugService.php`: تحويل أسماء المجلدات والروابط إلى مسارات clean URLs صحيحة وحل المسارات المتداخلة.
- `src/Services/AuthService.php`: إدارة جلسات تسجيل الدخول وحماية المسارات.
- `src/Services/AnalyticsService.php`: معالجة وتسجيل الإحصائيات عند الطلب.
- `src/Services/QrCodeService.php`: خدمة إعداد وإنشاء رموز QR Code للروابط عند طلبها.

#### د. الأدوات المساعدة (`src/Helpers/`)
- `src/Helpers/ResponseHelper.php`: إرجاع استجابات JSON منسقة للواجهة الأمامية.
- `src/Helpers/RequestHelper.php`: فحص وتنظيف مدخلات المستخدم ومنع ثغرات XSS/Injection.

### 4. الواجهة الأمامية والموجه التشغيلي (`public/`)
- `public/index.php`: الموجه الرئيسي (Front Controller / Router) لتحويل مسارات الروابط والموجهات.
- `public/assets/css/style.css`: التصميم البصري الفاخر (Dark Theme, Glassmorphism, Responsive UI).
- `public/assets/js/app.js`: وحدة إدارة لوحة التحكم والبحث المباشر والتنقل الشجري.
- `public/assets/js/modal.js`: إدارة النوافذ المنبثقة والقوائم المنسدلة (Context Menu / Modals).
- `public/assets/js/qrcode.min.js`: مكتبة إنشاء الرمز التعبيري QR Code محلياً عند الطلب.
