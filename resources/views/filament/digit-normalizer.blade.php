<script>
    (() => {
        const numericInputSelector = 'input[inputmode="decimal"], input[inputmode="numeric"], input[type="number"]';
        const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
        const arabicDigits = '٠١٢٣٤٥٦٧٨٩';

        document.addEventListener('input', (event) => {
            if (!(event.target instanceof HTMLInputElement) || ! event.target.matches(numericInputSelector)) {
                return;
            }

            const input = event.target;
            const normalizedValue = input.value
                .replace(/[۰-۹]/g, (digit) => persianDigits.indexOf(digit))
                .replace(/[٠-٩]/g, (digit) => arabicDigits.indexOf(digit))
                .replace(/٫/g, '.');

            if (normalizedValue !== input.value) {
                input.value = normalizedValue;
            }
        }, true);
    })();
</script>
