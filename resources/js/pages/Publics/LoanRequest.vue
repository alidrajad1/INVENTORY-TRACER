<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    Package2, Calendar, Mail, FileText, CheckCircle2, Search
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue
} from '@/components/ui/select';
import { route } from 'ziggy-js';

const props = defineProps<{
    assets: any[];
}>();

const form = useForm({
    email: '',
    asset_id: '',
    start_date: '',
    due_date: '',
    reason: '',
});

const isSuccess = ref(false);

const submit = () => {
    form.post(route('public.loan.store'), {
        onSuccess: () => {
            form.reset();
            isSuccess.value = true;
            setTimeout(() => isSuccess.value = false, 5000);
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Asset Loan Form" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-24">
        
        <div class="bg-white dark:bg-slate-900 border-b p-4 sticky top-0 z-10 shadow-sm">
            <div class="max-w-md mx-auto flex items-center gap-3">
                <div class="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center text-primary">
                    <Package2 class="w-6 h-6" />
                </div>
                <div>
                    <h1 class="font-bold text-lg leading-tight">Loan Request Form</h1>
                    <p class="text-xs text-muted-foreground">Enter details to borrow an asset</p>
                </div>
            </div>
        </div>

        <div v-if="isSuccess" class="p-4 max-w-md mx-auto animate-in slide-in-from-top-4">
            <div class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-lg flex items-center gap-3 shadow-sm">
                <CheckCircle2 class="w-6 h-6 shrink-0" />
                <div>
                    <p class="font-bold">Submitted Successfully!</p>
                    <p class="text-xs">The admin will process your request shortly.</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="p-4 max-w-md mx-auto grid gap-6 mt-2">
            
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border space-y-3">
                <h2 class="font-semibold text-sm text-slate-500 uppercase tracking-wider mb-2">Personal Information</h2>
                <div class="space-y-2">
                    <Label class="flex items-center gap-2">
                        <Mail class="w-4 h-4 text-primary" /> Work Email
                    </Label>
                    <Input type="email" v-model="form.email" placeholder="name@company.com" 
                        class="h-12 bg-slate-50 border-slate-200 focus:bg-white transition-all text-lg" required />
                    <p class="text-[10px] text-muted-foreground">Use the email registered in the system.</p>
                    <div v-if="form.errors.email" class="text-red-500 text-xs font-medium">{{ form.errors.email }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border space-y-4">
                <h2 class="font-semibold text-sm text-slate-500 uppercase tracking-wider mb-2">Asset Details</h2>
                
                <div class="space-y-2">
                    <Label class="flex items-center gap-2">
                        <Search class="w-4 h-4 text-primary" /> Select Asset
                    </Label>
                    <Select v-model="form.asset_id">
                        <SelectTrigger class="h-12 bg-slate-50 border-slate-200 text-base">
                            <SelectValue placeholder="Search asset..." />
                        </SelectTrigger>
                        <SelectContent class="max-h-[300px]">
                            <SelectItem v-for="asset in assets" :key="asset.id" :value="String(asset.id)">
                                <div class="py-1">
                                    <span class="font-medium block">{{ asset.name }} • </span>
                                    <span class="text-xs text-muted-foreground">{{ asset.asset_tag }} • {{ asset.brand }}</span>
                                </div>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.asset_id" class="text-red-500 text-xs font-medium">{{ form.errors.asset_id }}</div>
                </div>

                <div class="space-y-2">
                    <Label class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-primary" /> Start Date
                    </Label>
                    <Input type="date" v-model="form.start_date" 
                        class="h-12 bg-slate-50 border-slate-200 text-base block w-full" 
                        :min="new Date(new Date().setDate(new Date().getDate())).toISOString().split('T')[0]" required />
                    <div v-if="form.errors.start_date" class="text-red-500 text-xs font-medium">{{ form.errors.start_date }}</div>
                </div>

                <div class="space-y-2">
                    <Label class="flex items-center gap-2">
                        <Calendar class="w-4 h-4 text-primary" /> Return Date
                    </Label>
                    <Input type="date" v-model="form.due_date" 
                        class="h-12 bg-slate-50 border-slate-200 text-base block w-full" 
                        :min="new Date(new Date().setDate(new Date().getDate() + 1)).toISOString().split('T')[0]" required />
                    <div v-if="form.errors.due_date" class="text-red-500 text-xs font-medium">{{ form.errors.due_date }}</div>
                </div>

                <div class="space-y-2">
                    <Label class="flex items-center gap-2">
                        <FileText class="w-4 h-4 text-primary" /> Purpose
                    </Label>
                    <Textarea v-model="form.reason" placeholder="Example: Out-of-town client meeting" 
                        class="bg-slate-50 border-slate-200 min-h-[100px] text-base" required />
                    <div v-if="form.errors.reason" class="text-red-500 text-xs font-medium">{{ form.errors.reason }}</div>
                </div>
            </div>

        </form>

        <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/80 dark:bg-slate-950/80 backdrop-blur-md border-t z-20">
            <div class="max-w-md mx-auto">
                <Button @click="submit" size="lg" class="w-full text-base font-bold shadow-lg h-12" :disabled="form.processing">
                    <span v-if="form.processing">Submitting...</span>
                    <span v-else>Submit Request</span>
                </Button>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Disable zoom on input focus for iOS to prevent messing up layout */
@media screen and (max-width: 768px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}
</style>