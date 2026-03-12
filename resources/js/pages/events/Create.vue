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
import { computed } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
}

defineProps<{
    categories: Category[];
}>();

const form = useForm({
    title: '',
    description: '',
    start_date: '',
    end_date: '',
    venue_name: '',
    venue_address: '',
    venue_city: '',
    venue_country: 'Polska',
    venue_latitude: '',
    venue_longitude: '',
    max_attendees: '' as string | number,
    ticket_price: '' as string | number,
    currency: 'PLN',
    category_ids: [] as number[],
    cover_image: null as File | null,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        max_attendees: data.max_attendees === '' ? null : Number(data.max_attendees),
        ticket_price: data.ticket_price === '' ? null : Number(data.ticket_price),
    })).post(route('events.store'), {
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

const startDateMin = computed(() => {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Strona główna', href: '/' },
    { title: 'Wydarzenia', href: '/events' },
    { title: 'Dodaj wydarzenie', href: '/events/create' },
];
</script>

<template>
    <Head title="Dodaj wydarzenie" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Dodaj wydarzenie</CardTitle>
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
                            <Label for="cover_image">Zdjęcie okładki</Label>
                            <input
                                id="cover_image"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm file:border-0 file:bg-transparent file:text-sm file:font-medium"
                                @change="form.cover_image = ($event.target as HTMLInputElement).files?.[0] ?? null"
                            />
                            <p class="text-xs text-muted-foreground">JPEG, PNG, WebP, max 2 MB</p>
                            <InputError :message="form.errors.cover_image" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="start_date">Data i godzina rozpoczęcia *</Label>
                                <Input id="start_date" v-model="form.start_date" type="datetime-local" required :min="startDateMin" />
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

                        <div class="flex gap-3 pt-2">
                            <Button type="submit" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Utwórz wydarzenie
                            </Button>
                            <Link :href="route('events.index')">
                                <Button type="button" variant="outline">Anuluj</Button>
                            </Link>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
