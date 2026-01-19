<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { Search, Plus } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import {
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import { route } from 'ziggy-js';
import UserTable from '@/components/Users/UserTable.vue';

const props = defineProps<{
    users: any;
    roles: Record<string, string>; // { 'admin': 'admin', 'user': 'user' }
    filters: { search: string };
}>();

const breadcrumbs = [{ title: 'Users', href: route('users.index') }];
const search = ref(props.filters.search || '');
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingUser = ref<any>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

// Search Logic
watch(search, debounce((val: string) => {
    router.get(route('users.index'), { search: val }, { preserveState: true, replace: true });
}, 300));

// --- ACTIONS ---

const openCreate = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEdit = (user: any) => {
    isEditMode.value = true;
    editingUser.value = user;

    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Reset password field
    form.password_confirmation = '';
    // Ambil role pertama user (asumsi single role utama)
    form.role = user.roles.length > 0 ? user.roles[0].name : '';

    form.clearErrors();
    isModalOpen.value = true;
};

const handleSubmit = () => {
    if (isEditMode.value && editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => isModalOpen.value = false,
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => isModalOpen.value = false,
        });
    }
};

const handleDelete = (user: any) => {
    if (confirm(`Are you sure you want to delete "${user.name}"?`)) {
        router.delete(route('users.destroy', user.id));
    }
};
</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-card p-6 rounded-xl border">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">User Management</h2>
                    <p class="text-muted-foreground">Manage accounts, roles, and permissions.</p>
                </div>
                <div class="bg-primary/10 p-3 rounded-full">
                    <span class="text-primary font-bold text-xl">{{ users.total }}</span>
                </div>
            </div>

            <div class="rounded-xl border bg-card relative min-h-[500px]">
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input v-model="search" placeholder="Search users by name or email..." class="pl-8" />
                    </div>
                    <Button @click="openCreate">
                        <Plus class="mr-2 h-4 w-4" /> Add User
                    </Button>
                </div>

                <UserTable :users="users" @edit="openEdit" @delete="handleDelete"
                    @page-change="(url) => router.get(url)" />
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="(val) => isModalOpen = val">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Edit User' : 'Create New User' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditMode ? 'Update account details. Leave password blank to keep current.' : 'Fill in thedetails to create a new account.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="handleSubmit" class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="name">Full Name</Label>
                        <Input id="name" v-model="form.name" placeholder="John Doe" />
                        <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email Address</Label>
                        <Input id="email" type="email" v-model="form.email" placeholder="john@example.com" />
                        <span v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</span>
                    </div>

                    <div class="grid gap-2">
                        <Label>Role</Label>
                        <Select v-model="form.role">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="(roleName, key) in roles" :key="key" :value="String(roleName)">
                                    {{ roleName }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <span v-if="form.errors.role" class="text-xs text-red-500">{{ form.errors.role }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="password">Password</Label>
                            <Input id="password" type="password" v-model="form.password" placeholder="******" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirm Password</Label>
                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation"
                                placeholder="******" />
                        </div>
                    </div>
                    <span v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</span>
                    <span v-if="isEditMode" class="text-xs text-muted-foreground italic">
                        * Leave password blank to keep the current password.
                    </span>

                </form>

                <DialogFooter>
                    <Button variant="outline" @click="isModalOpen = false">Cancel</Button>
                    <Button type="submit" @click="handleSubmit" :disabled="form.processing">
                        {{ isEditMode ? 'Save Changes' : 'Create User' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>