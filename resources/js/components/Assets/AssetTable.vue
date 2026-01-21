<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    MoreHorizontal, Eye, FileEdit, Trash2, Printer, ArrowUpRightFromCircle, CircleArrowOutDownLeft
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

const selectedIds = ref<string[]>([]);

const isSelected = (id: any) => {
    return selectedIds.value.includes(String(id));
};

const isAllSelected = computed(() => {
    if (props.assets.data.length === 0) return false;
    return props.assets.data.every(asset => isSelected(asset.id));
});

const toggleSelectAll = (e: Event) => {
    const isChecked = (e.target as HTMLInputElement).checked;

    const pageIds = props.assets.data.map(a => String(a.id));

    if (isChecked) {
        pageIds.forEach(id => {
            if (!selectedIds.value.includes(id)) {
                selectedIds.value.push(id);
            }
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

watch(() => props.assets.data, () => {
    selectedIds.value = [];
});

const printBatch = () => {
    if (selectedIds.value.length === 0) return;

    const params = new URLSearchParams();
    selectedIds.value.forEach(id => params.append('ids[]', id));

    const url = route('assets.print-batch') + '?' + params.toString();
    window.open(url, '_blank');
};

const getStatusVariant = (status: string) => {
    switch (status?.toUpperCase()) {
        case 'AVAILABLE': return 'default';
        case 'MAINTENANCE': return 'destructive';
        case 'BORROWED': return 'secondary';
        default: return 'outline';
    }
};
</script>

<template>
    <div v-if="selectedIds.length > 0"
        class="bg-primary/10 border border-primary/20 p-2 mb-2 rounded-md flex justify-between items-center animate-in fade-in slide-in-from-top-1">
        <span class="text-sm font-medium ml-2 text-primary">
            {{ selectedIds.length }} selected item
        </span>
        <Button size="sm" variant="default" @click="printBatch">
            <Printer class="mr-2 h-4 w-4" />
            Print {{ selectedIds.length }} Sticker
        </Button>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[40px] pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isAllSelected" @change="toggleSelectAll" />
                    </TableHead>
                    <TableHead class="w-[50px]">QR</TableHead>
                    <TableHead>Asset Info</TableHead>
                    <TableHead>Model/Brand</TableHead>
                    <TableHead>Location</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="asset in assets.data" :key="asset.id" :class="{ 'bg-muted/50': isSelected(asset.id) }"
                    class="transition-colors hover:bg-muted/30">

                    <TableCell class="pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isSelected(asset.id)" @change="toggleSelection(asset.id)" />
                    </TableCell>

                    <TableCell>
                        <div class="h-9 w-9 bg-white rounded border p-0.5">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${asset.asset_tag}`"
                                class="w-full h-full object-contain" />
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex flex-col">
                            <span class="font-medium truncate max-w-[180px]">{{ asset.name }}</span>
                            <span class="text-xs text-muted-foreground font-mono">{{ asset.asset_tag }}</span>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex flex-col text-sm">
                            <span>{{ asset.model }}</span>
                            <span class="text-xs text-muted-foreground">{{ asset.brand }}</span>
                        </div>
                    </TableCell>
                    <TableCell>
                        {{ asset.location?.name || '-' }}
                    </TableCell>
                    <TableCell>
                        <Badge :variant="getStatusVariant(asset.status)">
                            {{ asset.status }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0">
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem @click="emit('detail', asset)">
                                    <Eye class="mr-2 h-4 w-4" /> Detail
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="emit('edit', asset)">
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
                                    class="text-red-600 focus:text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                                <DropdownMenuItem v-if="asset.status === 'AVAILABLE'" @click="emit('assign', asset)"
                                    class="text-blue-600 focus:text-blue-600 cursor-pointer">
                                    <ArrowUpRightFromCircle class="mr-2 h-4 w-4" /> Check Out / Assign
                                </DropdownMenuItem>

                                <DropdownMenuItem v-if="asset.status === 'BORROWED'" @click="emit('return', asset)"
                                    class="text-orange-600 focus:text-orange-600 cursor-pointer">
                                    <CircleArrowOutDownLeft  class="mr-2 h-4 w-4" /> Return / Check In
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
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

    <div class="flex items-center justify-between p-4 border-t" v-if="assets.total > 0">
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