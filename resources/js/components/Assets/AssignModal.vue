<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { 
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle 
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { route } from 'ziggy-js';
import { watch } from 'vue';
import { Package2, Calendar, User, FileText } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
    asset: any;
    employees: any[];
}>();

const emit = defineEmits(['close']);

const form = useForm({
    employee_id: '',
    loan_type: 'LONG_TERM', 
    due_date: '',
    notes: '',
});

// Reset form when modal opens for a different asset
watch(() => props.asset, () => {
    form.reset();
    form.clearErrors();
    form.loan_type = 'LONG_TERM';
});

const submit = () => {
    if (!props.asset) return;
    
    form.put(route('assets.assign', props.asset.id), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
        onError: (errors) => {
            console.error('Validation Errors:', errors);
        }
    });
};
</script>

<template>
    <Dialog :open="show" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-[500px] bg-background text-foreground transition-all">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Package2 class="w-5 h-5 text-primary" />
                    Check Out / Assign Asset
                </DialogTitle>
                <DialogDescription>
                    Select the recipient and specify the loan details.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-5 py-4">
                
                <div v-if="asset" class="flex items-center gap-4 p-3 rounded-lg border border-border bg-muted/40 dark:bg-muted/20">
                    <div class="h-10 w-10 bg-background rounded-md border flex items-center justify-center shrink-0 shadow-sm">
                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${asset.asset_tag}`"
                            alt="QR" class="w-8 h-8 object-contain opacity-80 dark:invert" />
                    </div>
                    <div>
                        <div class="font-semibold text-sm text-foreground">{{ asset.name }}</div>
                        <div class="text-xs text-muted-foreground font-mono">{{ asset.asset_tag }}</div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label class="flex items-center gap-1.5 text-sm font-medium">
                        <User class="w-4 h-4 text-muted-foreground" />
                        Recipient (Employee) <span class="text-red-500">*</span>
                    </Label>
                    <Select v-model="form.employee_id">
                        <SelectTrigger class="bg-background border-input ring-offset-background">
                            <SelectValue placeholder="Select employee name..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="emp in employees" :key="emp.id" :value="String(emp.id)">
                                <div class="flex flex-col text-left">
                                    <span class="font-medium">{{ emp.name }}</span>
                                    <span class="text-[10px] text-muted-foreground">{{ emp.position }} - {{ emp.department }}</span>
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.employee_id" class="text-red-500 text-xs mt-1">{{ form.errors.employee_id }}</div>
                </div>

                <div class="grid gap-3">
                    <Label class="text-sm font-medium">Loan Type</Label>
                    <RadioGroup v-model="form.loan_type" class="grid grid-cols-2 gap-4">
                        <div>
                            <RadioGroupItem id="long_term" value="LONG_TERM" class="peer sr-only" />
                            <Label for="long_term" 
                                class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-3 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary peer-data-[state=checked]:bg-primary/5 cursor-pointer transition-all text-center h-full">
                                <span class="font-semibold text-sm">Long Term</span>
                                <span class="text-[10px] text-muted-foreground mt-1">Fixed Inventory</span>
                            </Label>
                        </div>

                        <div>
                            <RadioGroupItem id="short_term" value="SHORT_TERM" class="peer sr-only" />
                            <Label for="short_term" 
                                class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-3 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary peer-data-[state=checked]:bg-primary/5 cursor-pointer transition-all text-center h-full">
                                <span class="font-semibold text-sm">Temporary</span>
                                <span class="text-[10px] text-muted-foreground mt-1">Short Term Loan</span>
                            </Label>
                        </div>
                    </RadioGroup>
                    <div v-if="form.errors.loan_type" class="text-red-500 text-xs">{{ form.errors.loan_type }}</div>
                </div>

                <div v-if="form.loan_type === 'SHORT_TERM'" 
                     class="grid gap-2 p-3 rounded-md bg-orange-50 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/50 animate-in slide-in-from-top-2 fade-in">
                    <Label class="flex items-center gap-1.5 text-sm font-medium text-orange-700 dark:text-orange-400">
                        <Calendar class="w-4 h-4" />
                        Expected Return Date <span class="text-red-500">*</span>
                    </Label>
                    <Input type="date" v-model="form.due_date" 
                           class="bg-background border-orange-200 dark:border-orange-800 focus-visible:ring-orange-500" 
                           :min="new Date().toISOString().split('T')[0]" />
                    <p class="text-[10px] text-orange-600/80 dark:text-orange-400/70">
                        Required so the system can send reminder notifications.
                    </p>
                    <div v-if="form.errors.due_date" class="text-red-500 text-xs">{{ form.errors.due_date }}</div>
                </div>

                <div class="grid gap-2">
                    <Label class="flex items-center gap-1.5 text-sm font-medium">
                        <FileText class="w-4 h-4 text-muted-foreground" />
                        Notes (Optional)
                    </Label>
                    <Textarea v-model="form.notes" 
                              placeholder="Example: Used for events, charger not included, etc." 
                              class="bg-background resize-none h-20" />
                </div>

            </form>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="emit('close')" class="dark:border-slate-700">Cancel</Button>
                <Button @click="submit" :disabled="form.processing">
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Confirm Assignment</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>