<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    MoreHorizontal, Eye, FileEdit, Trash2, Printer,
    ArrowUpRightFromCircle, CircleArrowOutDownLeft,
    ShieldCheck, ShieldAlert, CalendarClock, User
} from 'lucide-vue-next';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem,
    DropdownMenuLabel, DropdownMenuTrigger, DropdownMenuSeparator
} from '@/components/ui/dropdown-menu';
import { route } from 'ziggy-js';

const props = defineProps<{
    assets: {
        data: any[], links: any[],
        prev_page_url: string, next_page_url: string,
        from: number, to: number, total: number
    };
}>();

const emit = defineEmits(['edit', 'detail', 'delete', 'assign', 'return', 'page-change']);

// --- SELECTION LOGIC ---
const selectedIds = ref<string[]>([]);

const isSelected = (id: any) => selectedIds.value.includes(String(id));

const isAllSelected = computed(() => {
    if (props.assets.data.length === 0) return false;
    return props.assets.data.every(asset => isSelected(asset.id));
});

const toggleSelectAll = (e: Event) => {
    const isChecked = (e.target as HTMLInputElement).checked;
    const pageIds = props.assets.data.map(a => String(a.id));
    if (isChecked) {
        pageIds.forEach(id => {
            if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
        });
    } else {
        selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    }
};

const toggleSelection = (rawId: any) => {
    const id = String(rawId);
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(itemId => itemId !== id);
    } else {
        selectedIds.value.push(id);
    }
};

watch(() => props.assets.data, () => { selectedIds.value = []; });

const printBatch = () => {
    if (selectedIds.value.length === 0) return;
    const params = new URLSearchParams();
    selectedIds.value.forEach(id => params.append('ids[]', id));
    window.open(route('assets.print-batch') + '?' + params.toString(), '_blank');
};

// --- HELPERS ---
const getStatusVariant = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'AVAILABLE': return 'default';
        case 'MAINTENANCE': return 'destructive';
        case 'BORROWED': return 'secondary';
        default: return 'outline';
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    // Changed locale to en-US
    return new Date(dateString).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
};

const isOverdue = (dateString: string) => {
    if (!dateString) return false;
    return new Date(dateString) < new Date();
};
</script>

