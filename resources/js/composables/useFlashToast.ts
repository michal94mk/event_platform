import { toast } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

export function useFlashToast() {
    const page = usePage();

    watch(
        () => page.props.flash as { success?: string; error?: string; info?: string; status?: string } | undefined,
        (flash) => {
            if (!flash) return;
            if (flash.success) toast.success(flash.success);
            if (flash.error) toast.error(flash.error);
            if (flash.info) toast.info(flash.info);
            if (flash.status) toast(flash.status);
        },
        { deep: true, immediate: true }
    );
}
