document.querySelectorAll('[data-admin-table-filter]').forEach((input) => {
    const tableId = input.getAttribute('data-admin-table-filter');
    const table = document.getElementById(tableId);

    if (!table) {
        return;
    }

    const rows = () => table.querySelectorAll('tbody tr[data-search]');

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();

        rows().forEach((row) => {
            const haystack = (row.getAttribute('data-search') ?? '').toLowerCase();
            row.classList.toggle('hidden', query !== '' && !haystack.includes(query));
        });
    });
});
