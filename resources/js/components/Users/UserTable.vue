<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { 
    MoreHorizontal, Pencil, Trash2, User, Shield 
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
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';

const props = defineProps<{
    users: { 
        data: any[], 
        prev_page_url: string, next_page_url: string, 
        from: number, to: number, total: number 
    };
}>();

const emit = defineEmits(['edit', 'delete', 'page-change']);

const selectedIds = ref<string[]>([]);
const isSelected = (id: any) => selectedIds.value.includes(String(id));

const isAllSelected = computed(() => {
    if (props.users.data.length === 0) return false;
    return props.users.data.every(u => isSelected(u.id));
});

const toggleSelectAll = (e: Event) => {
    const isChecked = (e.target as HTMLInputElement).checked;
    const pageIds = props.users.data.map(u => String(u.id));

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
        selectedIds.value = selectedIds.value.filter(item => item !== id);
    } else {
        selectedIds.value.push(id);
    }
};

watch(() => props.users.data, () => selectedIds.value = []);

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<template>
    <div v-if="selectedIds.length > 0" class="bg-primary/10 border border-primary/20 p-2 mb-2 rounded-md flex justify-between items-center animate-in fade-in slide-in-from-top-1">
        <span class="text-sm font-medium ml-2 text-primary">
            {{ selectedIds.length }} user selected
        </span>
        <Button size="sm" variant="destructive">
            <Trash2 class="mr-2 h-4 w-4" /> Bulk Delete
        </Button>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-[40px] pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isAllSelected" @change="toggleSelectAll"
                        />
                    </TableHead>
                    <TableHead>User</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead class="text-right pr-6">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in users.data" :key="user.id" 
                    :class="{'bg-muted/50': isSelected(user.id)}"
                    class="transition-colors hover:bg-muted/30"
                >
                    <TableCell class="pl-4">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary shadow-sm cursor-pointer accent-primary"
                            :checked="isSelected(user.id)" @change="toggleSelection(user.id)"
                        />
                    </TableCell>

                    <TableCell>
                        <div class="flex items-center gap-3">
                            <Avatar class="h-8 w-8">
                                <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                            </Avatar>
                            <div class="flex flex-col">
                                <span class="font-medium text-sm">{{ user.name }}</span>
                                <span class="text-xs text-muted-foreground visible sm:hidden">{{ user.email }}</span>
                            </div>
                        </div>
                    </TableCell>

                    <TableCell>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-for="role in user.roles" :key="role.id" variant="outline" class="gap-1">
                                <Shield class="h-3 w-3 text-blue-500" />
                                {{ role.name }}
                            </Badge>
                            <span v-if="user.roles.length === 0" class="text-xs text-muted-foreground">- No Role -</span>
                        </div>
                    </TableCell>

                    <TableCell class="hidden sm:table-cell text-sm text-muted-foreground">
                        {{ user.email }}
                    </TableCell>

                    <TableCell class="text-right pr-6">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" class="h-8 w-8 p-0"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem @click="emit('edit', user)">
                                    <Pencil class="mr-2 h-4 w-4" /> Edit
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="emit('delete', user)" class="text-red-600 focus:text-red-600">
                                    <Trash2 class="mr-2 h-4 w-4" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>

                <TableRow v-if="users.data.length === 0">
                    <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                        No users found.
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>

    <div class="flex items-center justify-between p-4 border-t" v-if="users.total > 0">
        <div class="text-sm text-muted-foreground">
            Showing {{ users.from }} to {{ users.to }} of {{ users.total }} entries
        </div>
        <div class="flex gap-2">
            <Button variant="outline" size="sm" :disabled="!users.prev_page_url" 
                @click="emit('page-change', users.prev_page_url)">
                Previous
            </Button>
            <Button variant="outline" size="sm" :disabled="!users.next_page_url" 
                @click="emit('page-change', users.next_page_url)">
                Next
            </Button>
        </div>
    </div>
</template>