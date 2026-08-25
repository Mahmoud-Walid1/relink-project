class RelinkApp {
    constructor() {
        this.folders = [];
        this.links = [];
        this.currentFolderId = null;
        this.selectedItem = null;

        this.init();
    }

    async init() {
        this.bindEvents();
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
            return;
        }

        await this.loadData();
    }

    bindEvents() {
        const btnAddRootFolder = document.getElementById('btn-add-root-folder');
        if (btnAddRootFolder) {
            btnAddRootFolder.addEventListener('click', () => this.openFolderModal());
        }

        const btnAddRootLink = document.getElementById('btn-add-root-link');
        if (btnAddRootLink) {
            btnAddRootLink.addEventListener('click', () => this.openLinkModal());
        }

        const btnLogout = document.getElementById('btn-logout');
        if (btnLogout) {
            btnLogout.addEventListener('click', () => this.handleLogout());
        }

        const searchInput = document.getElementById('global-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }

        const formFolder = document.getElementById('form-folder');
        if (formFolder) {
            formFolder.addEventListener('submit', (e) => this.saveFolder(e));
        }

        const formLink = document.getElementById('form-link');
        if (formLink) {
            formLink.addEventListener('submit', (e) => this.saveLink(e));
        }

        const btnCopyQr = document.getElementById('btn-copy-qr-url');
        if (btnCopyQr) {
            btnCopyQr.addEventListener('click', () => {
                const input = document.getElementById('qr-full-url');
                input.select();
                navigator.clipboard.writeText(input.value);
                ToastManager.show('تم نسخ الرابط الثابت بنجاح! 📋', 'success');
            });
        }
    }

    async handleLogin(e) {
        e.preventDefault();
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        const errDiv = document.getElementById('login-error');

        try {
            const res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                errDiv.innerText = data.error || 'فشل تسجيل الدخول';
                errDiv.style.display = 'block';
            }
        } catch (err) {
            errDiv.innerText = 'حدث خطأ في الاتصال بالسيرفر';
            errDiv.style.display = 'block';
        }
    }

    async handleLogout() {
        await fetch('/api/auth/logout', { method: 'POST' });
        window.location.reload();
    }

    async loadData() {
        try {
            const [resFolders, resLinks] = await Promise.all([
                fetch('/api/folders').then(r => r.json()),
                fetch('/api/links').then(r => r.json())
            ]);

            if (resFolders.success) this.folders = resFolders.folders || [];
            if (resLinks.success) this.links = resLinks.links || [];

            this.renderTree();
            this.renderContent();
            this.populateSelects();
        } catch (err) {
            console.error("Error loading data:", err);
        }
    }

    populateSelects() {
        const folderSelects = [
            document.getElementById('folder-parent-id'),
            document.getElementById('link-folder-id')
        ];

        folderSelects.forEach(select => {
            if (!select) return;
            const defaultOpt = select.children[0];
            select.innerHTML = '';
            select.appendChild(defaultOpt);

            this.folders.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
                opt.textContent = `${'— '.repeat(this.getFolderDepth(f.id))} 📁 ${f.name}`;
                select.appendChild(opt);
            });
        });
    }

    getFolderDepth(folderId) {
        let depth = 0;
        let curr = this.folders.find(f => f.id == folderId);
        while (curr && curr.parent_id) {
            depth++;
            curr = this.folders.find(f => f.id == curr.parent_id);
        }
        return depth;
    }

    getFullFolderPath(folderId) {
        const path = [];
        let curr = this.folders.find(f => f.id == folderId);
        while (curr) {
            path.unshift(curr);
            curr = curr.parent_id ? this.folders.find(f => f.id == curr.parent_id) : null;
        }
        return path;
    }

    getFullLinkUrl(link) {
        const origin = window.location.origin;
        if (!link.folder_id) {
            return `${origin}/${link.slug}`;
        }
        const pathFolders = this.getFullFolderPath(link.folder_id);
        const folderSlugs = pathFolders.map(f => f.slug).join('/');
        return `${origin}/${folderSlugs}/${link.slug}`;
    }

    renderTree() {
        const treeContainer = document.getElementById('tree-container');
        if (!treeContainer) return;
        treeContainer.innerHTML = '';

        // Root Folder Item
        const rootItem = document.createElement('div');
        rootItem.className = `tree-item ${this.currentFolderId === null ? 'active' : ''}`;
        rootItem.innerHTML = `<span>🏠</span> <strong>الرئيسية (الجذر)</strong>`;
        rootItem.onclick = () => {
            this.currentFolderId = null;
            this.renderTree();
            this.renderContent();
        };
        rootItem.oncontextmenu = (e) => {
            e.preventDefault();
            this.showBackgroundContextMenu(e);
        };
        treeContainer.appendChild(rootItem);

        const rootFolders = this.folders.filter(f => !f.parent_id);
        rootFolders.forEach(f => {
            treeContainer.appendChild(this.buildTreeNode(f));
        });
    }

    buildTreeNode(folder) {
        const node = document.createElement('div');
        node.className = 'tree-node';

        const children = this.folders.filter(f => f.parent_id == folder.id);
        const hasChildren = children.length > 0;

        const item = document.createElement('div');
        item.className = `tree-item ${this.currentFolderId == folder.id ? 'active' : ''}`;
        item.innerHTML = `
            <span class="tree-toggler">${hasChildren ? '◀' : '•'}</span>
            <span>📁</span>
            <span>${folder.name}</span>
        `;

        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'tree-children';

        item.onclick = (e) => {
            e.stopPropagation();
            this.currentFolderId = folder.id;
            if (hasChildren) {
                childrenContainer.classList.toggle('open');
                item.querySelector('.tree-toggler').innerText = childrenContainer.classList.contains('open') ? '▼' : '◀';
            }
            this.renderTree();
            this.renderContent();
        };

        item.oncontextmenu = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.showFolderContextMenu(e, folder);
        };

        node.appendChild(item);

        if (hasChildren) {
            children.forEach(child => {
                childrenContainer.appendChild(this.buildTreeNode(child));
            });
            node.appendChild(childrenContainer);
        }

        return node;
    }

    renderContent() {
        const breadcrumb = document.getElementById('breadcrumb-bar');
        const itemsList = document.getElementById('items-list');
        const statsBadge = document.getElementById('folder-stats-badge');
        if (!itemsList) return;

        // Bind Windows-like background right click
        itemsList.oncontextmenu = (e) => {
            if (e.target.closest('.item-card') || e.target.closest('.btn')) return;
            e.preventDefault();
            this.showBackgroundContextMenu(e);
        };

        // Render Breadcrumb
        breadcrumb.innerHTML = '';
        const homeB = document.createElement('span');
        homeB.className = `breadcrumb-item ${this.currentFolderId === null ? 'active' : ''}`;
        homeB.innerText = 'الرئيسية';
        homeB.onclick = () => { this.currentFolderId = null; this.renderTree(); this.renderContent(); };
        breadcrumb.appendChild(homeB);

        if (this.currentFolderId !== null) {
            const path = this.getFullFolderPath(this.currentFolderId);
            path.forEach((f, idx) => {
                const sep = document.createElement('span');
                sep.innerText = ' / ';
                breadcrumb.appendChild(sep);

                const item = document.createElement('span');
                item.className = `breadcrumb-item ${idx === path.length - 1 ? 'active' : ''}`;
                item.innerText = f.name;
                item.onclick = () => { this.currentFolderId = f.id; this.renderTree(); this.renderContent(); };
                breadcrumb.appendChild(item);
            });
        }

        // Filter Items
        const currentSubFolders = this.folders.filter(f => f.parent_id == this.currentFolderId);
        const currentLinks = this.links.filter(l => l.folder_id == this.currentFolderId);

        if (statsBadge) {
            statsBadge.innerText = `${currentSubFolders.length} مجلدات | ${currentLinks.length} دروس وروابط`;
        }

        if (currentSubFolders.length === 0 && currentLinks.length === 0) {
            itemsList.innerHTML = `<div class="empty-state text-center py-5">
                <div style="font-size: 40px; margin-bottom: 10px;">📂</div>
                <p style="color: var(--text-muted);">هذا المجلد فارغ حالياً.</p>
                <p style="color: var(--accent); font-size: 13px; margin-top: 6px;">💡 اضغط كليك يمين في أي مكان فارغ لإنشاء مجلد أو رابط جديد بسرعة!</p>
            </div>`;
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'items-grid';

        // Render Folders Cards
        currentSubFolders.forEach(folder => {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.innerHTML = `
                <div class="card-header">
                    <span class="card-type-icon">📁</span>
                    <button class="card-actions-btn">⋮</button>
                </div>
                <div class="card-title">${folder.name}</div>
                <div class="card-slug">/${folder.slug}</div>
                <div class="card-footer">
                    <span style="color: var(--text-muted);">مجلد فرعي</span>
                </div>
            `;
            card.onclick = () => {
                this.currentFolderId = folder.id;
                this.renderTree();
                this.renderContent();
            };
            card.oncontextmenu = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showFolderContextMenu(e, folder);
            };
            card.querySelector('.card-actions-btn').onclick = (e) => {
                e.stopPropagation();
                this.showFolderContextMenu(e, folder);
            };
            grid.appendChild(card);
        });

        // Render Link Cards
        currentLinks.forEach(link => {
            const fullUrl = this.getFullLinkUrl(link);
            const isActive = parseInt(link.is_active) === 1;
            const trackAnalytics = parseInt(link.track_analytics) === 1;

            const card = document.createElement('div');
            card.className = 'item-card';
            card.innerHTML = `
                <div class="card-header">
                    <span class="card-type-icon">🔗</span>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <button class="btn btn-secondary btn-sm btn-copy-link" title="نسخ الرابط الثابت">📋 نسخ</button>
                        <button class="card-actions-btn">⋮</button>
                    </div>
                </div>
                <div class="card-title">${link.title}</div>
                <div class="card-slug" title="${fullUrl}">${fullUrl.replace(window.location.origin, '')}</div>
                <div class="card-target" title="${link.target_url}">➡️ ${link.target_url}</div>
                <div class="card-footer">
                    <span class="badge ${isActive ? 'badge-active' : 'badge-disabled'}">
                        ${isActive ? 'مفعل 🟢' : 'معطل 🔴'}
                    </span>
                    ${trackAnalytics ? `<span class="badge badge-active">📊 ${link.click_count || 0} زيارة</span>` : ''}
                </div>
            `;
            card.onclick = () => {
                this.openLinkModal(link);
            };
            card.oncontextmenu = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showLinkContextMenu(e, link);
            };
            card.querySelector('.btn-copy-link').onclick = (e) => {
                e.stopPropagation();
                navigator.clipboard.writeText(fullUrl);
                ToastManager.show('تم نسخ الرابط الثابت بنجاح! 📋', 'success');
            };
            card.querySelector('.card-actions-btn').onclick = (e) => {
                e.stopPropagation();
                this.showLinkContextMenu(e, link);
            };
            grid.appendChild(card);
        });

        itemsList.innerHTML = '';
        itemsList.appendChild(grid);
    }

    showBackgroundContextMenu(e) {
        ContextMenu.show(e.clientX, e.clientY, [
            { label: '📁 إنشاء مجلد جديد هنا', action: () => this.openFolderModal(null, this.currentFolderId) },
            { label: '🔗 إنشاء رابط جديد هنا', action: () => this.openLinkModal(null, this.currentFolderId) },
            'divider',
            { label: '🔄 تحديث العرض', action: () => this.loadData() }
        ]);
    }

    showFolderContextMenu(e, folder) {
        ContextMenu.show(e.clientX, e.clientY, [
            { label: '📂 فتح المجلد', action: () => { this.currentFolderId = folder.id; this.renderTree(); this.renderContent(); } },
            { label: '📁 إنشاء مجلد فرعي داخل هذا المجلد', action: () => this.openFolderModal(null, folder.id) },
            { label: '🔗 إنشاء رابط جديد داخل هذا المجلد', action: () => this.openLinkModal(null, folder.id) },
            'divider',
            { label: '✏️ تعديل اسم أو مسار المجلد', action: () => this.openFolderModal(folder) },
            { label: '🗑️ حذف المجلد وكافة محتوياته', danger: true, action: () => this.deleteItem({ type: 'folder', data: folder }) }
        ]);
    }

    showLinkContextMenu(e, link) {
        const fullUrl = this.getFullLinkUrl(link);
        ContextMenu.show(e.clientX, e.clientY, [
            { label: '📋 نسخ الرابط الثابت', action: () => { navigator.clipboard.writeText(fullUrl); ToastManager.show('تم نسخ الرابط الثابت بنجاح! 📋', 'success'); } },
            { label: '📱 توليد رمز QR Code', action: () => this.showQrModal(link) },
            { label: '⚙️ إحصائيات وتفاصيل الرابط', action: () => this.showAnalyticsModal(link) },
            'divider',
            { label: '✏️ تعديل الرابط', action: () => this.openLinkModal(link) },
            { label: '🗑️ حذف الرابط', danger: true, action: () => this.deleteItem({ type: 'link', data: link }) }
        ]);
    }

    showQrModal(link) {
        const fullUrl = this.getFullLinkUrl(link);
        document.getElementById('qr-link-title').innerText = link.title;
        document.getElementById('qr-full-url').value = fullUrl;

        new QRCode("qr-code-box", {
            text: fullUrl,
            width: 200,
            height: 200
        });

        ModalManager.open('modal-qr');
    }

    showAnalyticsModal(link) {
        document.getElementById('analytics-link-title').innerText = link.title;
        document.getElementById('analytics-total-count').innerText = link.click_count || 0;
        document.getElementById('analytics-last-access').innerText = link.last_accessed_at || 'لم يزار بعد';

        ModalManager.open('modal-analytics');
    }

    openFolderModal(folder = null, defaultParentId = null) {
        document.getElementById('modal-folder-title').innerText = folder ? 'تعديل المجلد' : 'إنشاء مجلد جديد';
        document.getElementById('folder-id').value = folder ? folder.id : '';
        document.getElementById('folder-name').value = folder ? folder.name : '';
        document.getElementById('folder-slug').value = folder ? folder.slug : '';
        document.getElementById('folder-parent-id').value = folder ? (folder.parent_id || '') : (defaultParentId !== null ? defaultParentId : (this.currentFolderId || ''));

        ModalManager.open('modal-folder');
    }

    openLinkModal(link = null, defaultFolderId = null) {
        document.getElementById('modal-link-title').innerText = link ? 'تعديل الرابط' : 'إنشاء رابط جديد';
        document.getElementById('link-id').value = link ? link.id : '';
        document.getElementById('link-title').value = link ? link.title : '';
        document.getElementById('link-target-url').value = link ? link.target_url : '';
        document.getElementById('link-slug').value = link ? link.slug : '';
        document.getElementById('link-folder-id').value = link ? (link.folder_id || '') : (defaultFolderId !== null ? defaultFolderId : (this.currentFolderId || ''));
        document.getElementById('link-is-active').checked = link ? (parseInt(link.is_active) === 1) : true;
        document.getElementById('link-track-analytics').checked = link ? (parseInt(link.track_analytics) === 1) : false;

        ModalManager.open('modal-link');
    }

    async saveFolder(e) {
        e.preventDefault();
        const id = document.getElementById('folder-id').value;
        const name = document.getElementById('folder-name').value;
        const slug = document.getElementById('folder-slug').value;
        const parent_id = document.getElementById('folder-parent-id').value;

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/folders/${id}` : '/api/folders';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, slug, parent_id })
        });
        const data = await res.json();
        if (data.success) {
            ModalManager.close('modal-folder');
            ToastManager.show('تم حفظ المجلد بنجاح! 📁', 'success');
            await this.loadData();
        } else {
            ToastManager.show(data.error || 'حدث خطأ أثناء حفظ المجلد', 'error');
        }
    }

    async saveLink(e) {
        e.preventDefault();
        const id = document.getElementById('link-id').value;
        const title = document.getElementById('link-title').value;
        const target_url = document.getElementById('link-target-url').value;
        const slug = document.getElementById('link-slug').value;
        const folder_id = document.getElementById('link-folder-id').value;
        const is_active = document.getElementById('link-is-active').checked ? 1 : 0;
        const track_analytics = document.getElementById('link-track-analytics').checked ? 1 : 0;

        const method = id ? 'PUT' : 'POST';
        const url = id ? `/api/links/${id}` : '/api/links';

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, target_url, slug, folder_id, is_active, track_analytics })
        });
        const data = await res.json();
        if (data.success) {
            ModalManager.close('modal-link');
            ToastManager.show('تم حفظ الرابط بنجاح! 🔗', 'success');
            await this.loadData();
        } else {
            ToastManager.show(data.error || 'حدث خطأ أثناء حفظ الرابط', 'error');
        }
    }

    async deleteItem(item) {
        if (!confirm(`هل أنت تأكد من إرادتك لحذف هذا الـ (${item.type === 'folder' ? 'مجلد' : 'رابط'})؟`)) return;

        const url = item.type === 'folder' ? `/api/folders/${item.data.id}` : `/api/links/${item.data.id}`;
        const res = await fetch(url, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            ToastManager.show('تم الحذف بنجاح! 🗑️', 'success');
            await this.loadData();
        } else {
            ToastManager.show(data.error || 'فشل عملية الحذف', 'error');
        }
    }

    handleSearch(query) {
        query = query.trim().toLowerCase();
        if (!query) {
            this.renderContent();
            return;
        }

        const itemsList = document.getElementById('items-list');
        const matchedLinks = this.links.filter(l => l.title.toLowerCase().includes(query) || l.slug.toLowerCase().includes(query) || l.target_url.toLowerCase().includes(query));
        const matchedFolders = this.folders.filter(f => f.name.toLowerCase().includes(query) || f.slug.toLowerCase().includes(query));

        if (matchedLinks.length === 0 && matchedFolders.length === 0) {
            itemsList.innerHTML = `<div class="empty-state text-center py-5">
                <p style="color: var(--text-muted);">لا توجد نتائج بحث تطابق: "${query}"</p>
            </div>`;
            return;
        }

        const grid = document.createElement('div');
        grid.className = 'items-grid';

        matchedFolders.forEach(f => {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.innerHTML = `<div class="card-title">📁 ${f.name}</div><div class="card-slug">/${f.slug}</div>`;
            card.onclick = () => {
                this.currentFolderId = f.id;
                document.getElementById('global-search').value = '';
                this.renderTree();
                this.renderContent();
            };
            grid.appendChild(card);
        });

        matchedLinks.forEach(l => {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.innerHTML = `<div class="card-title">🔗 ${l.title}</div><div class="card-target">➡️ ${l.target_url}</div>`;
            card.onclick = () => this.openLinkModal(l);
            grid.appendChild(card);
        });

        itemsList.innerHTML = '';
        itemsList.appendChild(grid);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.relinkApp = new RelinkApp();
});
