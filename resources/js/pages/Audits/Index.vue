<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, ClipboardList, AlertTriangle, CheckCircle2 } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge'; // Pastikan Badge diimport
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';
import AuditModal from '@/components/Audits/AuditModal.vue';
import { route } from 'ziggy-js';

const breadcrumbs = [{ title: 'Audit Assets', href: route('audits.index') }];

const props = defineProps<{
    assets: any;
    filters: { search: string };
}>();

const search = ref(props.filters.search || '');
const isAuditModalOpen = ref(false);
const selectedAsset = ref<any>(null);

// Search Watcher
watch(search, debounce((val: string) => {
    router.get(route('audits.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const openAudit = (asset: any) => {
    selectedAsset.value = asset;
    isAuditModalOpen.value = true;
};

const isOverdue = (date: string | null) => {
    if (!date) return true;
    const lastAudit = new Date(date);
    const threeMonthsAgo = new Date();
    threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
    return lastAudit < threeMonthsAgo;
};

const formatDate = (date: string) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getConditionColor = (condition: string) => {
    switch (condition) {
        case 'GOOD': return 'default'; 
        case 'BAD': return 'secondary'; 
        case 'BROKEN': return 'destructive'; 
        default: return 'outline';
    }
};
</script>

<template>
    <Head title="Audit Assets" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Asset Audit</h2>
                    <p class="text-muted-foreground">Verify physical asset existence and condition.</p>
                </div>
                <div class="bg-primary/10 p-3 rounded-full">
                    <ClipboardList class="w-6 h-6 text-primary" />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="relative w-full max-w-sm">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input v-model="search" placeholder="Search by Tag or Name..." class="pl-8" />
                </div>
            </div>

            <div class="rounded-md border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Asset Info</TableHead>
                            <TableHead>Location</TableHead>
                            <TableHead>Condition</TableHead> 
                            <TableHead>Last Audit</TableHead>
                            <TableHead>Audit Status</TableHead>
                            <TableHead class="text-right">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="asset in assets.data" :key="asset.id">
                            <TableCell>
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ asset.name }}</span>
                                    <span class="text-xs text-muted-foreground font-mono">{{ asset.asset_tag }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                {{ asset.location?.name || '-' }}
                            </TableCell>
                            
                            <TableCell>
                                <Badge v-if="asset.last_audit_condition" :variant="getConditionColor(asset.last_audit_condition)">
                                    {{ asset.last_audit_condition }}
                                </Badge>
                                <span v-else class="text-muted-foreground text-xs">-</span>
                            </TableCell>

                            <TableCell>
                                <span class="text-sm">{{ formatDate(asset.last_audit_date) }}</span>
                            </TableCell>
                            <TableCell>
                                <div v-if="isOverdue(asset.last_audit_date)" class="flex items-center text-amber-600 text-xs font-medium">
                                    <AlertTriangle class="w-3 h-3 mr-1" /> Overdue / Never
                                </div>
                                <div v-else class="flex items-center text-green-600 text-xs font-medium">
                                    <CheckCircle2 class="w-3 h-3 mr-1" /> Verified
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button size="sm" variant="outline" @click="openAudit(asset)">
                                    Verify
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="assets.data.length === 0">
                            <TableCell colspan="6" class="h-24 text-center text-muted-foreground">
                                No assets found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
            
            <div class="flex gap-2 justify-end mt-4">
                <Button :disabled="!assets.prev_page_url" @click="router.get(assets.prev_page_url)" variant="ghost">Prev</Button>
                <Button :disabled="!assets.next_page_url" @click="router.get(assets.next_page_url)" variant="ghost">Next</Button>
            </div>
        </div>

        <AuditModal 
            :show="isAuditModalOpen" 
            :asset="selectedAsset"
            @close="isAuditModalOpen = false"
        />
    </AppLayout>
</template>