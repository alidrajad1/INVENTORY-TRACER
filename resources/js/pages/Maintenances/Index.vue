<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import MaintenanceTable from '@/components/Maintenances/MaintenanceTable.vue';
import MaintenanceModal from '@/components/Maintenances/MaintenanceModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Search, Download } from 'lucide-vue-next';

const breadcrumbs = [{ title: 'Maintenance Logs', href: route('maintenances.index') }];

const props = defineProps<{
    maintenances: any;
    assets: any[];
    filters: any;
}>();

// --- STATE ---
const showModal = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const selectedItem = ref(null);
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

// --- ACTIONS ---

// Open Create Modal
const openCreate = () => {
    modalMode.value = 'create';
    selectedItem.value = null;
    showModal.value = true;
};

// Open Edit Modal (Receives item from table emit)
const openEdit = (item: any) => {
    modalMode.value = 'edit';
    selectedItem.value = item;
    showModal.value = true;
};

// Delete Item
const deleteItem = (item: any) => {
    if (confirm('Are you sure you want to delete this maintenance record?')) {
        router.delete(route('maintenances.destroy', item.id));
    }
};

// Search & Filter
const handleSearch = () => {
    router.get(route('maintenances.index'), {
        search: search.value,
        status: statusFilter.value
    }, {
        preserveState: true,
        replace: true
    });
};

// Pagination
const handlePageChange = (url: string) => {
    router.get(url, {
        search: search.value,
        status: statusFilter.value
    }, { preserveState: true });
};

const exportExcel = () => {
    // Get filter parameters from current URL
    const params = new URLSearchParams(window.location.search);
    window.location.href = route('maintenances.export') + '?' + params.toString();
};
</script>

<template>

    <Head title="Maintenance Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Maintenance Logs</h2>
                    <p class="text-muted-foreground text-sm">Manage asset repair and maintenance schedules.</p>
                </div>
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <Button variant="outline" @click="exportExcel" class="w-full sm:w-auto">
                        <Download class="w-4 h-4 mr-2" />
                        Export Excel
                    </Button>

                    <Button @click="openCreate" class="w-full sm:w-auto">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Schedule
                    </Button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 items-center bg-card p-4 rounded-lg border">
                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Search asset or description..." class="pl-9"
                        @keyup.enter="handleSearch" />
                </div>

                <select v-model="statusFilter" @change="handleSearch"
                    class="h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <option value="">All Statuses</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <MaintenanceTable :maintenances="maintenances" @edit="openEdit" @delete="deleteItem"
                @page-change="handlePageChange" />

            <MaintenanceModal :show="showModal" :mode="modalMode" :assets="assets" :maintenance="selectedItem"
                @close="showModal = false" />
        </div>
    </AppLayout>
</template>