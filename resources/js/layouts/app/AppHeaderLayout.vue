<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import PageSkeleton from '@/components/PageSkeleton.vue';
import { useNavigationLoading } from '@/composables/useNavigationLoading';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { isNavigating } = useNavigationLoading();
</script>

<template>
    <AppShell variant="header" class="flex flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header" class="relative flex-1 px-4 py-6 md:px-6">
            <slot />
            <Transition name="skeleton-fade">
                <div
                    v-if="isNavigating"
                    class="absolute inset-0 z-10 overflow-auto bg-[#FDFDFC] px-4 py-6 dark:bg-[#0a0a0a] md:px-6"
                >
                    <div class="mx-auto max-w-6xl">
                        <PageSkeleton />
                    </div>
                </div>
            </Transition>
        </AppContent>
    </AppShell>
</template>

<style scoped>
.skeleton-fade-enter-active,
.skeleton-fade-leave-active {
    transition: opacity 0.15s ease;
}
.skeleton-fade-enter-from,
.skeleton-fade-leave-to {
    opacity: 0;
}
</style>
