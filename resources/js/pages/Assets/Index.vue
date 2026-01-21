<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import { Search, Plus } from 'lucide-vue-next';
import { route } from 'ziggy-js';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

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
}>();

const breadcrumbs = [{ title: 'Assets', href: route('assets.index') }];

const search = ref(props.filters.search || '');
const isFormOpen = ref(false);
const formMode = ref<'create' | 'edit'>('create');
const isDetailOpen = ref(false);
const selectedAsset = ref<any>(null);
const isSyncing = ref(false);

const isAssignModalOpen = ref(false);
const assigningAsset = ref<any>(null);
const isReturnModalOpen = ref(false);
const returningAsset = ref<any>(null);

watch(search, debounce((val: string) => {
    router.get(route('assets.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const form = useForm({
    asset_tag: '', serial_number: '', name: '', brand: '', model: '',
    category_id: null, location_id: null, status: 'AVAILABLE',
    hardware_specs: { cpu: '', ram: '', storage: '', os: '' }
});

const openCreate = () => {
    form.reset(); form.clearErrors();
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
    if (!form.serial_number) return alert('Enter Serial Number first.');
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
    if (confirm('Delete this asset?')) router.delete(route('assets.destroy', id));
};
</script>

<template>
    <Head title="Assets" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            
            <AssetStats :stats="stats" />

            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-card">
                
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" type="search" placeholder="Search Assets..." class="pl-8" />
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> New Asset
                    </Button>
                </div>

                <AssetTable 
                    :assets="assets"
                    @detail="(asset) => { selectedAsset = asset; isDetailOpen = true; }"
                    @edit="openEdit"
                    @delete="handleDelete"
                    @assign="openAssign"
                    @return="openReturn"
                    @page-change="(url) => router.get(url)"
                />
            </div>
        </div>

        <AssetFormModal 
            :show="isFormOpen" 
            :mode="formMode" 
            :form="form" 
            :categories="categories" 
            :locations="locations" 
            :is-syncing="isSyncing"
            @close="isFormOpen = false" 
            @submit="handleSubmit" 
            @sync="handleSync" 
        />

        <AssetDetailModal 
            :show="isDetailOpen" 
            :asset="selectedAsset" 
            @close="isDetailOpen = false" 
            @edit="() => { isDetailOpen = false; openEdit(selectedAsset); }" 
        />

        <AssignModal 
            :show="isAssignModalOpen" 
            :asset="assigningAsset"
            :employees="employees"
            @close="isAssignModalOpen = false"
        />

        <ReturnModal 
            :show="isReturnModalOpen" 
            :asset="returningAsset"
            @close="isReturnModalOpen = false"
        />

    </AppLayout>
</template>