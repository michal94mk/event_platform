<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import Dialog from '@/components/ui/dialog/Dialog.vue';
import DialogContent from '@/components/ui/dialog/DialogContent.vue';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue';
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Event {
    id: number;
    title: string;
    slug: string;
    start_date: string;
    venue_name: string;
}

interface Registration {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    ticket_quantity: number;
    total_amount: string;
    payment_status: string;
    checked_in: boolean;
    event: Event;
}

defineProps<{
    registrations: Registration[];
}>();

const isDialogOpen = ref(false);
const registrationToCancel = ref<Registration | null>(null);

const canCancelRegistration = computed(() => {
    if (!registrationToCancel.value) return false;

    if (registrationToCancel.value.checked_in) {
        return false;
    }

    const eventDate = new Date(registrationToCancel.value.event.start_date);
    const now = new Date();

    return eventDate.getTime() > now.getTime();
});

function openCancelDialog(registration: Registration) {
    registrationToCancel.value = registration;
    isDialogOpen.value = true;
}

function closeDialog() {
    isDialogOpen.value = false;
    registrationToCancel.value = null;
}

function confirmCancel() {
    if (!registrationToCancel.value || !canCancelRegistration.value) {
        closeDialog();
        return;
    }

    router.delete(route('registrations.destroy', registrationToCancel.value.id), {
        preserveScroll: true,
        onFinish: () => {
            closeDialog();
        },
    });
}

function canCancel(registration: Registration): boolean {
    if (registration.checked_in) {
        return false;
    }

    const eventDate = new Date(registration.event.start_date);
    const now = new Date();

    return eventDate.getTime() > now.getTime();
}
</script>

<template>
    <Head title="Moje rejestracje" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link :href="route('registrations.index')" class="text-sm font-medium hover:underline">Moje rejestracje</Link>
                    <Link :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="mb-6 text-2xl font-semibold">Moje rejestracje</h1>

            <div v-if="registrations.length === 0" class="rounded-xl border bg-card p-8 text-center text-muted-foreground">
                Nie masz jeszcze żadnych rejestracji.
                <Link :href="route('events.index')" class="mt-2 block text-primary hover:underline">Przejdź do wydarzeń →</Link>
            </div>

            <ul v-else class="space-y-3">
                <li v-for="r in registrations" :key="r.id">
                    <Card>
                        <CardContent class="flex flex-row items-center justify-between gap-4 p-4">
                            <div>
                                <p class="font-medium">{{ r.event.title }}</p>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(r.event.start_date).toLocaleDateString('pl-PL') }} · {{ r.event.venue_name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ r.first_name }} {{ r.last_name }} · {{ r.ticket_quantity }}
                                    {{ r.ticket_quantity === 1 ? 'bilet' : 'bilety' }}
                                    <span v-if="r.checked_in" class="text-green-600 dark:text-green-400"> · Odhaczono</span>
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <Link :href="route('registrations.show', r.id)">
                                    <Button variant="outline" size="sm">Bilet / QR</Button>
                                </Link>
                                <Button
                                    v-if="canCancel(r)"
                                    variant="ghost"
                                    size="sm"
                                    class="text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950"
                                    @click="openCancelDialog(r)"
                                >
                                    Anuluj udział
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </li>
            </ul>

            <Dialog v-model:open="isDialogOpen">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Anulować udział w wydarzeniu?</DialogTitle>
                        <DialogDescription>
                            Ta operacja usunie Twoją rejestrację. Nie będziesz już widniał na liście uczestników ani miał dostępu do biletu.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" size="sm" @click="closeDialog">
                            Anuluj
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            :disabled="!canCancelRegistration"
                            @click="confirmCancel"
                        >
                            Potwierdź anulowanie
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </main>
    </div>
</template>
