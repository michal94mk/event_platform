<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    events_count: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Panel admina', href: '/admin' },
    { title: 'Kategorie', href: '/admin/categories' },
];

defineProps<{
    categories: Category[];
}>();

const addDialogOpen = ref(false);
const editingId = ref<number | null>(null);

const addForm = useForm({
    name: '',
    description: '',
});

const editForm = useForm({
    name: '',
    description: '',
});

function openAdd() {
    addForm.reset();
    addDialogOpen.value = true;
}

function submitAdd() {
    addForm.post(route('admin.categories.store'), {
        onSuccess: () => {
            addDialogOpen.value = false;
        },
    });
}

function startEdit(c: Category) {
    editingId.value = c.id;
    editForm.name = c.name;
    editForm.description = c.description ?? '';
}

function cancelEdit() {
    editingId.value = null;
}

function submitEdit(id: number) {
    editForm.put(route('admin.categories.update', id), {
        onSuccess: () => {
            editingId.value = null;
        },
    });
}

function deleteCategory(c: Category) {
    if (c.events_count > 0) {
        alert('Nie można usunąć kategorii z przypisanymi wydarzeniami.');
        return;
    }
    if (!confirm(`Czy na pewno usunąć kategorię „${c.name}”?`)) return;
    router.delete(route('admin.categories.destroy', c.id));
}
</script>

<template>
    <Head title="Kategorie – Panel admina" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Kategorie wydarzeń</h1>
                    <p class="mt-1 text-muted-foreground">
                        Zarządzaj kategoriami dostępnymi przy tworzeniu wydarzeń.
                    </p>
                </div>
                <Button @click="openAdd" class="gap-2">
                    <Plus class="size-4" />
                    Dodaj kategorię
                </Button>
            </div>

            <Card>
                <CardContent class="p-0">
                    <div
                        v-if="categories.length === 0"
                        class="p-8 text-center text-muted-foreground"
                    >
                        Brak kategorii. Dodaj pierwszą.
                    </div>
                    <ul v-else class="divide-y">
                        <li
                            v-for="c in categories"
                            :key="c.id"
                            class="flex items-center justify-between gap-4 p-4 transition-colors hover:bg-muted/30"
                        >
                            <div v-if="editingId !== c.id" class="min-w-0 flex-1">
                                <p class="font-medium">{{ c.name }}</p>
                                <p v-if="c.description" class="text-sm text-muted-foreground">{{ c.description }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ c.events_count }} {{ c.events_count === 1 ? 'wydarzenie' : 'wydarzeń' }}
                                </p>
                            </div>
                            <form
                                v-else
                                class="flex flex-1 flex-wrap items-end gap-4"
                                @submit.prevent="submitEdit(c.id)"
                            >
                                <div class="flex-1 min-w-[200px] space-y-2">
                                    <Label for="edit-name">Nazwa</Label>
                                    <Input
                                        id="edit-name"
                                        v-model="editForm.name"
                                        required
                                        class="w-full"
                                    />
                                </div>
                                <div class="flex-1 min-w-[200px] space-y-2">
                                    <Label for="edit-desc">Opis</Label>
                                    <Input
                                        id="edit-desc"
                                        v-model="editForm.description"
                                        class="w-full"
                                        placeholder="Opcjonalnie"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <Button type="submit" :disabled="editForm.processing">Zapisz</Button>
                                    <Button type="button" variant="outline" @click="cancelEdit">Anuluj</Button>
                                </div>
                            </form>
                            <div v-if="editingId !== c.id" class="flex shrink-0 gap-2">
                                <Button variant="ghost" size="icon" @click="startEdit(c)">
                                    <Pencil class="size-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    :disabled="c.events_count > 0"
                                    title="Nie można usunąć kategorii z wydarzeniami"
                                    @click="deleteCategory(c)"
                                >
                                    <Trash2 class="size-4 text-destructive" />
                                </Button>
                            </div>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="addDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Dodaj kategorię</DialogTitle>
                    <DialogDescription>
                        Nowa kategoria będzie dostępna przy tworzeniu wydarzeń.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <div class="space-y-2">
                        <Label for="add-name">Nazwa</Label>
                        <Input
                            id="add-name"
                            v-model="addForm.name"
                            required
                            placeholder="np. Konferencje"
                        />
                        <p v-if="addForm.errors.name" class="text-sm text-destructive">{{ addForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="add-desc">Opis (opcjonalnie)</Label>
                        <Input
                            id="add-desc"
                            v-model="addForm.description"
                            placeholder="Krótki opis kategorii"
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="addDialogOpen = false">
                            Anuluj
                        </Button>
                        <Button type="submit" :disabled="addForm.processing">
                            Dodaj
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
