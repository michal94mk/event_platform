<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { Download, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Event {
    id: number;
    title: string;
    slug: string;
}

interface Registration {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    ticket_quantity: number;
    checked_in: boolean;
    checked_in_at: string | null;
    payment_status: string;
    can_refund: boolean;
}

const props = defineProps<{
    event: Event;
    registrations: Registration[];
}>();

const page = usePage();
const refundingId = ref<number | null>(null);

const form = useForm({
    qr_code: '',
});

const submitCheckIn = () => {
    form.post(route('events.check-in', props.event.slug), {
        preserveScroll: true,
        onSuccess: () => form.reset('qr_code'),
    });
};

function paymentLabel(status: string): string {
    const map: Record<string, string> = {
        paid: 'Opłacone',
        pending: 'Oczekuje na płatność',
        failed: 'Płatność nieudana',
        refunded: 'Zwrócone',
    };
    return map[status] ?? status;
}

function requestRefund(registrationId: number) {
    if (!confirm('Czy na pewno zwrócić płatność Stripe za tę rejestrację?')) {
        return;
    }
    refundingId.value = registrationId;
    router.post(
        route('registrations.refund', registrationId),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                refundingId.value = null;
            },
        },
    );
}
</script>

<template>
    <Head :title="'Check-in: ' + event.title" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex flex-wrap gap-3 sm:gap-4">
                    <Link :href="route('events.show', event.slug)" class="text-sm hover:underline">Wydarzenie</Link>
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="mb-2 text-2xl font-semibold">Check-in: {{ event.title }}</h1>
            <p class="mb-6 text-sm text-muted-foreground">Wpisz kod QR z biletu uczestnika lub zeskanuj kod.</p>

            <p v-if="(page.props.errors as Record<string, string>)?.refund" class="mb-4 rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                {{ (page.props.errors as Record<string, string>).refund }}
            </p>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Odhacz wejście</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitCheckIn" class="flex flex-col gap-3 sm:flex-row sm:gap-2">
                        <div class="grid min-w-0 flex-1 gap-1">
                            <Label for="qr_code" class="sr-only">Kod QR</Label>
                            <Input id="qr_code" v-model="form.qr_code" placeholder="Wklej lub wpisz kod z biletu" class="font-mono" />
                            <InputError :message="form.errors.qr_code" />
                        </div>
                        <Button type="submit" :disabled="form.processing" class="shrink-0">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Check-in
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <CardTitle>Lista uczestników ({{ registrations.length }})</CardTitle>
                    <a :href="route('events.registrations.export', event.slug)" class="w-fit">
                        <Button variant="outline" size="sm" class="w-full sm:w-auto">
                            <Download class="mr-2 h-4 w-4 shrink-0" />
                            Eksportuj CSV
                        </Button>
                    </a>
                </CardHeader>
                <CardContent>
                    <ul class="divide-y">
                        <li
                            v-for="r in registrations"
                            :key="r.id"
                            class="flex flex-col gap-2 py-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-x-4"
                            :class="{ 'text-muted-foreground': r.checked_in }"
                        >
                            <div class="min-w-0 flex-1">
                                <span class="font-medium">{{ r.first_name }} {{ r.last_name }}</span>
                                <span class="text-muted-foreground"> · {{ r.email }}</span>
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ paymentLabel(r.payment_status) }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span v-if="r.checked_in" class="text-xs text-green-600 dark:text-green-400">
                                    Odhaczono
                                    <template v-if="r.checked_in_at">
                                        {{ new Date(r.checked_in_at).toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' }) }}
                                    </template>
                                </span>
                                <span v-else class="text-xs text-amber-600 dark:text-amber-400">Oczekuje na wejście</span>
                                <Button
                                    v-if="r.can_refund"
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    class="text-destructive hover:bg-destructive/10"
                                    :disabled="refundingId === r.id"
                                    @click="requestRefund(r.id)"
                                >
                                    <LoaderCircle v-if="refundingId === r.id" class="mr-2 h-4 w-4 animate-spin" />
                                    Zwrot płatności
                                </Button>
                            </div>
                        </li>
                    </ul>
                    <p v-if="registrations.length === 0" class="py-4 text-center text-sm text-muted-foreground">Brak rejestracji na to wydarzenie.</p>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
