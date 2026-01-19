<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, Plus } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch'; // Pastikan ada komponen Switch (shadcn)
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import { route } from 'ziggy-js';
import EmployeeTable from '@/components/Employees/EmployeeTable.vue';
import EmployeeDetailModal from '@/components/Employees/EmployeeDetailModal.vue';

const props = defineProps<{
    employees: any;
    filters: { search: string };
}>();

const breadcrumbs = [{ title: 'Employees', href: route('employees.index') }];
const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingEmp = ref<any>(null);

const isDetailOpen = ref(false);
const selectedDetailEmp = ref<any>(null);

const form = useForm({
    nid: '',
    name: '',
    email: '',
    position: '',
    department: '',
    is_active: true, // Default active
});

watch(search, debounce((val: string) => {
    router.get(route('employees.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

const openDetail = (emp: any) => {
    selectedDetailEmp.value = emp;
    isDetailOpen.value = true;
};

const openCreate = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEdit = (emp: any) => {
    isEditMode.value = true;
    editingEmp.value = emp;
    form.nid = emp.nid;
    form.name = emp.name;
    form.email = emp.email;
    form.position = emp.position;
    form.department = emp.department;
    form.is_active = Boolean(emp.is_active);
    form.clearErrors();
    isModalOpen.value = true;
};

const handleSubmit = () => {
    if (isEditMode.value && editingEmp.value) {
        form.put(route('employees.update', editingEmp.value.id), { onSuccess: () => isModalOpen.value = false });
    } else {
        form.post(route('employees.store'), { onSuccess: () => isModalOpen.value = false });
    }
};

const handleDelete = (emp: any) => {
    if (confirm(`Are you sure you want to delete "${emp.name}"?`)) {
        router.delete(route('employees.destroy', emp.id));
    }
};
</script>

<template>

    <Head title="Employees" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Employees</h2>
                    <p class="text-muted-foreground">Manage employee directory and asset assignments.</p>
                </div>
                <div class="bg-primary/10 p-3 rounded-full">
                    <span class="text-primary font-bold text-xl">{{ employees.total }}</span>
                </div>
            </div>

            <div class="rounded-xl border bg-card relative min-h-[500px]">
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search by name, NID, or email..." class="pl-8" />
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Add Employee
                    </Button>
                </div>

                <EmployeeTable :employees="employees" @detail="openDetail" @edit="openEdit" @delete="handleDelete"
                    @page-change="(url) => router.get(url)" />
                <EmployeeDetailModal :show="isDetailOpen" :employee="selectedDetailEmp" @close="isDetailOpen = false"
                    @edit="() => { isDetailOpen = false; openEdit(selectedDetailEmp); }" />
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="(val) => isModalOpen = val">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Edit Employee' : 'Add New Employee' }}</DialogTitle>
                    <DialogDescription>
                        Fill in the employee details. NID and Email must be unique.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="nid">Employee ID (NID)</Label>
                            <Input id="nid" v-model="form.nid" placeholder="EMP-001" />
                            <span v-if="form.errors.nid" class="text-xs text-red-500">{{ form.errors.nid }}</span>
                        </div>
                        <div class="grid gap-2">
                            <Label for="name">Full Name</Label>
                            <Input id="name" v-model="form.name" placeholder="John Doe" />
                            <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="john.doe@company.com" />
                        <span v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="position">Position</Label>
                            <Input id="position" v-model="form.position" placeholder="Software Engineer" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="department">Department</Label>
                            <Input id="department" v-model="form.department" placeholder="IT Dept" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between border rounded-lg p-3 shadow-sm">
                        <div class="space-y-0.5">
                            <Label class="text-base">Active Status</Label>
                            <p class="text-xs text-muted-foreground">Can this employee access resources?</p>
                        </div>
                        <Switch :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = val" />
                    </div>

                </form>

                <DialogFooter>
                    <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                    <Button type="submit" @click="handleSubmit" :disabled="form.processing">
                        {{ isEditMode ? 'Save Changes' : 'Create Employee' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>