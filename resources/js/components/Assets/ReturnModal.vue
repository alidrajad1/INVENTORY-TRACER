<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
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
import { watch } from 'vue';

const props = defineProps<{
    show: boolean;
    asset: any;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    condition: 'GOOD', // Default condition
    notes: '',
});

// Reset form when modal opens with a new asset
watch(() => props.asset, () => {
    form.reset();
    form.clearErrors();
    // If asset has a previous condition, it can be set here if desired
    // form.condition = props.asset?.condition || 'GOOD';
});

const submit = () => {
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
                <DialogTitle>Return Asset</DialogTitle>
                <DialogDescription>
                    Please verify the physical condition of the item before accepting the return.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
                <div v-if="asset" class="p-3 bg-muted/50 rounded-md text-sm mb-2">
                    <p class="font-medium">{{ asset.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ asset.asset_tag }}</p>
                    <p class="text-xs mt-1">Borrower: <span class="font-semibold">{{ asset.employee?.name }}</span></p>
                </div>

                <div class="grid gap-2">
                    <Label>Condition on Return</Label>
                    <Select v-model="form.condition">
                        <SelectTrigger>
                            <SelectValue placeholder="Select Condition" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="GOOD">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Good
                                </span>
                            </SelectItem>
                            <SelectItem value="BAD">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span> Bad / Poor
                                </span>
                            </SelectItem>
                            <SelectItem value="BROKEN">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> Broken
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label>Notes / Comments</Label>
                    <Textarea v-model="form.notes" placeholder="Example: Minor scratches on screen, charger missing..." />
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Cancel</Button>
                <Button @click="submit" :disabled="form.processing">
                    Confirm Return
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>