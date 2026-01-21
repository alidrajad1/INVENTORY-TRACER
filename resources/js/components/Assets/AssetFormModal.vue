<script setup lang="ts">
import { RefreshCw, Save } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{
    show: boolean;
    mode: 'create' | 'edit';
    form: any; 
    categories: Array<{ id: number; name: string }>;
    locations: Array<{ id: number; name: string }>;
    isSyncing: boolean;
}>();

const emit = defineEmits(['close', 'submit', 'sync']);
</script>

<template>
    <Dialog :open="show" @update:open="(val:boolean) => !val && emit('close')">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
            <DialogHeader>
                <DialogTitle>{{ mode === 'create' ? 'Add New Asset' : 'Edit Asset' }}</DialogTitle>
                <DialogDescription v-if="mode === 'create'">Input asset details based on database schema.</DialogDescription>
            </DialogHeader>
            
            <form @submit.prevent="emit('submit')" class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Asset Tag</Label>
                        <Input v-model="form.asset_tag" placeholder="Auto-generated" disabled />
                    </div>
                    <div class="grid gap-2">
                        <Label>Serial Number *</Label>
                        <div class="flex gap-2">
                            <Input v-model="form.serial_number" placeholder="SN123456" required />
                            <Button type="button" variant="outline" size="icon" @click="emit('sync')" 
                                :disabled="isSyncing" title="Sync from GLPI">
                                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': isSyncing }" />
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Asset Name</Label>
                    <Input v-model="form.name" placeholder="e.g. MacBook Pro M3" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Brand</Label>
                        <Input v-model="form.brand" placeholder="Apple" />
                    </div>
                    <div class="grid gap-2">
                        <Label>Model</Label>
                        <Input v-model="form.model" placeholder="A2991" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label>Category</Label>
                        <Select v-model="form.category_id">
                            <SelectTrigger><SelectValue placeholder="Select Category" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Location</Label>
                        <Select v-model="form.location_id">
                            <SelectTrigger><SelectValue placeholder="Select Location" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label>Status</Label>
                    <Select v-model="form.status">
                        <SelectTrigger><SelectValue placeholder="Status" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="AVAILABLE">Available</SelectItem>
                            <SelectItem value="BORROWED">Borrowed</SelectItem>
                            <SelectItem value="MAINTENANCE">Maintenance</SelectItem>
                            <SelectItem value="LOST">Lost</SelectItem>
                            <SelectItem value="DISPOSED">Disposed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="border rounded-md p-3 bg-slate-50 dark:bg-slate-900 mt-2">
                    <div class="text-xs font-semibold text-muted-foreground mb-3 uppercase tracking-wider">Hardware Specifications</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1">
                            <Label class="text-xs">Processor</Label>
                            <Input v-model="form.hardware_specs.cpu" class="h-8 text-xs bg-white" placeholder="e.g. Intel i5" />
                        </div>
                        <div class="grid gap-1">
                            <Label class="text-xs">RAM</Label>
                            <Input v-model="form.hardware_specs.ram" class="h-8 text-xs bg-white" placeholder="e.g. 16GB" />
                        </div>
                        <div class="grid gap-1">
                            <Label class="text-xs">Storage</Label>
                            <Input v-model="form.hardware_specs.storage" class="h-8 text-xs bg-white" placeholder="e.g. 512GB SSD" />
                        </div>
                        <div class="grid gap-1">
                            <Label class="text-xs">OS</Label>
                            <Input v-model="form.hardware_specs.os" class="h-8 text-xs bg-white" placeholder="e.g. Windows 11" />
                        </div>
                    </div>
                </div>
            </form>

            <DialogFooter>
                <Button variant="outline" @click="emit('close')">Cancel</Button>
                <Button type="submit" @click="emit('submit')" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" /> {{ mode === 'create' ? 'Save' : 'Update' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>