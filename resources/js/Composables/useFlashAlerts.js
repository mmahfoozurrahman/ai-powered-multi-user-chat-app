import { computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useFlashAlerts() {
    const page = usePage();

    const success = computed(() => page.props.flash?.success);
    const error = computed(() => page.props.flash?.error);

    let lastSuccess = null;
    let lastError = null;

    watch(success, (value) => {
        if (!value || value === lastSuccess || !window.Swal) {
            return;
        }

        lastSuccess = value;

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            icon: 'success',
            title: value,
        });
    }, { immediate: true });

    watch(error, (value) => {
        if (!value || value === lastError || !window.Swal) {
            return;
        }

        lastError = value;

        window.Swal.fire({
            toast: true,
            position: 'top-end',
            timer: 3500,
            timerProgressBar: true,
            showConfirmButton: false,
            icon: 'error',
            title: value,
        });
    }, { immediate: true });
}
