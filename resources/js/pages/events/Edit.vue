<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

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
    venue_latitude: string | null;
    venue_longitude: string | null;
    max_attendees: number | null;
    ticket_price: string | null;
    currency: string;
    status: string;
    cover_image_url: string | null;
    categories?: { id: number }[];
}

const props = defineProps<{
    event: Event;
    categories: Category[];
}>();

const form = useForm({
    title: props.event.title,
    description: props.event.description,
    start_date: props.event.start_date.slice(0, 16),
    end_date: props.event.end_date.slice(0, 16),
    venue_name: props.event.venue_name,
    venue_address: props.event.venue_address ?? '',
    venue_city: props.event.venue_city ?? '',
    venue_country: props.event.venue_country ?? 'Polska',
    venue_latitude: props.event.venue_latitude ?? '',
    venue_longitude: props.event.venue_longitude ?? '',
    max_attendees: props.event.max_attendees ?? ('' as number | ''),
    ticket_price: props.event.ticket_price ?? ('' as string | number | ''),
    currency: props.event.currency || 'PLN',
    status: props.event.status,
    category_ids: (props.event.categories ?? []).map((c) => c.id),
    cover_image: null as File | null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        max_attendees: data.max_attendees === '' ? null : Number(data.max_attendees),
        ticket_price: data.ticket_price === '' ? null : Number(data.ticket_price),
    })).put(route('events.update', props.event.slug), {
        forceFormData: true,
    });
};

const toggleCategory = (id: number) => {
    const idx = form.category_ids.indexOf(id);
    if (idx === -1) {
        form.category_ids = [...form.category_ids, id];
    } else {
        form.category_ids = form.category_ids.filter((c) => c !== id);
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Wydarzenia', href: '/events' },
    { title: props.event.title, href: `/events/${props.event.slug}` },
    { title: 'Edycja', href: `/events/${props.event.slug}/edit` },
];
</script>

<template>
    <Head :title="'Edycja: ' + event.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Edycja wydarzenia</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="flex flex-col gap-6">
                        <div class="grid gap-2">
                            <Label for="title">Tytuł *</Label>
                            <Input id="title" v-model="form.title" required placeholder="Nazwa wydarzenia" />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="description">Opis *</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                required
                                rows="4"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Opis wydarzenia"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="grid gap-2">
                            <Label>Zdjęcie okładki</Label>
                            <img
                                v-if="event.cover_image_url"
                                :src="event.cover_image_url"
                                alt="Okładka"
                                class="h-40 w-full rounded-md border object-cover"
                            />
                            <input
                                id="cover_image"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                                @change="form.cover_image = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <p class="text-xs text-muted-foreground">
                                {{ event.cover_image_url ? 'Wybierz nowe zdjęcie, aby zastąpić' : 'JPEG, PNG, WebP, max 2 MB' }}
                            </p>
                            <InputError :message="form.errors.cover_image" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_date">Data i godzina rozpoczęcia *</Label>
                                <Input id="start_date" v-model="form.start_date" type="datetime-local" required />
                                <InputError :message="form.errors.start_date" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="end_date">Data i godzina zakończenia *</Label>
                                <Input id="end_date" v-model="form.end_date" type="datetime-local" required />
                                <InputError :message="form.errors.end_date" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="venue_name">Miejsce (nazwa) *</Label>
                            <Input id="venue_name" v-model="form.venue_name" required placeholder="np. Centrum Konferencyjne" />
                            <InputError :message="form.errors.venue_name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="venue_address">Adres</Label>
                            <Input id="venue_address" v-model="form.venue_address" placeholder="Ulica, numer" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="venue_city">Miasto</Label>
                                <Input id="venue_city" v-model="form.venue_city" placeholder="Miasto" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="venue_country">Kraj</Label>
                                <Input id="venue_country" v-model="form.venue_country" placeholder="Kraj" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label>Kategorie</Label>
                            <div class="flex flex-wrap gap-3">
                                <label v-for="cat in categories" :key="cat.id" class="flex cursor-pointer items-center gap-2">
                                    <Checkbox :checked="form.category_ids.includes(cat.id)" @update:checked="toggleCategory(cat.id)" />
                                    <span class="text-sm">{{ cat.name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="max_attendees">Maks. liczba uczestników</Label>
                                <Input id="max_attendees" v-model="form.max_attendees" type="number" min="1" placeholder="Puste = bez limitu" />
                                <InputError :message="form.errors.max_attendees" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="ticket_price">Cena biletu (PLN)</Label>
                                <Input
                                    id="ticket_price"
                                    v-model="form.ticket_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0 = wstęp wolny"
                                />
                                <InputError :message="form.errors.ticket_price" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option value="draft">Szkic</option>
                                <option value="published">Opublikowane</option>
                                <option value="cancelled">Anulowane</option>
                                <option value="completed">Zakończone</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>

                        <div class="flex gap-3 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Zapisz zmiany
                            </Button>
                            <Link :href="route('events.show', event.slug)">
                                <Button type="button" variant="outline">Anuluj</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
