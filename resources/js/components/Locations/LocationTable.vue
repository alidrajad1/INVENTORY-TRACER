<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    MoreHorizontal, Pencil, Trash2, MapPin, Building2 
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

// --- PROPS ---
const props = defineProps<{
    locations: { 
        data: any[], 
        prev_page_url: string, next_page_url: string, 
        from: number, to: number, total: number 
    };
}>();

const emit = defineEmits(['edit', 'delete', 'page-change']);

// --- LOGIC CHECKBOX (Sama dengan Category & Asset) ---
const selectedIds = ref<string[]>([]);
const isSelected = (id: any) => selectedIds.value.includes(String(id));

// 1. Computed: Select All
const isAllSelected = computed(() => {
    if (props.locations.data.length === 0) return false;
    return props.locations.data.every(loc => isSelected(loc.id));
});

// 2. Action: Toggle Select All
const toggleSelectAll = (e: Event) => {
    const isChecked = (e.target as HTMLInputElement).checked;
    const pageIds = props.locations.data.map(l => String(l.id));

    if (isChecked) {
        pageIds.forEach(id => {
            if (!selectedIds.value.includes(id)) selectedIds.value.push(id);
        });
    } else {
        selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    }
};

// 3. Action: Toggle Single
const toggleSelection = (rawId: any) => {
    const id = String(rawId);
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(item => item !== id);
    } else {
        selectedIds.value.push(id);
    }
};

// Reset saat ganti halaman
watch(() => props.locations.data, () => {
    selectedIds.value = [];
});
</script>

<template>
    <div v-if="selectedIds.length > 0" class="bg-primary/10 border border-primary/20 p-2 mb-2 rounded-md flex justify-between items-center animate-in fade-in slide-in-from-top-1">
        <span class="text-sm font-medium ml-2 text-primary">
            {{ selectedIds.length }} selected item
        </span>
        <Button size="sm" variant="destructive">
            <Trash2 class="mr-2 h-4 w-4" /> Delete Selected Item
        </Button>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[40px] pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isAllSelected"
                            @change="toggleSelectAll"
                        />
                    </TableHead>
                    <TableHead>Location Name</TableHead>
                    <TableHead>Building</TableHead>
                    <TableHead class="text-center">Total Assets</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="location in locations.data" :key="location.id" 
                    :class="{'bg-muted/50': isSelected(location.id)}"
                    class="transition-colors hover:bg-muted/30"
                >
                    <TableCell class="pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isSelected(location.id)"
                            @change="toggleSelection(location.id)"
                        />
                    </TableCell>

                    <TableCell>
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-muted rounded-md">
                                <MapPin class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <span class="font-medium">{{ location.name }}</span>
                        </div>
                    </TableCell>

                    <TableCell>
                        <div class="flex items-center gap-2 text-muted-foreground">
                            <Building2 class="h-3 w-3" />
                            <span>{{ location.building }}</span>
                        </div>
                    </TableCell>

                    <TableCell class="text-center">
                        <Badge variant="secondary" class="font-mono">
                            {{ location.assets_count }} Items
                        </Badge>
                    </TableCell>

                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem @click="emit('edit', location)">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="emit('delete', location)" class="text-red-600 focus:text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>

                <TableRow v-if="locations.data.length === 0">
                    <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                        No locations found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <div class="flex items-center justify-between p-4 border-t" v-if="locations.total > 0">
        <div class="text-sm text-muted-foreground">
            Showing {{ locations.from }} to {{ locations.to }} of {{ locations.total }} entries
        </div>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!locations.prev_page_url" 
                @click="emit('page-change', locations.prev_page_url)">
                Previous
            </Button>
            <Button variant="outline" size="sm" :disabled="!locations.next_page_url" 
                @click="emit('page-change', locations.next_page_url)">
                Next
            </Button>
        </div>
    </div>
</template>