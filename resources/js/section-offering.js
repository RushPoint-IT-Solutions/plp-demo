document.addEventListener('DOMContentLoaded', function () {
    const lines = document.querySelectorAll('.section-offering-page .cor-info-line');
    if (!lines || !lines.length) return;

    lines.forEach(line => {
        const labelEl = line.querySelector('.cor-info-label');
        if (!labelEl) return;
        const labelText = labelEl.textContent.trim().toLowerCase();
        if (!labelText.startsWith('school year')) return;

        const valueEl = line.querySelector('.cor-info-value');
        if (!valueEl) return;
        const raw = valueEl.textContent.trim();

        // Extract school year range (e.g. 2025-2026)
        const yearMatch = raw.match(/(\d{4}-\d{4})/);

        // Extract semester token (1st, 2nd, 3rd, first, second, etc., or plain digits)
        const semMatch = raw.match(/(1st|1|first|one|2nd|2|second|two|3rd|3|third|three)/i);

        let result = '';
        if (yearMatch) result = yearMatch[1];

        if (semMatch) {
            const token = semMatch[1].toLowerCase();
            let num = null;
            if (/1|first|one/.test(token)) num = 1;
            else if (/2|second|two/.test(token)) num = 2;
            else if (/3|third|three/.test(token)) num = 3;

            if (num !== null) {
                const suffix = num === 1 ? 'ST' : num === 2 ? 'ND' : num === 3 ? 'RD' : 'TH';
                result = (result ? result + ' / ' : '') + `${num}${suffix} SEMESTER`;
            }
        }

        // Fallback: if nothing parsed, strip leading 'SY' and trim
        if (!result) {
            const fallback = raw.replace(/^\s*SY\s*/i, '').trim();
            valueEl.textContent = fallback;
        } else {
            valueEl.textContent = result;
        }
    });
});
