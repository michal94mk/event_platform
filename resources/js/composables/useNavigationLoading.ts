import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const isNavigating = ref(false);
let initialized = false;

export function useNavigationLoading() {
    if (!initialized) {
        initialized = true;
        router.on('start', () => {
            isNavigating.value = true;
        });
        router.on('finish', () => {
            isNavigating.value = false;
        });
        router.on('cancel', () => {
            isNavigating.value = false;
        });
    }
    return { isNavigating };
}
