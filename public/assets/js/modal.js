class ModalManager {
    static open(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('open');
        }
    }

    static close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('open');
        }
    }

    static initCloseListeners() {
        document.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const backdrop = e.target.closest('.modal-backdrop');
                if (backdrop) backdrop.classList.remove('open');
            });
        });

        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    backdrop.classList.remove('open');
                }
            });
        });
    }
}

class ContextMenu {
    static init() {
        const menu = document.getElementById('context-menu');
        document.addEventListener('click', (e) => {
            if (menu && !menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });
    }

    static show(x, y, items) {
        const menu = document.getElementById('context-menu');
        if (!menu) return;

        menu.innerHTML = '';
        items.forEach(item => {
            if (item === 'divider') {
                const div = document.createElement('div');
                div.className = 'menu-divider';
                menu.appendChild(div);
            } else {
                const el = document.createElement('div');
                el.className = `menu-item ${item.danger ? 'danger' : ''}`;
                el.innerHTML = item.label;
                el.onclick = (e) => {
                    e.stopPropagation();
                    menu.classList.remove('active');
                    if (item.action) item.action();
                };
                menu.appendChild(el);
            }
        });

        // Adjust position if near screen edge
        const menuWidth = 220;
        const menuHeight = menu.children.length * 36;
        if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
        if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;

        menu.style.top = `${y}px`;
        menu.style.left = `${x}px`;
        menu.classList.add('active');
    }
}

class ToastManager {
    static show(message, type = 'success', duration = 3000) {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = type === 'success' ? '✅' : (type === 'error' ? '⚠️' : 'ℹ️');
        toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
        
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    ModalManager.initCloseListeners();
    ContextMenu.init();
});
