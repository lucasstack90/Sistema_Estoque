document.addEventListener('DOMContentLoaded', () => {
    // Menu lateral no celular
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle && sidebar) {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('show') && !sidebar.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    // Confirmação antes de excluir
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Linhas clicáveis em tabelas
    document.querySelectorAll('tr[data-href]').forEach((row) => {
        row.classList.add('clickable');
        row.addEventListener('click', (e) => {
            if (!e.target.closest('a, button, form, input')) {
                window.location = row.dataset.href;
            }
        });
    });

    // Envia o formulário de filtro ao trocar um select
    document.querySelectorAll('form.auto-submit select').forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
    });

    // Máscara simples de CNPJ
    document.querySelectorAll('input[data-mask="cnpj"]').forEach((input) => {
        input.addEventListener('input', () => {
            const v = input.value.replace(/\D/g, '').slice(0, 14);
            input.value = v
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        });
    });
});
