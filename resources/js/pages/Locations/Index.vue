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

import LocationTable from '@/components/Locations/LocationTable.vue';

const props = defineProps<{
    locations: any;
    filters: { search: string };
}>();

const breadcrumbs = [{ title: 'Locations', href: route('locations.index') }];

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingLocation = ref<any>(null);

const form = useForm({
    name: '',
    building: ''
});

watch(search, debounce((val: string) => {
    router.get(route('locations.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const openCreate = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEdit = (loc: any) => {
    isEditMode.value = true;
    editingLocation.value = loc;
    form.name = loc.name;
    form.building = loc.building;
    form.clearErrors();
    isModalOpen.value = true;
};

const handleSubmit = () => {
    if (isEditMode.value && editingLocation.value) {
        form.put(route('locations.update', editingLocation.value.id), {
            onSuccess: () => isModalOpen.value = false,
        });
    } else {
        form.post(route('locations.store'), {
            onSuccess: () => isModalOpen.value = false,
        });
    }
};

const handleDelete = (loc: any) => {
    if (loc.assets_count > 0) {
        alert(`Cannot delete location "${loc.name}" because it has ${loc.assets_count} assets assigned.`);
        return;
    }
    if (confirm(`Are you sure you want to delete "${loc.name}"?`)) {
        router.delete(route('locations.destroy', loc.id));
    }
};
</script>

<template>
    <Head title="Locations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Locations</h2>
                    <p class="text-muted-foreground">Manage physical locations and buildings.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <span class="text-primary font-bold text-xl">{{ locations.total }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card relative min-h-[500px]">
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search locations or buildings..." class="pl-8" />
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Add Location
                    </Button>
                </div>

                <LocationTable 
                    :locations="locations"
                    @edit="openEdit"
                    @delete="handleDelete"
                    @page-change="(url) => router.get(url)"
                />
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="(val) => isModalOpen = val">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Edit Location' : 'Add New Location' }}</DialogTitle>
                    <DialogDescription>
                        Define the room name and the building it belongs to.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="name">Location / Room Name</Label>
                        <Input id="name" v-model="form.name" placeholder="e.g. Server Room, Meeting Room A" />
                        <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="building">Building Name</Label>
                        <Input id="building" v-model="form.building" placeholder="e.g. Main Building, Warehouse 1" />
                        <span v-if="form.errors.building" class="text-xs text-red-500">{{ form.errors.building }}</span>
                    </div>
                </form>

                <DialogFooter>
                    <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                    <Button type="submit" @click="handleSubmit" :disabled="form.processing">
                        {{ isEditMode ? 'Save Changes' : 'Create Location' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>