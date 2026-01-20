<script setup lang="ts">
import { watch } from 'vue'; // <--- 1. Pastikan import watch
import { useForm } from '@inertiajs/vue3';
import { ClipboardCheck } from 'lucide-vue-next';
import { 
    Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogDescription // <--- Tambahkan Description biar ga warning
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { route } from 'ziggy-js';

const props = defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    asset_id: null as number | null,
    condition: 'GOOD',
    notes: '',
    location_id: null as number | null,
});

watch(() => props.show, (isOpen) => {
    if (isOpen && props.asset) {
        form.asset_id = props.asset.id;
        form.location_id = props.asset.location_id; 
        form.condition = 'GOOD'; 
        form.notes = '';
        form.clearErrors();
    }
});

const handleSubmit = () => {
    form.post(route('audits.store'), {
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
                <DialogTitle class="flex items-center gap-2 text-green-700">
                    <ClipboardCheck class="w-5 h-5" /> Verify Asset (Audit)
                </DialogTitle>
                <DialogDescription>
                    Update kondisi fisik dan verifikasi keberadaan aset ini.
                </DialogDescription>
            </DialogHeader>
            
            <div class="grid gap-4 py-4" v-if="asset">
                <div class="bg-muted/50 p-3 rounded border text-sm space-y-1">
                    <div class="font-bold">{{ asset.name }}</div>
                    <div class="text-xs font-mono text-muted-foreground">{{ asset.asset_tag }}</div>
                    <div class="text-xs text-muted-foreground">Current Location: {{ asset.location?.name || '-' }}</div>
                </div>

                <div class="grid gap-2">
                    <Label>Physical Condition</Label>
                    <Select v-model="form.condition">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="GOOD">Good (Baik)</SelectItem>
                            <SelectItem value="BAD">Bad (Rusak Ringan/Lecet)</SelectItem>
                            <SelectItem value="BROKEN">Broken (Rusak Berat)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Audit Notes</Label>
                    <Textarea v-model="form.notes" placeholder="e.g. Item is clean, sticker intact." />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Cancel</Button>
                <Button @click="handleSubmit" :disabled="form.processing" class="bg-green-600 hover:bg-green-700 text-white">
                    Confirm Verified
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>