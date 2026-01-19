<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, Plus, Wrench } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue
} from '@/components/ui/select';
import MaintenanceTable from '@/components/Maintenances/MaintenanceTable.vue';
import { route } from 'ziggy-js';

const breadcrumbs = [{ title: 'Maintenances Logs', href: route('maintenances.index') }];
const props = defineProps<{
    maintenances: any;
    assets: any[]; // List asset untuk dropdown
    filters: { search: string };
}>();

const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingItem = ref<any>(null);

const form = useForm({
    asset_id: '',
    description: '',
    status: 'scheduled',
    scheduled_at: new Date().toISOString().split('T')[0], // Default today
    completed_at: '',
});

// Search watcher
watch(search, debounce((val: string) => {
    router.get(route('maintenaces.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const openCreate = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEdit = (item: any) => {
    isEditMode.value = true;
    editingItem.value = item;
    form.asset_id = item.asset_id.toString();
    form.description = item.description;
    form.status = item.status;
    form.scheduled_at = item.scheduled_at;
    form.completed_at = item.completed_at || '';
    isModalOpen.value = true;
};

const handleSubmit = () => {
    // Auto-fill completed_at jika status completed tapi tanggal kosong
    if(form.status === 'completed' && !form.completed_at) {
        form.completed_at = new Date().toISOString().split('T')[0];
    }

    if (isEditMode.value) {
        form.put(route('maintenances.update', editingItem.value.id), { onSuccess: () => isModalOpen.value = false });
    } else {
        form.post(route('maintenances.store'), { onSuccess: () => isModalOpen.value = false });
    }
};

const handleDelete = (item: any) => {
    if (confirm('Delete this maintenance record?')) {
        router.delete(route('maintenance.destroy'));
    }
};
</script>

<template>
    <Head title="Maintenance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex items-center justify-between bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Maintenance Schedule</h2>
                    <p class="text-muted-foreground">Track repairs, upgrades, and routine checks.</p>
                </div>
                <div class="bg-primary/10 p-3 rounded-full">
                    <Wrench class="w-6 h-6 text-primary" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="relative w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Search description or asset..." class="pl-8" />
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Schedule Maintenance
                </Button>
            </div>

            <MaintenanceTable 
                :maintenances="maintenances"
                @edit="openEdit"
                @delete="handleDelete"
                @page-change="(url) => router.get(url)"
            />
        </div>

        <Dialog :open="isModalOpen" @update:open="(val) => isModalOpen = val">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Update Maintenance' : 'Schedule New Maintenance' }}</DialogTitle>
                    <DialogDescription>Fill in the details below.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label>Select Asset</Label>
                        <Select v-model="form.asset_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Choose an asset" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="asset in assets" :key="asset.id" :value="asset.id.toString()">
                                    {{ asset.name }} ({{ asset.asset_tag }})
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="form.errors.asset_id" class="text-xs text-red-500">{{ form.errors.asset_id }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label>Description / Issue</Label>
                        <Textarea v-model="form.description" placeholder="e.g. Monthly cleaning, Fan replacement..." />
                        <span v-if="form.errors.description" class="text-xs text-red-500">{{ form.errors.description }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label>Scheduled Date</Label>
                            <Input type="date" v-model="form.scheduled_at" />
                        </div>
                        <div class="grid gap-2">
                            <Label>Status</Label>
                            <Select v-model="form.status">
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="scheduled">Scheduled</SelectItem>
                                    <SelectItem value="in_progress">In Progress</SelectItem>
                                    <SelectItem value="completed">Completed</SelectItem>
                                    <SelectItem value="canceled">Canceled</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="grid gap-2" v-if="form.status === 'completed'">
                        <Label>Completion Date</Label>
                        <Input type="date" v-model="form.completed_at" />
                    </div>

                </form>

                <DialogFooter>
                    <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                    <Button @click="handleSubmit" :disabled="form.processing">
                        {{ isEditMode ? 'Save Changes' : 'Create Schedule' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>