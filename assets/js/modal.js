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
        document.addEventListener('click', () => {
            if (menu) menu.classList.remove('active');
        });
    }

    static show(x, y, onSelectCallbacks) {
        const menu = document.getElementById('context-menu');
        if (!menu) return;

        menu.style.top = `${y}px`;
        menu.style.left = `${x}px`;
        menu.classList.add('active');

        // Bind items
        const btnCopy = document.getElementById('ctx-copy');
        if (btnCopy) btnCopy.onclick = () => onSelectCallbacks.onCopy && onSelectCallbacks.onCopy();
        document.getElementById('ctx-qr').onclick = () => onSelectCallbacks.onQr && onSelectCallbacks.onQr();
        document.getElementById('ctx-edit').onclick = () => onSelectCallbacks.onEdit && onSelectCallbacks.onEdit();
        document.getElementById('ctx-settings').onclick = () => onSelectCallbacks.onSettings && onSelectCallbacks.onSettings();
        document.getElementById('ctx-delete').onclick = () => onSelectCallbacks.onDelete && onSelectCallbacks.onDelete();
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