<template>
    <div v-if="selectedIds.length > 0"
        class="bg-primary/10 border border-primary/20 p-2 mb-2 rounded-md flex justify-between items-center animate-in fade-in slide-in-from-top-1">
        <span class="text-sm font-medium ml-2 text-primary">
            {{ selectedIds.length }} items selected
        </span>
        <Button size="sm" variant="default" @click="printBatch">
            <Printer class="mr-2 h-4 w-4" />
            Print {{ selectedIds.length }} Stickers
        </Button>
    </div>

    <div class="rounded-md border bg-card text-card-foreground">
        <Table>
            <TableHeader>
                <TableRow class="hover:bg-transparent">
                    <TableHead class="w-[40px] pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isAllSelected" @change="toggleSelectAll" />
                    </TableHead>
                    <TableHead class="w-[60px]">QR</TableHead>
                    <TableHead>Asset Info</TableHead>
                    <TableHead>Model/Brand</TableHead>
                    <TableHead>Period</TableHead>
                    <TableHead>Location</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="asset in assets.data" :key="asset.id" 
                    :class="{ 'bg-muted/50': isSelected(asset.id) }"
                    class="transition-colors hover:bg-muted/40">

                    <TableCell class="pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isSelected(asset.id)" @change="toggleSelection(asset.id)" />
                    </TableCell>

                    <TableCell>
                        <div class="h-10 w-10 bg-white rounded-md border p-0.5 flex items-center justify-center overflow-hidden">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${asset.asset_tag}`"
                                class="w-full h-full object-contain" alt="QR" loading="lazy" />
                        </div>
                    </TableCell>

                    <TableCell>
                        <div class="flex flex-col">
                            <span class="font-medium truncate max-w-[180px] text-foreground" :title="asset.name">
                                {{ asset.name }}
                            </span>
                            <span class="text-xs text-muted-foreground font-mono">{{ asset.asset_tag }}</span>
                            <span v-if="asset.vendor"
                                class="text-[10px] text-muted-foreground mt-0.5 flex items-center gap-1">
                                Vendor: {{ asset.vendor }}
                            </span>
                        </div>
                    </TableCell>

                    <TableCell>
                        <div class="flex flex-col text-sm">
                            <span class="text-foreground">{{ asset.model || '-' }}</span>
                            <span class="text-xs text-muted-foreground">{{ asset.brand || '-' }}</span>
                        </div>
                    </TableCell>

                    <TableCell>
                        <div v-if="asset.expiry_year" class="flex items-center gap-1.5">
                            <template v-if="asset.is_assets_active">
                                <ShieldCheck class="w-4 h-4 text-emerald-500" />
                                <div class="flex flex-col">
                                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Active</span>
                                    <span class="text-[10px] text-muted-foreground">until {{ asset.expiry_year }}</span>
                                </div>
                            </template>
                            <template v-else>
                                <ShieldAlert class="w-4 h-4 text-rose-500" />
                                <div class="flex flex-col">
                                    <span class="text-xs text-rose-600 dark:text-rose-400">Expired</span>
                                    <span class="text-[10px] text-muted-foreground">{{ asset.expiry_year }}</span>
                                </div>
                            </template>
                        </div>
                        <span v-else class="text-xs text-muted-foreground">-</span>
                    </TableCell>

                    <TableCell>
                        <span class="text-sm">{{ asset.location?.name || '-' }}</span>
                    </TableCell>

                    <TableCell>
                        <div class="flex flex-col gap-1 items-start">
                            <Badge :variant="getStatusVariant(asset.status)">
                                {{ asset.status }}
                            </Badge>

                            <div v-if="asset.status === 'BORROWED'"
                                class="flex flex-col mt-1 bg-muted/40 p-2 rounded border border-border w-fit">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <User class="w-3 h-3 text-muted-foreground" />
                                    <span class="text-xs font-medium truncate max-w-[120px] text-foreground">
                                        {{ asset.employee?.name || 'Unknown' }}
                                    </span>
                                </div>

                                <div v-if="asset.loan_type === 'SHORT_TERM' && asset.due_date"
                                    class="flex items-center gap-1.5 pt-1 border-t border-border mt-1"
                                    :class="isOverdue(asset.due_date) ? 'text-red-600 dark:text-red-400 font-bold' : 'text-muted-foreground'">
                                    <CalendarClock class="w-3 h-3" />
                                    <span class="text-[10px]">{{ formatDate(asset.due_date) }}</span>
                                </div>
                            </div>
                        </div>
                    </TableCell>

                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0 hover:bg-muted">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>

                                <DropdownMenuItem @click="emit('detail', asset)" class="cursor-pointer">
                                    <Eye class="mr-2 h-4 w-4" /> Details
                                </DropdownMenuItem>

                                <DropdownMenuItem @click="emit('edit', asset)" class="cursor-pointer">
                                    <FileEdit class="mr-2 h-4 w-4" /> Edit
                                </DropdownMenuItem>

                                <DropdownMenuItem as-child>
                                    <a :href="route('assets.print-label', asset.id)" target="_blank"
                                        class="flex items-center cursor-pointer w-full">
                                        <Printer class="mr-2 h-4 w-4" /> Print Sticker
                                    </a>
                                </DropdownMenuItem>

                                <DropdownMenuSeparator />

                                <DropdownMenuItem @click="emit('delete', asset.id)"
                                    class="text-red-600 focus:text-red-600 dark:text-red-400 dark:focus:text-red-400 cursor-pointer">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>

                                <DropdownMenuItem v-if="asset.status === 'AVAILABLE'" @click="emit('assign', asset)"
                                    class="text-blue-600 dark:text-blue-400 focus:text-blue-600 dark:focus:text-blue-400 cursor-pointer bg-blue-50 dark:bg-blue-900/20 mt-1">
                                    <ArrowUpRightFromCircle class="mr-2 h-4 w-4" /> Check Out / Assign
                                </DropdownMenuItem>

                                <DropdownMenuItem v-if="asset.status === 'BORROWED'" @click="emit('return', asset)"
                                    class="text-orange-600 dark:text-orange-400 focus:text-orange-600 dark:focus:text-orange-400 cursor-pointer bg-orange-50 dark:bg-orange-900/20 mt-1">
                                    <CircleArrowOutDownLeft class="mr-2 h-4 w-4" /> Return / Check In
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>

                <TableRow v-if="assets.data.length === 0">
                    <TableCell colspan="8" class="h-48">
                        <div class="flex flex-col items-center justify-center text-muted-foreground w-full h-full">
                            <span class="text-lg font-medium">No assets found</span>
                            <span class="text-sm">Try adjusting your search filters.</span>
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <div class="flex items-center justify-between p-4 border-t border-border" v-if="assets.total > 0">
        <div class="text-sm text-muted-foreground">
            Showing {{ assets.from }} to {{ assets.to }} of {{ assets.total }} entries
        </div>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!assets.prev_page_url"
                @click="emit('page-change', assets.prev_page_url)">
                Previous
            </Button>
            <Button variant="outline" size="sm" :disabled="!assets.next_page_url"
                @click="emit('page-change', assets.next_page_url)">
                Next
            </Button>
        </div>
    </div>
</template>