<script setup lang="ts">
import { Toaster } from 'vue-sonner';
import { useFlashToast } from '@/composables/useFlashToast';

useFlashToast();
</script>

<template>
    <Toaster position="bottom-right" rich-colors close-button />
</template>
