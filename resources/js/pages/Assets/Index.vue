<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import { Search, Plus, Download, Filter, X } from 'lucide-vue-next';
import { route } from 'ziggy-js';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue
} from '@/components/ui/select';

import AssetStats from '@/components/Assets/AssetStats.vue';
import AssetTable from '@/components/Assets/AssetTable.vue';
import AssetFormModal from '@/components/Assets/AssetFormModal.vue';
import AssetDetailModal from '@/components/Assets/AssetDetailModal.vue';
import AssignModal from '@/components/Assets/AssignModal.vue';
import ReturnModal from '@/components/Assets/ReturnModal.vue';

const props = defineProps<{
    assets: any;
    categories: any[];
    locations: any[];
    employees: any[];
    filters: any;
    stats: any;
    years: number[];
}>();

const breadcrumbs = [{ title: 'Assets', href: route('assets.index') }];

// --- FILTER STATE ---
// Combine search and other filters into one reactive object
const params = ref({
    search: props.filters.search || '',
    status: props.filters.status || 'ALL',
    loan_type: props.filters.loan_type || 'ALL',
    year: props.filters.year || 'ALL',
});

// Automatic Filter Watcher
watch(params, debounce((val) => {
    // Clear 'ALL' values so they aren't sent to the URL if not needed
    const query = {
        ...val,
        status: val.status === 'ALL' ? null : val.status,
        loan_type: val.loan_type === 'ALL' ? null : val.loan_type,
        year: val.year === 'ALL' ? null : val.year,
    };

    router.get(route('assets.index'), query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 300), { deep: true });

const resetFilters = () => {
    params.value = {
        search: '',
        status: 'ALL',
        loan_type: 'ALL',
        year: 'ALL',
    };
};

// --- FORM LOGIC ---
const isFormOpen = ref(false);
const formMode = ref<'create' | 'edit'>('create');
const isDetailOpen = ref(false);
const selectedAsset = ref<any>(null);
const isSyncing = ref(false);

const isAssignModalOpen = ref(false);
const assigningAsset = ref<any>(null);
const isReturnModalOpen = ref(false);
const returningAsset = ref<any>(null);

const form = useForm({
    asset_tag: '',
    serial_number: '',
    name: '',
    brand: '',
    model: '',
    category_id: null,
    location_id: null,
    status: 'AVAILABLE',
    purchase_year: '',
    period: '',
    vendor: '',
    hardware_specs: { cpu: '', ram: '', storage: '', os: '' }
});

const openCreate = () => {
    form.reset();
    form.clearErrors();
    form.hardware_specs = { cpu: '', ram: '', storage: '', os: '' };
    formMode.value = 'create';
    isFormOpen.value = true;
};

const openEdit = (asset: any) => {
    selectedAsset.value = asset;
    form.clearErrors();
    Object.assign(form, {
        asset_tag: asset.asset_tag,
        serial_number: asset.serial_number,
        name: asset.name,
        brand: asset.brand,
        model: asset.model,
        category_id: asset.category_id,
        location_id: asset.location_id,
        status: asset.status,
        purchase_year: asset.purchase_year,
        period: asset.period,
        vendor: asset.vendor,
        hardware_specs: {
            cpu: asset.hardware_specs?.cpu || '',
            ram: asset.hardware_specs?.ram || '',
            storage: asset.hardware_specs?.storage || '',
            os: asset.hardware_specs?.os || ''
        }
    });
    formMode.value = 'edit';
    isFormOpen.value = true;
};

const handleSubmit = () => {
    const options = { onSuccess: () => isFormOpen.value = false };
    if (formMode.value === 'create') form.post(route('assets.store'), options);
    else form.put(route('assets.update', selectedAsset.value.id), options);
};

const openAssign = (asset: any) => {
    assigningAsset.value = asset;
    isAssignModalOpen.value = true;
};

const openReturn = (asset: any) => {
    returningAsset.value = asset;
    isReturnModalOpen.value = true;
};

const handleSync = async () => {
    if (!form.serial_number) return alert('Please enter Serial Number first.');
    isSyncing.value = true;
    try {
        const { data } = await axios.post(route('assets.sync'), { serial_number: form.serial_number });
        if (data.specs) {
            form.brand = data.specs.brand || form.brand;
            form.model = data.specs.model || form.model;
            if (data.specs.hardware_specs) Object.assign(form.hardware_specs, data.specs.hardware_specs);
        }
    } catch (e: any) {
        alert(e.response?.data?.errors?.serial_number || 'Sync Failed');
    } finally {
        isSyncing.value = false;
    }
};

const handleDelete = (id: number) => {
    if (confirm('Are you sure you want to delete this asset?')) router.delete(route('assets.destroy', id));
};

const handleExport = () => {
    // Use active filter params
    // Clean 'ALL' values before export
    const query: any = { ...params.value };
    if (query.status === 'ALL') delete query.status;
    if (query.loan_type === 'ALL') delete query.loan_type;
    if (query.year === 'ALL') delete query.year;

    const queryString = new URLSearchParams(query).toString();
    window.location.href = route('assets.export') + '?' + queryString;
};
</script>

<template>

    <Head title="Assets" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">

            <AssetStats :stats="stats" />

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Asset List</h2>
                    <p class="text-muted-foreground text-sm">Manage inventory and office asset loans.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="handleExport">
                        <Download class="mr-2 h-4 w-4" /> Export Excel
                    </Button>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> New Asset
                    </Button>
                </div>
            </div>

            <div class="bg-card border rounded-lg p-4 shadow-sm space-y-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-muted-foreground">
                    <Filter class="w-4 h-4" /> Filter Data
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    <div class="space-y-1">
                        <Label class="text-xs">Search</Label>
                        <div class="relative">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="params.search" type="search" placeholder="Search Assets..." class="pl-8" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label class="text-xs">Status</Label>
                        <Select v-model="params.status">
                            <SelectTrigger>
                                <SelectValue placeholder="All Statuses" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="ALL">All Statuses</SelectItem>
                                <SelectItem value="AVAILABLE">Available</SelectItem>
                                <SelectItem value="BORROWED">Borrowed</SelectItem>
                                <SelectItem value="MAINTENANCE">Maintenance</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <Label class="text-xs">Loan Type</Label>
                        <Select v-model="params.loan_type">
                            <SelectTrigger>
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="ALL">All Types</SelectItem>
                                <SelectItem value="LONG_TERM">Long Term (Fixed)</SelectItem>
                                <SelectItem value="SHORT_TERM">Short Term (Temporary)</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <Label class="text-xs">Purchase Year</Label>
                        <div class="flex gap-2">
                            <Select v-model="params.year">
                                <SelectTrigger class="flex-1">
                                    <SelectValue placeholder="All Years" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="ALL">All Years</SelectItem>
                                    <SelectItem v-for="year in years" :key="year" :value="String(year)">
                                        {{ year }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Button
                                v-if="params.search || params.status !== 'ALL' || params.loan_type !== 'ALL' || params.year !== 'ALL'"
                                variant="ghost" size="icon" @click="resetFilters" title="Reset Filters"
                                class="shrink-0 text-red-500 hover:text-red-700 hover:bg-red-50">
                                <X class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                </div>
            </div>

            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 bg-card overflow-hidden">
                <AssetTable :assets="assets"
                    @page-change="(url) => router.get(url, {}, { preserveState: true, preserveScroll: true })"
                    @edit="openEdit" @detail="(asset) => { selectedAsset = asset; isDetailOpen = true; }"
                    @delete="handleDelete" @assign="openAssign" @return="openReturn" />
            </div>
        </div>

        <AssetFormModal :show="isFormOpen" :mode="formMode" :form="form" :categories="categories" :locations="locations"
            :is-syncing="isSyncing" @close="isFormOpen = false" @submit="handleSubmit" @sync="handleSync" />

        <AssetDetailModal :show="isDetailOpen" :asset="selectedAsset" @close="isDetailOpen = false"
            @edit="() => { isDetailOpen = false; openEdit(selectedAsset); }" />

        <AssignModal :show="isAssignModalOpen" :asset="assigningAsset" :employees="employees"
            @close="isAssignModalOpen = false" />

        <ReturnModal :show="isReturnModalOpen" :asset="returningAsset" @close="isReturnModalOpen = false" />

    </AppLayout>
</template>