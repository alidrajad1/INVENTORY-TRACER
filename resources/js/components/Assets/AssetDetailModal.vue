<script setup lang="ts">
import { computed } from 'vue';
import {
    Cpu, HardDrive, MemoryStick, Monitor, History,
    ShoppingBag, ShieldCheck, ShieldAlert, CalendarClock, User
} from 'lucide-vue-next';
import {
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close', 'edit']);

const getStatusColor = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'AVAILABLE': return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800';
        case 'BORROWED': return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800';
        case 'MAINTENANCE': return 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800';
        case 'LOST': return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
        default: return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
    }
};

const getActionColor = (action: string) => {
    switch (action?.toLowerCase()) {
        case 'assign': return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800';
        case 'return': return 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800';
        case 'maintenance': return 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800';
        default: return 'bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-800/50 dark:text-gray-400 dark:border-gray-700';
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    // Changed to en-US for English formatting
    return new Date(dateString).toLocaleDateString('en-US', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

// --- WARRANTY LOGIC ---
const warrantyInfo = computed(() => {
    if (!props.asset?.purchase_year || !props.asset?.period) return null;

    const start = parseInt(props.asset.purchase_year);
    const duration = parseInt(props.asset.period);
    const expiryYear = start + duration;
    const currentYear = new Date().getFullYear();
    const isActive = currentYear <= expiryYear;

    return { expiryYear, isActive };
});
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Asset Details</DialogTitle>
            </DialogHeader>

            <div v-if="asset" class="grid gap-6 py-4">

                <div class="flex items-start gap-4">
                    <div
                        class="h-24 w-24 bg-white dark:bg-secondary rounded-lg border p-1 shadow-sm shrink-0 flex items-center justify-center">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${asset.asset_tag}`"
                            alt="QR Code"
                            class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal dark:invert" />
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg text-foreground">{{ asset.name }}</h3>
                                <p class="text-sm text-muted-foreground mb-2">{{ asset.asset_tag }}</p>
                            </div>
                            <span
                                :class="['px-2.5 py-1 rounded-full text-xs font-bold border shadow-sm', getStatusColor(asset.status)]">
                                {{ asset.status }}
                            </span>
                        </div>

                        <div v-if="asset.status === 'BORROWED'"
                            class="mt-2 bg-blue-50 dark:bg-blue-900/20 p-2 rounded-md border border-blue-100 dark:border-blue-800 text-sm">
                            <div class="flex items-center gap-2 mb-1">
                                <User class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span class="font-semibold text-blue-800 dark:text-blue-300">{{ asset.employee?.name ||
                                    'Unknown User' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400">
                                <span class="uppercase font-bold tracking-wider">{{ asset.loan_type === 'SHORT_TERM' ?
                                    'Short Term Loan' : 'Fixed Inventory' }}</span>
                                <span v-if="asset.loan_type === 'SHORT_TERM' && asset.due_date"
                                    class="flex items-center gap-1 ml-2">
                                    <CalendarClock class="w-3 h-3" />
                                    Due: <strong>{{ formatDate(asset.due_date) }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm border-t border-border pt-4">
                    <div>
                        <span class="text-xs text-muted-foreground block">Serial Number</span>
                        <span class="font-medium font-mono text-foreground">{{ asset.serial_number || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Category</span>
                        <span class="font-medium text-foreground">{{ asset.category?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Brand / Model</span>
                        <span class="font-medium text-foreground">{{ asset.brand }} / {{ asset.model }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-muted-foreground block">Location</span>
                        <span class="font-medium text-foreground">{{ asset.location?.name || '-' }}</span>
                    </div>
                </div>

                <div class="border border-border rounded-md bg-muted/30 p-3 mt-1">
                    <div class="flex items-center gap-2 mb-3 border-b border-border pb-2">
                        <ShoppingBag class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                        <h4 class="text-xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">
                            Procurement
                        </h4>
                    </div>

                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-[10px] uppercase text-muted-foreground block mb-0.5">Vendor</span>
                            <span class="font-medium text-foreground">{{ asset.vendor || '-' }}</span>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase text-muted-foreground block mb-0.5">Purchase Year</span>
                            <span class="font-medium text-foreground">{{ asset.purchase_year || '-' }}</span>
                        </div>

                        <div>
                            <span class="text-[10px] uppercase text-muted-foreground block mb-0.5">Status</span>
                            <div v-if="warrantyInfo">
                                <Badge v-if="warrantyInfo.isActive" variant="outline"
                                    class="bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 gap-1 pl-1 pr-2">
                                    <ShieldCheck class="w-3 h-3" />
                                    Active until {{ warrantyInfo.expiryYear }}
                                </Badge>
                                <Badge v-else variant="outline"
                                    class="bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800 gap-1 pl-1 pr-2">
                                    <ShieldAlert class="w-3 h-3" />
                                    Expired {{ warrantyInfo.expiryYear }}
                                </Badge>
                            </div>
                            <span v-else class="text-muted-foreground text-xs">-</span>
                        </div>
                    </div>
                </div>

                <div v-if="asset.hardware_specs" class="border-t border-border pt-4">
                    <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">
                        Hardware Specifications
                    </h4>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-3 bg-card border border-border p-2 rounded">
                            <Cpu class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-[10px] text-muted-foreground">Processor</span>
                                <span class="font-medium text-xs text-foreground">{{ asset.hardware_specs.cpu || '-'
                                    }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-card border border-border p-2 rounded">
                            <MemoryStick class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-[10px] text-muted-foreground">RAM</span>
                                <span class="font-medium text-xs text-foreground">{{ asset.hardware_specs.ram || '-'
                                    }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-card border border-border p-2 rounded">
                            <HardDrive class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-[10px] text-muted-foreground">Storage</span>
                                <span class="font-medium text-xs text-foreground">{{ asset.hardware_specs.storage || '-'
                                    }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-card border border-border p-2 rounded">
                            <Monitor class="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span class="block text-[10px] text-muted-foreground">OS</span>
                                <span class="font-medium text-xs text-foreground">{{ asset.hardware_specs.os || '-'
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <History class="h-4 w-4 text-muted-foreground" />
                        <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Activity History (Recent)
                        </h4>
                    </div>

                    <div v-if="asset.histories && asset.histories.length > 0"
                        class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">

                        <div v-for="history in asset.histories" :key="history.id"
                            class="flex justify-between items-start text-sm border-b border-border pb-2 last:border-0 hover:bg-muted/30 p-1 rounded transition-colors">

                            <div>
                                <div class="font-medium text-foreground">
                                    {{ history.employee ? history.employee.name : 'Warehouse / Admin' }}
                                </div>
                                <div class="text-[10px] text-muted-foreground flex gap-1">
                                    <span>By: {{ history.user ? history.user.name : 'System' }}</span>
                                    <span>•</span>
                                    <span>{{ formatDate(history.created_at) }}</span>
                                </div>
                                <div v-if="history.notes" class="text-xs text-muted-foreground italic mt-0.5">
                                    "{{ history.notes }}"
                                </div>
                            </div>

                            <div class="text-right shrink-0 ml-2 flex flex-col items-end gap-1">
                                <span class="text-[10px] text-muted-foreground">{{ formatDate(history.created_at)
                                    }}</span>

                                <span
                                    :class="['text-[10px] px-2 py-0.5 rounded border capitalize font-medium', getActionColor(history.action)]">
                                    {{ history.action }}
                                </span>

                                <span v-if="history.condition" :class="{
                                    'text-green-600 bg-green-50 border-green-200': history.condition === 'GOOD',
                                    'text-yellow-600 bg-yellow-50 border-yellow-200': history.condition === 'BAD',
                                    'text-red-600 bg-red-50 border-red-200': history.condition === 'BROKEN'
                                }" class="text-[10px] px-1.5 py-0.5 rounded border font-semibold">
                                    {{ history.condition }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else
                        class="text-sm text-muted-foreground text-center py-4 bg-muted/20 rounded border border-dashed border-border">
                        No activity history found.
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
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    /* Using Shadcn native CSS variables (supports dark mode automatically) */
    background-color: hsl(var(--border));
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    /* Slightly darker on hover */
    background-color: hsl(var(--muted-foreground));
    opacity: 0.5;
}
</style>