<script setup lang="ts">
import Skeleton from '@/components/ui/skeleton/Skeleton.vue';
</script>

<template>
    <div class="flex flex-col gap-6 animate-in fade-in duration-200">
        <Skeleton class="h-9 w-48" />
        <div class="flex flex-col gap-4">
            <Skeleton class="h-4 w-full" />
            <Skeleton class="h-4 w-4/5" />
            <Skeleton class="h-4 w-4/5" />
            <Skeleton class="h-4 w-2/3" />
        </div>
        <div class="mt-4 flex flex-col gap-3">
            <Skeleton class="h-4 w-full" />
            <Skeleton class="h-4 w-11/12" />
            <Skeleton class="h-4 w-4/5" />
            <Skeleton class="h-4 w-5/6" />
            <Skeleton class="h-4 w-3/4" />
        </div>
    </div>
</template>
