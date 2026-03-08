<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Check, Copy } from 'lucide-vue-next';

defineProps<{
    subscriptionUrl: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Subskrypcja kalendarza', href: '/settings/calendar' },
];

const copied = ref(false);

async function copyToClipboard(url: string) {
    try {
        await navigator.clipboard.writeText(url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // fallback for older browsers
        const input = document.createElement('input');
        input.value = url;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Subskrypcja kalendarza" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Subskrypcja kalendarza"
                    description="Dodaj ten link w Google Calendar, Outlook lub innym kalendarzu, aby widzieć swoje wydarzenia automatycznie. Kalendarz będzie się odświeżał co ok. 15 minut."
                />

                <div class="space-y-2">
                    <Label for="subscription-url">Link do subskrypcji</Label>
                    <div class="flex gap-2">
                        <Input
                            id="subscription-url"
                            :model-value="subscriptionUrl"
                            type="text"
                            readonly
                            class="font-mono text-sm"
                        />
                        <Button
                            type="button"
                            variant="outline"
                            size="icon"
                            :aria-label="copied ? 'Skopiowano' : 'Kopiuj link'"
                            @click="copyToClipboard(subscriptionUrl)"
                        >
                            <Check v-if="copied" class="h-4 w-4 text-green-600" />
                            <Copy v-else class="h-4 w-4" />
                        </Button>
                    </div>
                    <p v-if="copied" class="text-sm text-green-600 dark:text-green-400">
                        Skopiowano do schowka
                    </p>
                </div>

                <div class="rounded-lg border bg-muted/50 p-4 text-sm text-muted-foreground">
                    <p class="font-medium text-foreground">Jak dodać subskrypcję?</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        <li>
                            <strong>Google Calendar:</strong> Ustawienia → Dodaj kalendarz → Z adresu URL → wklej link
                        </li>
                        <li>
                            <strong>Outlook:</strong> Kalendarz → Dodaj kalendarz → Subskrybuj z sieci Web → wklej link
                        </li>
                        <li>
                            <strong>Apple Calendar:</strong> Plik → Nowa subskrypcja kalendarza → wklej link
                        </li>
                    </ul>
                </div>

                <p class="text-xs text-muted-foreground">
                    Link jest osobisty i nie udostępniaj go innym. Zawiera wydarzenia, na które jesteś zapisany, oraz wydarzenia, które organizujesz.
                </p>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
