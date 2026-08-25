<?php

require_once __DIR__ . '/../Services/AuthService.php';

class AdminController {
    public static function render(): void {
        $isLoggedIn = AuthService::isLoggedIn();
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Relink - لوحة التحكم الشجرية</title>
            <link rel="stylesheet" href="/assets/css/style.css">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
            <script src="/assets/js/qrcode.min.js"></script>
        </head>
        <body class="dark-theme">
            <div id="app">
                <?php if (!$isLoggedIn): ?>
                    <!-- Login Form View -->
                    <div class="login-wrapper">
                        <div class="login-card glass-panel">
                            <div class="brand">
                                <div class="brand-icon">🔗</div>
                                <h1>Relink Dashboard</h1>
                                <p>إدارة روابط الإنتاج المعرفي والتوجيه الديناميكي</p>
                            </div>
                            <form id="login-form">
                                <div class="form-group">
                                    <label>اسم المستخدم</label>
                                    <input type="text" id="login-username" required placeholder="admin" autocomplete="username">
                                </div>
                                <div class="form-group">
                                    <label>كلمة المرور</label>
                                    <input type="password" id="login-password" required placeholder="••••••••" autocomplete="current-password">
                                </div>
                                <div id="login-error" class="error-msg"></div>
                                <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Admin Main Interface -->
                    <header class="main-header glass-panel">
                        <div class="header-content">
                            <div class="brand-logo">
                                <span class="logo-icon">🔗</span>
                                <div>
                                    <h1 class="logo-title">Relink</h1>
                                    <span class="logo-sub">إدارة الإنتاج المعرفي</span>
                                </div>
                            </div>

                            <div class="search-bar-wrapper">
                                <span class="search-icon">🔍</span>
                                <input type="text" id="global-search" placeholder="بحث سريع في كافة المجلدات والدروس..." autocomplete="off">
                            </div>

                            <div class="header-actions">
                                <button class="btn btn-success" id="btn-add-root-folder">📁 مجلد جديد</button>
                                <button class="btn btn-primary" id="btn-add-root-link">🔗 رابط جديد</button>
                                <button class="btn btn-danger btn-sm" id="btn-logout">تسجيل الخروج</button>
                            </div>
                        </div>
                    </header>

                    <main class="dashboard-body container">
                        <div class="dashboard-grid">
                            <!-- Left Sidebar: Tree View Navigation -->
                            <aside class="tree-sidebar glass-panel">
                                <div class="sidebar-header">
                                    <h3>الهيكل الشجري</h3>
                                    <button class="btn-icon" id="btn-expand-all" title="توسيع الكل">📂</button>
                                </div>
                                <div id="tree-container" class="tree-root">
                                    <div class="loading-spinner">جاري تحميل البيانات...</div>
                                </div>
                            </aside>

                            <!-- Main Content Area: Active Folder Contents / Details -->
                            <section class="main-content glass-panel">
                                <div class="content-header">
                                    <div class="breadcrumb-container" id="breadcrumb-bar">
                                        <span class="breadcrumb-item active">الرئيسية (كافة العناصر)</span>
                                    </div>
                                    <div id="folder-stats-badge" class="stats-badge"></div>
                                </div>

                                <div class="items-list-container" id="items-list">
                                    <!-- Dynamic content will be injected here -->
                                </div>
                            </section>
                        </div>
                    </main>

                    <!-- Context Menu Overlay -->
                    <div id="context-menu" class="context-menu">
                        <div class="menu-item" id="ctx-qr">📱 توليد رمز QR Code</div>
                        <div class="menu-item" id="ctx-edit">✏️ تعديل</div>
                        <div class="menu-item" id="ctx-settings">⚙️ إعدادات وخيارات متقدمة</div>
                        <div class="menu-divider"></div>
                        <div class="menu-item danger" id="ctx-delete">🗑️ حذف</div>
                    </div>

                    <!-- Modals -->
                    <!-- Folder Modal -->
                    <div id="modal-folder" class="modal-backdrop">
                        <div class="modal-card glass-panel">
                            <h3 id="modal-folder-title">إنشاء مجلد جديد</h3>
                            <form id="form-folder">
                                <input type="hidden" id="folder-id">
                                <div class="form-group">
                                    <label>اسم المجلد (الظاهر)</label>
                                    <input type="text" id="folder-name" required placeholder="مثال: الإنتاج المعرفي أول متوسط">
                                </div>
                                <div class="form-group">
                                    <label>مسار الرابط (Slug اختياري)</label>
                                    <input type="text" id="folder-slug" placeholder="سيتولد تلقائياً إن تركته فارغاً">
                                    <small class="help-text">يستخدم لبناء مسار الرابط النظيف</small>
                                </div>
                                <div class="form-group">
                                    <label>المجلد الأب (الموقع الشجري)</label>
                                    <select id="folder-parent-id">
                                        <option value="">[المستوى الرئيسي - الجذر]</option>
                                    </select>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary modal-close">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">حفظ المجلد</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Link Modal -->
                    <div id="modal-link" class="modal-backdrop">
                        <div class="modal-card glass-panel">
                            <h3 id="modal-link-title">إنشاء رابط جديد</h3>
                            <form id="form-link">
                                <input type="hidden" id="link-id">
                                <div class="form-group">
                                    <label>عنوان الدرس / المحتوى</label>
                                    <input type="text" id="link-title" required placeholder="مثال: درس 1 العلم وعملياته">
                                </div>
                                <div class="form-group">
                                    <label>رابط الوجهة (الرابط الفعلي كالـ Youtube)</label>
                                    <input type="url" id="link-target-url" required placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                                <div class="form-group">
                                    <label>مسار الرابط (Slug اختياري)</label>
                                    <input type="text" id="link-slug" placeholder="سيتولد تلقائياً إن تركته فارغاً">
                                </div>
                                <div class="form-group">
                                    <label>المجلد التابع له</label>
                                    <select id="link-folder-id">
                                        <option value="">[بدون مجلد - الرابط الرئيسي]</option>
                                    </select>
                                </div>
                                <div class="form-group checkbox-group">
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="link-is-active" checked>
                                        <span class="slider"></span>
                                    </label>
                                    <span>الرابط مفعل جاهز للتحويل</span>
                                </div>
                                <div class="form-group checkbox-group">
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="link-track-analytics">
                                        <span class="slider"></span>
                                    </label>
                                    <span>تفعيل حساب وتتبع الضغطات الإحصائية</span>
                                </div>
                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary modal-close">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">حفظ الرابط</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- QR Modal -->
                    <div id="modal-qr" class="modal-backdrop">
                        <div class="modal-card glass-panel text-center">
                            <h3>رمز QR Code للرابط الثابت</h3>
                            <p id="qr-link-title" class="sub-text"></p>
                            <div class="qr-container" id="qr-code-box"></div>
                            <div class="qr-url-box">
                                <input type="text" id="qr-full-url" readonly>
                                <button class="btn btn-secondary btn-sm" id="btn-copy-qr-url">نسخ الرابط</button>
                            </div>
                            <div class="modal-actions justify-center">
                                <button class="btn btn-secondary modal-close">إغلاق</button>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Modal -->
                    <div id="modal-analytics" class="modal-backdrop">
                        <div class="modal-card glass-panel">
                            <h3>سجل إحصائيات الضغطات</h3>
                            <p id="analytics-link-title" class="sub-text"></p>
                            <div id="analytics-stats-summary" class="analytics-summary"></div>
                            <div class="table-scroll">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>التاريخ والوقت</th>
                                            <th>عنوان IP</th>
                                            <th>المتصفح والجهاز</th>
                                        </tr>
                                    </thead>
                                    <tbody id="analytics-table-body">
                                        <tr><td colspan="3">لا توجد زيارات مسجلة بعد</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-actions">
                                <button class="btn btn-secondary modal-close">إغلاق</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <script src="/assets/js/modal.js"></script>
            <script src="/assets/js/app.js"></script>
        </body>
        </html>
        <?php
    }
}
