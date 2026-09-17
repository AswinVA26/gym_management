{{-- Dark mode: applies the saved theme (or system preference) before first
   paint to avoid a flash, then scopes the custom app chrome to follow it.
   The Light/Dark/Auto buttons are wired below manually: Bootstrap 5.3 ships
   the color-mode helpers in the bundle but does not auto-wire them. Persisted
   under localStorage key bs-theme. --}}
<script>
    (function () {
        var STORAGE_KEY = 'bs-theme';

        function getPreferred() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        function storedTheme() {
            try {
                var value = localStorage.getItem(STORAGE_KEY);
                return value === 'dark' || value === 'light' || value === 'auto' ? value : null;
            } catch (e) {
                return null;
            }
        }

        if (storedTheme() === 'dark' || (storedTheme() !== 'light' && getPreferred() === 'dark')) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }

        function applyTheme(theme) {
            var active = theme === 'auto' ? getPreferred() : theme;
            document.documentElement.setAttribute('data-bs-theme', active);
            try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}

            document.querySelectorAll('[data-bs-theme-value]').forEach(function (button) {
                button.classList.toggle('active', button.getAttribute('data-bs-theme-value') === theme);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var activeTheme = storedTheme() || 'auto';

            document.querySelectorAll('[data-bs-theme-value]').forEach(function (button) {
                button.addEventListener('click', function () {
                    applyTheme(button.getAttribute('data-bs-theme-value'));
                });
            });

            applyTheme(activeTheme);

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                if (storedTheme() === 'auto') {
                    applyTheme('auto');
                }
            });
        });
    })();
</script>
<style>
    html[data-bs-theme="dark"] body { background: #0f1115; }
    html[data-bs-theme="dark"] .topbar { background: #1b1f26; box-shadow: 0 2px 10px rgba(0,0,0,.35); }
    html[data-bs-theme="dark"] .sidebar { background: #15181d; }
    html[data-bs-theme="dark"] .sidebar a { color: #9aa1a9; }
    html[data-bs-theme="dark"] .sidebar a:hover { background: #23262c; color: #fff; }
    html[data-bs-theme="dark"] .stat-icon { opacity: .25; }
    html[data-bs-theme="dark"] .table thead th { color: #a7afb9; }
    html[data-bs-theme="dark"] .pre-wrap pre { color: var(--bs-body-color); }
    html[data-bs-theme="dark"] .auth-page { background: linear-gradient(135deg, #0b3d91, #101216); }
    .theme-toggle { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; }
    .theme-toggle .fa-sun { display: none; }
    html[data-bs-theme="dark"] .theme-toggle .fa-sun { display: inline; }
    html[data-bs-theme="dark"] .theme-toggle .fa-moon { display: none; }
</style>