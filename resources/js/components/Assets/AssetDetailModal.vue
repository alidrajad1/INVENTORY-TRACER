<script setup lang="ts">
import { Cpu, HardDrive, MemoryStick, Monitor } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

// Helper warna bisa ditaruh di utils terpisah, tapi disini ok
const getStatusColor = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'AVAILABLE': return 'bg-green-100 text-green-700 border-green-200';
        case 'BORROWED': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'MAINTENANCE': return 'bg-orange-100 text-orange-700 border-orange-200';
        case 'LOST': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};

defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close', 'edit']);
</script>

<template>
    <Dialog :open="show" @update:open="(val:boolean) => !val && emit('close')">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Asset Details</DialogTitle>
            </DialogHeader>
            
            <div v-if="asset" class="grid gap-6 py-4">
                <div class="flex items-start gap-4">
                    <div class="h-24 w-24 bg-white rounded-lg border p-1 shadow-sm shrink-0">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${asset.asset_tag}`"
                            alt="QR" class="w-full h-full object-contain" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ asset.name }}</h3>
                        <p class="text-sm text-muted-foreground mb-1">{{ asset.asset_tag }}</p>
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium border', getStatusColor(asset.status)]">
                            {{ asset.status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div><span class="text-xs text-muted-foreground block">Serial Number</span><span class="font-medium">{{ asset.serial_number }}</span></div>
                    <div><span class="text-xs text-muted-foreground block">Category</span><span class="font-medium">{{ asset.category?.name || '-' }}</span></div>
                    <div><span class="text-xs text-muted-foreground block">Brand</span><span class="font-medium">{{ asset.brand || '-' }}</span></div>
                    <div><span class="text-xs text-muted-foreground block">Model</span><span class="font-medium">{{ asset.model || '-' }}</span></div>
                    <div><span class="text-xs text-muted-foreground block">Location</span><span class="font-medium">{{ asset.location?.name || '-' }}</span></div>
                    <div><span class="text-xs text-muted-foreground block">Last Seen</span><span class="font-medium">{{ asset.last_seen_at ? new Date(asset.last_seen_at).toLocaleDateString() : 'Never' }}</span></div>
                </div>

                <div v-if="asset.hardware_specs" class="border-t pt-4">
                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">Hardware Specifications</h4>
                    <div class="grid gap-3 text-sm">
                        <div class="flex items-center gap-3"><Cpu class="h-4 w-4 text-muted-foreground" /><div><span class="block text-xs text-muted-foreground">Processor</span><span class="font-medium">{{ asset.hardware_specs.cpu || 'N/A' }}</span></div></div>
                        <div class="flex items-center gap-3"><MemoryStick class="h-4 w-4 text-muted-foreground" /><div><span class="block text-xs text-muted-foreground">RAM</span><span class="font-medium">{{ asset.hardware_specs.ram || 'N/A' }}</span></div></div>
                        <div class="flex items-center gap-3"><HardDrive class="h-4 w-4 text-muted-foreground" /><div><span class="block text-xs text-muted-foreground">Storage</span><span class="font-medium">{{ asset.hardware_specs.storage || 'N/A' }}</span></div></div>
                        <div class="flex items-center gap-3"><Monitor class="h-4 w-4 text-muted-foreground" /><div><span class="block text-xs text-muted-foreground">OS</span><span class="font-medium">{{ asset.hardware_specs.os || 'N/A' }}</span></div></div>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="secondary" @click="emit('close')">Close</Button>
                <Button @click="emit('edit')">Edit</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>