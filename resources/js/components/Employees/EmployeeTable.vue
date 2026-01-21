<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    MoreHorizontal, Pencil, Trash2, User, Eye, BadgeCheck, Ban
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

const props = defineProps<{
    employees: { 
        data: any[], prev_page_url: string, next_page_url: string, 
        from: number, to: number, total: number 
    };
}>();

const emit = defineEmits(['detail', 'edit', 'delete', 'page-change']);

const selectedIds = ref<string[]>([]);
const isSelected = (id: any) => selectedIds.value.includes(String(id));

const isAllSelected = computed(() => {
    if (props.employees.data.length === 0) return false;
    return props.employees.data.every(e => isSelected(e.id));
});

const toggleSelectAll = (e: Event) => {
    const isChecked = (e.target as HTMLInputElement).checked;
    const pageIds = props.employees.data.map(e => String(e.id));
    if (isChecked) {
        pageIds.forEach(id => { if (!selectedIds.value.includes(id)) selectedIds.value.push(id); });
    } else {
        selectedIds.value = selectedIds.value.filter(id => !pageIds.includes(id));
    }
};

const toggleSelection = (rawId: any) => {
    const id = String(rawId);
    if (selectedIds.value.includes(id)) selectedIds.value = selectedIds.value.filter(i => i !== id);
    else selectedIds.value.push(id);
};

watch(() => props.employees.data, () => selectedIds.value = []);
</script>

<template>
    <div v-if="selectedIds.length > 0" class="bg-primary/10 border border-primary/20 p-2 mb-2 rounded-md flex justify-between items-center animate-in fade-in slide-in-from-top-1">
        <span class="text-sm font-medium ml-2 text-primary">{{ selectedIds.length }} selected</span>
        <Button size="sm" variant="destructive"><Trash2 class="mr-2 h-4 w-4" /> Bulk Delete</Button>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[40px] pl-4">
                        <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer accent-primary" 
                            :checked="isAllSelected" @change="toggleSelectAll" />
                    </TableHead>
                    <TableHead>NID</TableHead>
                    <TableHead>Employee Info</TableHead>
                    <TableHead>Position / Dept</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-center">Assets</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="emp in employees.data" :key="emp.id" :class="{'bg-muted/50': isSelected(emp.id)}" class="hover:bg-muted/30">
                    <TableCell class="pl-4">
                        <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer accent-primary" 
                            :checked="isSelected(emp.id)" @change="toggleSelection(emp.id)" />
                    </TableCell>

                    <TableCell>
                        <Badge variant="outline" class="font-mono">{{ emp.nid }}</Badge>
                    </TableCell>

                    <TableCell>
                        <div class="flex items-center gap-3">
                            <div class="bg-muted p-2 rounded-full">
                                <User class="h-4 w-4 text-muted-foreground" />
                            </div>
                            <div class="flex flex-col">
                                <span class="font-medium text-sm">{{ emp.name }}</span>
                                <span class="text-xs text-muted-foreground">{{ emp.email }}</span>
                            </div>
                        </div>
                    </TableCell>

                    <TableCell>
                        <div class="flex flex-col text-sm">
                            <span class="font-medium">{{ emp.position || '-' }}</span>
                            <span class="text-xs text-muted-foreground">{{ emp.department || '-' }}</span>
                        </div>
                    </TableCell>

                    <TableCell>
                        <Badge :variant="emp.is_active ? 'default' : 'secondary'" class="gap-1">
                            <component :is="emp.is_active ? BadgeCheck : Ban" class="h-3 w-3" />
                            {{ emp.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </TableCell>

                    <TableCell class="text-center">
                        <span class="font-mono text-sm">{{ emp.assets_count }}</span>
                    </TableCell>

                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                
                                <DropdownMenuItem @click="emit('detail', emp)">
                                    <Eye class="mr-2 h-4 w-4" /> Detail Profile
                                </DropdownMenuItem>
                                
                                <DropdownMenuItem @click="emit('edit', emp)">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit Data
                                </DropdownMenuItem>
                                
                                <DropdownMenuSeparator />
                                
                                <DropdownMenuItem @click="emit('delete', emp)" class="text-red-600 focus:text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>

                <TableRow v-if="employees.data.length === 0">
                    <TableCell colspan="7" class="h-24 text-center text-muted-foreground">No employees found.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <div class="flex items-center justify-between p-4 border-t" v-if="employees.total > 0">
        <div class="text-sm text-muted-foreground">Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} entries</div>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!employees.prev_page_url" @click="emit('page-change', employees.prev_page_url)">Previous</Button>
            <Button variant="outline" size="sm" :disabled="!employees.next_page_url" @click="emit('page-change', employees.next_page_url)">Next</Button>
        </div>
    </div>
</template>