<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/uk.js"></script>
<script>
    (function () {
        const fromInput = document.getElementById('date_from');
        const toInput = document.getElementById('date_to');
        if (!fromInput || !toInput || typeof flatpickr === 'undefined') return;

        const common = {
            locale: 'uk',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd.m.Y',
            allowInput: true,
            disableMobile: true,
        };

        const fromPicker = flatpickr(fromInput, {
            ...common,
            defaultDate: fromInput.value || undefined,
            onChange: function (selectedDates) {
                if (selectedDates[0]) {
                    toPicker.set('minDate', selectedDates[0]);
                }
            },
        });

        const toPicker = flatpickr(toInput, {
            ...common,
            defaultDate: toInput.value || undefined,
            onChange: function (selectedDates) {
                if (selectedDates[0]) {
                    fromPicker.set('maxDate', selectedDates[0]);
                }
            },
        });

        if (fromInput.value) {
            toPicker.set('minDate', fromInput.value);
        }
        if (toInput.value) {
            fromPicker.set('maxDate', toInput.value);
        }
    })();
</script>
