<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="header" class="flex flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header" class="flex-1 px-4 py-6 md:px-6">
            <slot />
        </AppContent>
    </AppShell>
</template>
