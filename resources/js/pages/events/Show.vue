<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Calendar, LoaderCircle } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Event {
    id: number;
    title: string;
    slug: string;
    description: string;
    start_date: string;
    end_date: string;
    venue_name: string;
    venue_address: string | null;
    venue_city: string | null;
    venue_country: string | null;
    ticket_price: string | null;
    currency: string;
    status: string;
    max_attendees: number | null;
    cover_image_url?: string | null;
    user?: { id: number; name: string };
    categories?: Category[];
}

const props = defineProps<{
    event: Event;
    canUpdate: boolean;
    canDelete: boolean;
    canRegister: boolean;
    registerDisabledReason?: string | null;
    placesLeft: number | null;
    isOrganizer: boolean;
}>();

const page = usePage();
const authUser = page.props.auth?.user;

const form = useForm({
    first_name: authUser?.name?.split(' ')[0] ?? '',
    last_name: authUser?.name?.split(' ').slice(1).join(' ') ?? '',
    email: authUser?.email ?? '',
    phone: '',
    ticket_quantity: 1,
});

const submitRegister = () => {
    form.post(route('events.register', props.event.slug));
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Wydarzenia', href: '/events' },
    { title: props.event.title, href: `/events/${props.event.slug}` },
];
</script>

<template>
    <Head :title="event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl">
            <Card>
                <img v-if="event.cover_image_url" :src="event.cover_image_url" :alt="event.title" class="h-48 w-full rounded-t-lg object-cover" />
                <CardHeader class="flex flex-row items-start justify-between gap-4">
                    <div>
                        <span
                            v-if="event.status !== 'published'"
                            class="mb-2 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900 dark:text-amber-200"
                        >
                            {{ event.status }}
                        </span>
                        <CardTitle class="text-2xl">{{ event.title }}</CardTitle>
                        <p v-if="event.user" class="mt-1 text-sm text-muted-foreground">Organizator: {{ event.user.name }}</p>
                    </div>
                    <div v-if="canUpdate || canDelete" class="flex gap-2">
                        <Link v-if="canUpdate" :href="route('events.edit', event.slug)">
                            <Button variant="outline" size="sm">Edytuj</Button>
                        </Link>
                        <button
                            v-if="canDelete"
                            type="button"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-destructive px-4 text-sm font-medium text-destructive-foreground hover:bg-destructive/90"
                            @click="
                                if (confirm('Czy na pewno usunąć to wydarzenie?')) {
                                    router.delete(route('events.destroy', event.slug));
                                }
                            "
                        >
                            Usuń
                        </button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="event.categories?.length" class="flex flex-wrap gap-2">
                        <span v-for="c in event.categories" :key="c.id" class="rounded-md bg-muted px-2 py-1 text-xs">
                            {{ c.name }}
                        </span>
                    </div>

                    <p class="whitespace-pre-wrap text-sm">{{ event.description }}</p>

                    <dl class="grid gap-2 text-sm">
                        <div>
                            <dt class="font-medium text-muted-foreground">Start</dt>
                            <dd>{{ new Date(event.start_date).toLocaleString('pl-PL') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Koniec</dt>
                            <dd>{{ new Date(event.end_date).toLocaleString('pl-PL') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Miejsce</dt>
                            <dd>
                                {{ event.venue_name }}
                                <template v-if="event.venue_address">, {{ event.venue_address }}</template>
                                <template v-if="event.venue_city">, {{ event.venue_city }}</template>
                                <template v-if="event.venue_country">, {{ event.venue_country }}</template>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-muted-foreground">Cena</dt>
                            <dd>
                                <template v-if="event.ticket_price && Number(event.ticket_price) > 0">
                                    {{ event.ticket_price }} {{ event.currency }}
                                </template>
                                <template v-else>Wstęp wolny</template>
                            </dd>
                        </div>
                        <div v-if="event.max_attendees">
                            <dt class="font-medium text-muted-foreground">Maks. uczestników</dt>
                            <dd>{{ event.max_attendees }}</dd>
                        </div>
                    </dl>

                    <div
                        v-if="form.errors.payment"
                        class="rounded-lg border border-destructive/50 bg-destructive/10 p-4 text-sm text-destructive"
                    >
                        {{ form.errors.payment }}
                    </div>
                    <div
                        v-else-if="registerDisabledReason"
                        class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        {{ registerDisabledReason }}
                    </div>
                    <div v-else-if="canRegister" class="rounded-lg border bg-muted/30 p-4">
                        <h3 class="mb-3 font-medium">Zapisz się na wydarzenie</h3>
                        <form @submit.prevent="submitRegister" class="flex flex-col gap-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1">
                                    <Label for="first_name">Imię *</Label>
                                    <Input id="first_name" v-model="form.first_name" required />
                                    <InputError :message="form.errors.first_name" />
                                </div>
                                <div class="grid gap-1">
                                    <Label for="last_name">Nazwisko *</Label>
                                    <Input id="last_name" v-model="form.last_name" required />
                                    <InputError :message="form.errors.last_name" />
                                </div>
                            </div>
                            <div class="grid gap-1">
                                <Label for="email">E-mail *</Label>
                                <Input id="email" v-model="form.email" type="email" required />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="phone">Telefon</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                            </div>
                            <div class="grid gap-1">
                                <Label for="ticket_quantity">Liczba biletów *</Label>
                                <Input id="ticket_quantity" v-model.number="form.ticket_quantity" type="number" min="1" :max="placesLeft ?? 10" />
                                <InputError :message="form.errors.ticket_quantity" />
                                <p v-if="placesLeft !== null" class="text-xs text-muted-foreground">Pozostało miejsc: {{ placesLeft }}</p>
                            </div>
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Zapisz się
                            </Button>
                        </form>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-4">
                        <a v-if="event.status === 'published'" :href="route('events.calendar', event.slug)" target="_blank" rel="noopener noreferrer">
                            <Button variant="outline" size="sm">
                                <Calendar class="mr-2 h-4 w-4" />
                                Dodaj do kalendarza
                            </Button>
                        </a>
                        <Link v-if="isOrganizer" :href="route('events.check-in.page', event.slug)">
                            <Button variant="secondary" size="sm">Check-in uczestników</Button>
                        </Link>
                        <Link v-if="$page.props.auth?.user" :href="route('registrations.index')">
                            <Button variant="outline" size="sm">Moje rejestracje</Button>
                        </Link>
                        <Link :href="route('events.index')">
                            <Button variant="outline">← Powrót do listy</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
