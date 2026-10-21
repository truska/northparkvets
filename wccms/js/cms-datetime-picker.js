// Load the CMS's bundled picker in dependency order, including when CDN jQuery fails.
(function () {
    const loadScript = (src) => new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = () => reject(new Error(`Could not load ${src}`));
        document.head.appendChild(script);
    });

    async function initialize() {
        const controls = document.querySelectorAll('[data-cms-minute-step]');
        if (!controls.length) return;
        try {
            if (!window.jQuery) await loadScript('/wccms/js-20250113/jquery.js');
            if (!window.jQuery.fn.datetimepicker) {
                await loadScript('/wccms/js-20250113/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js');
            }
            const $ = window.jQuery;
            controls.forEach((control) => {
                if ($(control).data('datetimepicker')) return;
                const savedValue = control.value;
                const timeOnly = control.dataset.cmsPickerType === 'time';
                control.type = 'text';
                control.value = savedValue.replace('T', ' ');
                $(control).datetimepicker({
                    format: timeOnly ? 'hh:ii' : 'yyyy-mm-dd hh:ii',
                    formatViewType: timeOnly ? 'time' : 'datetime',
                    minuteStep: Number(control.dataset.cmsMinuteStep),
                    startView: timeOnly ? 1 : 2,
                    maxView: timeOnly ? 1 : 4,
                    minView: 0,
                    autoclose: true,
                    todayBtn: !timeOnly,
                    pickerPosition: 'bottom-left'
                });
                control.dataset.cmsPickerReady = 'true';
            });
        } catch (error) {
            console.error('CMS date/time picker failed to initialize.', error);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, {once: true});
    } else {
        initialize();
    }
})();
