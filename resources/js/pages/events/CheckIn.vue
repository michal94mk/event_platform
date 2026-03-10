<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Download, LoaderCircle } from 'lucide-vue-next';

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
}

const props = defineProps<{
    event: Event;
    registrations: Registration[];
}>();

const form = useForm({
    qr_code: '',
});

const submitCheckIn = () => {
    form.post(route('events.check-in', props.event.slug), {
        preserveScroll: true,
        onSuccess: () => form.reset('qr_code'),
    });
};
</script>

<template>
    <Head :title="'Check-in: ' + event.title" />

    <div class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <header class="border-b border-[#19140035] px-4 py-3 dark:border-[#3E3E3A]">
            <nav class="mx-auto flex max-w-4xl items-center justify-between">
                <Link :href="route('home')" class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"> Event Platform </Link>
                <div class="flex gap-4">
                    <Link :href="route('events.show', event.slug)" class="text-sm hover:underline">Wydarzenie</Link>
                    <Link :href="route('events.index')" class="text-sm hover:underline">Wydarzenia</Link>
                    <Link :href="route('dashboard')" class="text-sm hover:underline">Dashboard</Link>
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl px-4 py-8">
            <h1 class="mb-2 text-2xl font-semibold">Check-in: {{ event.title }}</h1>
            <p class="mb-6 text-sm text-muted-foreground">Wpisz kod QR z biletu uczestnika lub zeskanuj kod.</p>

            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Odhacz wejście</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitCheckIn" class="flex gap-2">
                        <div class="grid flex-1 gap-1">
                            <Label for="qr_code" class="sr-only">Kod QR</Label>
                            <Input id="qr_code" v-model="form.qr_code" placeholder="Wklej lub wpisz kod z biletu" class="font-mono" />
                            <InputError :message="form.errors.qr_code" />
                        </div>
                        <Button type="submit" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Check-in
                        </Button>
                    </form>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between gap-4">
                    <CardTitle>Lista uczestników ({{ registrations.length }})</CardTitle>
                    <a :href="route('events.registrations.export', event.slug)">
                        <Button variant="outline" size="sm">
                            <Download class="mr-2 h-4 w-4" />
                            Eksportuj CSV
                        </Button>
                    </a>
                </CardHeader>
                <CardContent>
                    <ul class="divide-y">
                        <li
                            v-for="r in registrations"
                            :key="r.id"
                            class="flex items-center justify-between py-3"
                            :class="{ 'text-muted-foreground': r.checked_in }"
                        >
                            <span>{{ r.first_name }} {{ r.last_name }}</span>
                            <span v-if="r.checked_in" class="text-xs text-green-600 dark:text-green-400">
                                Odhaczono
                                <template v-if="r.checked_in_at">
                                    {{ new Date(r.checked_in_at).toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' }) }}
                                </template>
                            </span>
                            <span v-else class="text-xs text-amber-600 dark:text-amber-400">Oczekuje</span>
                        </li>
                    </ul>
                    <p v-if="registrations.length === 0" class="py-4 text-center text-sm text-muted-foreground">Brak rejestracji na to wydarzenie.</p>
                </CardContent>
            </Card>
        </main>
    </div>
</template>
