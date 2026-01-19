<script setup lang="ts">
import { 
    MoreHorizontal, Pencil, Trash2, CalendarClock, CheckCircle 
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
    maintenances: { data: any[], from: number, to: number, total: number, prev_page_url: string, next_page_url: string };
}>();

const emit = defineEmits(['edit', 'delete', 'page-change']);

const formatDate = (date: string) => {
    if(!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'scheduled': return 'secondary'; // Abu-abu
        case 'in_progress': return 'default'; // Hitam/Biru (Primary)
        case 'completed': return 'outline';   // Hijau (Custom class nanti)
        case 'canceled': return 'destructive'; // Merah
        default: return 'outline';
    }
};
</script>

<template>
    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Asset Name</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Scheduled Date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Completed Date</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="item in maintenances.data" :key="item.id">
                    <TableCell>
                        <div class="flex flex-col">
                            <span class="font-medium">{{ item.asset?.name || 'Unknown Asset' }}</span>
                            <span class="text-xs text-muted-foreground font-mono">{{ item.asset?.asset_tag }}</span>
                        </div>
                    </TableCell>
                    <TableCell class="max-w-[200px] truncate" :title="item.description">
                        {{ item.description }}
                    </TableCell>
                    <TableCell>
                        <div class="flex items-center gap-2">
                            <CalendarClock class="w-3 h-3 text-muted-foreground" />
                            {{ formatDate(item.scheduled_at) }}
                        </div>
                    </TableCell>
                    <TableCell>
                        <Badge :variant="getStatusColor(item.status) as any" class="capitalize">
                            {{ item.status.replace('_', ' ') }}
                        </Badge>
                    </TableCell>
                    <TableCell>
                        {{ formatDate(item.completed_at) }}
                    </TableCell>
                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem @click="emit('edit', item)">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit / Update
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="emit('delete', item)" class="text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
                <TableRow v-if="maintenances.data.length === 0">
                    <TableCell colspan="6" class="h-24 text-center text-muted-foreground">No maintenance records found.</TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
    
    <div class="flex items-center justify-between p-4 border-t" v-if="maintenances.total > 0">
        <div class="text-sm text-muted-foreground">Showing {{ maintenances.from }} to {{ maintenances.to }} of {{ maintenances.total }}</div>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!maintenances.prev_page_url" @click="emit('page-change', maintenances.prev_page_url)">Prev</Button>
            <Button variant="outline" size="sm" :disabled="!maintenances.next_page_url" @click="emit('page-change', maintenances.next_page_url)">Next</Button>
        </div>
    </div>
</template>