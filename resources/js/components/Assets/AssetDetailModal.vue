<script setup lang="ts">
import {
    Cpu, HardDrive, MemoryStick, Monitor, History
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogDescription
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

// Helper warna status Aset
const getStatusColor = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'AVAILABLE': return 'bg-green-100 text-green-700 border-green-200';
        case 'BORROWED': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'MAINTENANCE': return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'LOST': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

// Helper warna aksi History
const getActionColor = (action: string) => {
    switch (action?.toLowerCase()) {
        case 'assign': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'return': return 'bg-green-50 text-green-700 border-green-200';
        case 'maintenance': return 'bg-orange-50 text-orange-700 border-orange-200';
        default: return 'bg-gray-50 text-gray-600 border-gray-200';
    }
};

// Helper format tanggal
const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const props = defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close', 'edit']);
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[500px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Asset Details</DialogTitle>
            </DialogHeader>

            <div v-if="asset" class="grid gap-6 py-4">
                <div class="flex items-start gap-4">
                    <div class="h-24 w-24 bg-white rounded-lg border p-1 shadow-sm shrink-0">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${asset.asset_tag}`"
                            alt="QR Code" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ asset.name }}</h3>
                        <p class="text-sm text-muted-foreground mb-1">{{ asset.asset_tag }}</p>
                        <span
                            :class="['px-2 py-0.5 rounded-full text-xs font-medium border', getStatusColor(asset.status)]">
                            {{ asset.status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div>
                        <span class="text-xs text-muted-foreground block">Serial Number</span>
                        <span class="font-medium">{{ asset.serial_number || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Category</span>
                        <span class="font-medium">{{ asset.category?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Brand</span>
                        <span class="font-medium">{{ asset.brand || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Model</span>
                        <span class="font-medium">{{ asset.model || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Location</span>
                        <span class="font-medium">{{ asset.location?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Last Seen</span>
                        <span class="font-medium">
                            {{ asset.last_seen_at ? formatDate(asset.last_seen_at) : 'Never' }}
                        </span>
                    </div>
                </div>

                <div v-if="asset.hardware_specs" class="border-t pt-4">
                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
                        Hardware Specifications
                    </h4>
                    <div class="grid gap-3 text-sm">
                        <div class="flex items-center gap-3">
                            <Cpu class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-xs text-muted-foreground">Processor</span>
                                <span class="font-medium">{{ asset.hardware_specs.cpu || 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <MemoryStick class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-xs text-muted-foreground">RAM</span>
                                <span class="font-medium">{{ asset.hardware_specs.ram || 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <HardDrive class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-xs text-muted-foreground">Storage</span>
                                <span class="font-medium">{{ asset.hardware_specs.storage || 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <Monitor class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-xs text-muted-foreground">OS</span>
                                <span class="font-medium">{{ asset.hardware_specs.os || 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <History class="h-4 w-4 text-muted-foreground" />
                        <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Usage History
                        </h4>
                    </div>

                    <div v-if="asset.histories && asset.histories.length > 0"
                        class="space-y-3 max-h-[150px] overflow-y-auto pr-2 custom-scrollbar">

                        <div v-for="history in asset.histories" :key="history.id"
                            class="flex justify-between items-start text-sm border-b pb-2 last:border-0">

                            <div>
                                <div class="font-medium">
                                    {{ history.employee ? history.employee.name : 'Unknown / Warehouse' }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    PIC: {{ history.user ? history.user.name : 'System' }}
                                </div>
                                <div v-if="history.notes" class="text-xs text-muted-foreground italic mt-0.5">
                                    "{{ history.notes }}"
                                </div>
                            </div>

                            <div class="text-right shrink-0 ml-2">
                                <div class="text-xs text-muted-foreground mb-1">
                                    {{ formatDate(history.created_at) }}
                                </div>
                                <span
                                    :class="['text-[10px] px-1.5 py-0.5 rounded border capitalize', getActionColor(history.action)]">
                                    {{ history.action }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-sm text-muted-foreground text-center py-2 bg-muted/30 rounded">
                        No history available.
                    </div>
                </div>

            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Close</Button>
                <Button @click="emit('edit')">Edit Asset</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
/* Opsional: Style untuk scrollbar agar lebih rapi */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
</style>