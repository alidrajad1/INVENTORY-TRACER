<script setup lang="ts">
import { watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js'; 
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription
} from '@/components/ui/dialog';
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue
} from '@/components/ui/select';

const props = defineProps<{
    show: boolean;
    mode: 'create' | 'edit';
    assets: any[];
    maintenance?: any; 
}>();

const emit = defineEmits(['close']);

const form = useForm({
    id: null,
    asset_id: '',
    description: '',
    scheduled_at: '',
    status: 'scheduled',
    completed_at: null,
});

const title = computed(() => props.mode === 'create' ? 'Jadwalkan Maintenance' : 'Edit Maintenance');
// Tambahkan teks deskripsi dinamis
const description = computed(() => props.mode === 'create' 
    ? 'Isi formulir untuk membuat jadwal baru.' 
    : 'Ubah detail atau status pengerjaan.'
);

watch(() => props.maintenance, (newVal) => {
    if (props.mode === 'edit' && newVal) {
        form.id = newVal.id;
        form.asset_id = newVal.asset_id.toString(); 
        form.description = newVal.description;
        form.scheduled_at = newVal.scheduled_at ? newVal.scheduled_at.substring(0, 10) : ''; 
        form.status = newVal.status;
        form.completed_at = newVal.completed_at;
    } else {
        form.reset();
        form.status = 'scheduled';
        form.scheduled_at = new Date().toISOString().split('T')[0];
    }
}, { immediate: true });

const submit = () => {
    if (props.mode === 'create') {
        form.post(route('maintenances.store'), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.put(route('maintenances.update', props.maintenance.id), {
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
                </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                
                <div class="space-y-2">
                    <Label>Aset</Label>
                    <Select v-model="form.asset_id" :disabled="mode === 'edit'">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Aset" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="asset in assets" :key="asset.id" :value="asset.id.toString()">
                                {{ asset.name }} ({{ asset.asset_tag }})
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.asset_id" class="text-red-500 text-xs">{{ form.errors.asset_id }}</div>
                </div>

                <div class="space-y-2">
                    <Label>Deskripsi</Label>
                    <Textarea v-model="form.description" placeholder="Deskripsi kerusakan..." />
                    <div v-if="form.errors.description" class="text-red-500 text-xs">{{ form.errors.description }}</div>
                </div>

                <div class="space-y-2">
                    <Label>Tanggal Jadwal</Label>
                    <Input type="date" v-model="form.scheduled_at" />
                    <div v-if="form.errors.scheduled_at" class="text-red-500 text-xs">{{ form.errors.scheduled_at }}</div>
                </div>

                <div class="space-y-2">
                    <Label>Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="scheduled">Scheduled</SelectItem>
                            <SelectItem value="in_progress">In Progress</SelectItem>
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="canceled">Canceled</SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.status" class="text-red-500 text-xs">{{ form.errors.status }}</div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="emit('close')">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ mode === 'create' ? 'Simpan' : 'Update' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>