<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ArrowDownLeftFromCircle, AlertTriangle } from 'lucide-vue-next';
import { 
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select';
import { route } from 'ziggy-js';

const props = defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    condition: 'GOOD',
    notes: '',
});

const handleSubmit = () => {
    if (!props.asset) return;

    form.post(route('assets.return', props.asset.id), {
        onSuccess: () => {
            form.reset();
            emit('close');
        }
    });
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-orange-600">
                    <ArrowDownLeftFromCircle class="w-5 h-5" /> Return Asset (Check-In)
                </DialogTitle>
                <DialogDescription>
                    Proses pengembalian aset ke gudang (Available).
                </DialogDescription>
            </DialogHeader>
            
            <form @submit.prevent="handleSubmit" class="grid gap-4 py-4" v-if="asset">
                
                <div class="bg-orange-50 p-3 rounded-md border border-orange-100 space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="font-bold text-orange-900">{{ asset.name }}</span>
                        <span class="font-mono text-xs text-orange-800">{{ asset.asset_tag }}</span>
                    </div>
                    <div class="text-xs text-orange-700">
                        Dipinjam oleh: <b>{{ asset.employee?.name || 'Unknown' }}</b>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Condition upon Return</Label>
                    <Select v-model="form.condition">
                        <SelectTrigger>
                            <SelectValue placeholder="Select Condition" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="GOOD">Good (Baik & Lengkap)</SelectItem>
                            <SelectItem value="BAD">Bad (Lecet/Kotor/Tidak Lengkap)</SelectItem>
                            <SelectItem value="BROKEN">Broken (Rusak - Perlu Servis)</SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-[10px] text-muted-foreground" v-if="form.condition === 'BROKEN'">
                        * Aset akan ditandai rusak di history, tapi status tetap Available. Anda perlu mengirimnya ke Maintenance terpisah jika ingin diperbaiki.
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label>Notes</Label>
                    <Textarea v-model="form.notes" placeholder="e.g. Kabel charger hilang, ada goresan di layar..." />
                </div>
            </form>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Cancel</Button>
                <Button @click="handleSubmit" :disabled="form.processing" class="bg-orange-600 hover:bg-orange-700 text-white">
                    Confirm Return
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>