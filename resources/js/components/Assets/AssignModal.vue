<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { UserPlus, Calendar, FileText } from 'lucide-vue-next';
import { 
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select';
import { route } from 'ziggy-js';

// Props yang diterima dari Index.vue
const props = defineProps<{
    show: boolean;
    asset: any;
    employees: Array<{ id: number; name: string; nid: string }>;
}>();

const emit = defineEmits(['close']);

// Setup Form Inertia
const form = useForm({
    employee_id: '',
    assigned_date: new Date().toISOString().split('T')[0], // Default Hari Ini
    notes: '',
});

const handleSubmit = () => {
    if (!props.asset) return;

    form.post(route('assets.assign', props.asset.id), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
        onError: () => {
            // Optional: Handle error toast here
        }
    });
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-blue-600">
                    <UserPlus class="w-5 h-5" /> Check Out Asset
                </DialogTitle>
                <DialogDescription>
                    Assign aset ini ke pegawai. Status akan berubah menjadi <b>Borrowed</b>.
                </DialogDescription>
            </DialogHeader>
            
            <form @submit.prevent="handleSubmit" class="grid gap-4 py-4" v-if="asset">
                
                <div class="bg-blue-50/50 p-3 rounded-md border border-blue-100 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-sm text-blue-900">{{ asset.name }}</div>
                        <div class="text-xs font-mono text-blue-700">{{ asset.asset_tag }}</div>
                    </div>
                    <div class="text-xs bg-white px-2 py-1 rounded border text-gray-500">
                        {{ asset.category?.name || 'No Category' }}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Select Employee <span class="text-red-500">*</span></Label>
                    <Select v-model="form.employee_id">
                        <SelectTrigger>
                            <SelectValue placeholder="Choose Employee..." />
                        </SelectTrigger>
                        <SelectContent class="max-h-[200px]">
                            <SelectItem v-for="emp in employees" :key="emp.id" :value="String(emp.id)">
                                {{ emp.name }} <span class="text-muted-foreground text-xs">({{ emp.nid }})</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <span v-if="form.errors.employee_id" class="text-xs text-red-500">{{ form.errors.employee_id }}</span>
                </div>

                <div class="grid gap-2">
                    <Label>Assigned Date</Label>
                    <div class="relative">
                        <Input type="date" v-model="form.assigned_date" />
                        <Calendar class="absolute right-3 top-2.5 h-4 w-4 text-muted-foreground pointer-events-none" />
                    </div>
                    <span v-if="form.errors.assigned_date" class="text-xs text-red-500">{{ form.errors.assigned_date }}</span>
                </div>

                <div class="grid gap-2">
                    <Label class="flex items-center gap-2">
                        Notes <span class="text-xs text-muted-foreground font-normal">(Optional)</span>
                    </Label>
                    <Textarea v-model="form.notes" placeholder="e.g. Untuk keperluan project luar kota..." rows="3" />
                </div>

            </form>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Cancel</Button>
                <Button @click="handleSubmit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white">
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Confirm Check Out</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>