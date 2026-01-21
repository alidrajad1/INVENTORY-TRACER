<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, Plus } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import { route } from 'ziggy-js';

import CategoryTable from '@/components/Categories/CategoryTable.vue';

const props = defineProps<{
    categories: any;
    filters: { search: string };
}>();

const breadcrumbs = [{ title: 'Categories', href: route('categories.index') }];

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingCategory = ref<any>(null);

const form = useForm({ name: '', code: '' });

watch(search, debounce((val: string) => {
    router.get(
        route('categories.index'),
        { search: val },
        { preserveState: true, replace: true }
    );
}, 300));

const openCreate = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEdit = (category: any) => {
    isEditMode.value = true;
    editingCategory.value = category;
    form.name = category.name;
    form.code = category.code;
    form.clearErrors();
    isModalOpen.value = true;
};

const handleSubmit = () => {
    if (isEditMode.value && editingCategory.value) {
        form.put(route('categories.update', editingCategory.value.id), {
            onSuccess: () => isModalOpen.value = false,
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => isModalOpen.value = false,
        });
    }
};

const handleDelete = (category: any) => {
    if (category.assets_count > 0) {
        alert(`Cannot delete category "${category.name}" because it is used by ${category.assets_count} assets.`);
        return;
    }
    if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
        router.delete(route('categories.destroy', category.id));
    }
};
</script>

<template>

    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Categories</h2>
                    <p class="text-muted-foreground">Manage asset classifications and groups.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <span class="text-primary font-bold text-xl">{{ categories.total }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card relative min-h-[500px]">

                <div class="flex items-center justify-between p-4 border-b">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search categories..." class="pl-8" />
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Add Category
                    </Button>
                </div>

                <CategoryTable :categories="categories" @edit="openEdit" @delete="handleDelete"
                    @page-change="(url) => router.get(url)" />
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="(val: boolean) => isModalOpen = val">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Edit Category' : 'Create New Category' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update existing category name.' : 'Add a new category for your assets.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">

                    <div class="grid gap-2">
                        <Label for="code">Category Code</Label>
                        <Input id="code" v-model="form.code" placeholder="e.g. CAT-LP, ELEC" />
                        <span v-if="form.errors.code" class="text-xs text-red-500">{{ form.errors.code }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Category Name</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g. Laptop, Electronics" />
                        <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                    </div>

                </form>

                <DialogFooter>
                    <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                    <Button type="submit" @click="handleSubmit" :disabled="form.processing">
                        {{ isEditMode ? 'Save Changes' : 'Create Category' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>