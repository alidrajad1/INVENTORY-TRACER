<script setup lang="ts">
import { 
    User, Mail, Briefcase, History, Smartphone, Laptop, Calendar 
} from 'lucide-vue-next';
import { 
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';

const getActionColor = (action: string) => {
    switch (action?.toLowerCase()) {
        case 'assign': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'return': return 'bg-green-50 text-green-700 border-green-200'; 
        default: return 'bg-gray-50 text-gray-600 border-gray-200';
    }
};

const getInitials = (name: string) => {
    if (!name) return '??';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const props = defineProps<{
    show: boolean;
    employee: any;
}>();

const emit = defineEmits(['close', 'edit']);
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[500px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Employee Profile</DialogTitle>
            </DialogHeader>
            
            <div v-if="employee" class="grid gap-6 py-4">
                
                <div class="flex items-start gap-4">
                    <Avatar class="h-20 w-20 border-2 border-muted">
                        <AvatarFallback class="text-xl font-bold bg-primary/10 text-primary">
                            {{ getInitials(employee.name) }}
                        </AvatarFallback>
                    </Avatar>
                    
                    <div class="space-y-1">
                        <h3 class="font-bold text-lg leading-none">{{ employee.name }}</h3>
                        <div class="flex items-center gap-1 text-sm text-muted-foreground">
                            <Mail class="w-3.5 h-3.5" /> {{ employee.email }}
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <Badge variant="outline" class="font-mono text-xs">
                                {{ employee.nid }}
                            </Badge>
                            <Badge :variant="employee.is_active ? 'default' : 'destructive'" class="text-[10px] h-5">
                                {{ employee.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                    <div class="flex items-center gap-3">
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <span class="block text-xs text-muted-foreground">Position</span>
                            <span class="font-medium">{{ employee.position || '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <User class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <span class="block text-xs text-muted-foreground">Department</span>
                            <span class="font-medium">{{ employee.department || '-' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <div>
                            <span class="block text-xs text-muted-foreground">Joined At</span>
                            <span class="font-medium">{{ formatDate(employee.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-4">
                    <div class="flex items-center gap-2 mb-3">
                        <History class="h-4 w-4 text-muted-foreground" />
                        <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                            Asset Assignment History
                        </h4>
                    </div>

                    <div v-if="employee.histories && employee.histories.length > 0" 
                         class="space-y-3 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                        
                        <div v-for="history in employee.histories" :key="history.id" 
                             class="flex justify-between items-start text-sm border-b pb-2 last:border-0 group">
                            
                            <div class="flex gap-3">
                                <div class="mt-0.5 p-1.5 bg-muted rounded-md text-muted-foreground">
                                    <Laptop class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-medium text-primary">
                                        {{ history.asset ? history.asset.name : 'Unknown Asset' }}
                                    </div>
                                    <div class="text-xs text-muted-foreground font-mono">
                                        {{ history.asset ? history.asset.asset_tag : 'No Tag' }}
                                    </div>
                                    <div v-if="history.notes" class="text-xs text-muted-foreground italic mt-0.5">
                                        "{{ history.notes }}"
                                    </div>
                                </div>
                            </div>

                            <div class="text-right shrink-0 ml-2">
                                <div class="text-xs text-muted-foreground mb-1">
                                    {{ formatDate(history.created_at) }}
                                </div>
                                <span :class="['text-[10px] px-1.5 py-0.5 rounded border capitalize', getActionColor(history.action)]">
                                    {{ history.action === 'assign' ? 'Received' : history.action }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-sm text-muted-foreground text-center py-4 bg-muted/30 rounded border border-dashed">
                        No asset history found for this employee.
                    </div>
                </div>

            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Close</Button>
                <Button @click="emit('edit')">Edit Profile</Button>
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
    background: #e5e7eb;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}
</style>