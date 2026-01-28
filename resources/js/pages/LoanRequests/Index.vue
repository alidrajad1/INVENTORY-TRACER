<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { CheckCircle2, XCircle, Clock, Calendar } from 'lucide-vue-next';
import { route } from 'ziggy-js';

const breadcrumbs = [{ title: 'Loan Request', href: route('loan-requests.index') }];

const props = defineProps<{
    requests: { data: any[], links: any[] };
}>();

// --- FIXED DATE FORMATTER ---
const formatDate = (dateString: string) => {
    if (!dateString) return '-'; // Handle if null/empty

    const date = new Date(dateString);
    // Check if date is valid
    if (isNaN(date.getTime())) return '-';

    return date.toLocaleDateString('en-US', {
        day: 'numeric', month: 'short', year: 'numeric'
    });
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'APPROVED': return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400';
        case 'REJECTED': return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400';
        default: return 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400';
    }
};

// --- REJECT LOGIC ---
const rejectModalOpen = ref(false);
const selectedRequest = ref<any>(null);
const rejectForm = useForm({ rejection_reason: '' });

const openRejectModal = (req: any) => {
    selectedRequest.value = req;
    rejectForm.reset();
    rejectModalOpen.value = true;
};

const submitReject = () => {
    rejectForm.post(route('loan-requests.reject', selectedRequest.value.id), {
        onSuccess: () => rejectModalOpen.value = false
    });
};

// --- APPROVE LOGIC ---
const approve = (req: any) => {
    // Get borrower name (user or employee)
    const borrowerName = req.user ? req.user.name : (req.employee ? req.employee.name : 'Employee');

    if (confirm(`Approve loan of ${req.asset.name} for ${borrowerName}?`)) {
        router.post(route('loan-requests.approve', req.id));
    }
};
</script>

<template>

    <Head title="Loan Request" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Asset Request List</h1>
                    <p class="text-muted-foreground">Manage loan requests from employees.</p>
                </div>
            </div>

            <div class="rounded-md border bg-card text-card-foreground">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Request Date</TableHead>
                            <TableHead>Borrower</TableHead>
                            <TableHead>Asset</TableHead>
                            <TableHead>Estimated Take</TableHead>
                            <TableHead>Estimated Return</TableHead>
                            <TableHead>Reason</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="req in requests.data" :key="req.id" class="hover:bg-muted/40">

                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Clock class="w-4 h-4 text-muted-foreground" />
                                    <span>{{ formatDate(req.created_at) }}</span>
                                </div>
                            </TableCell>

                            <TableCell>
                                <div class="flex flex-col">
                                    <span class="font-medium">
                                        {{ req.user ? req.user.name : (req.employee ? req.employee.name : 'Unknown') }}
                                    </span>

                                    <span class="text-xs text-muted-foreground">
                                        {{ req.user ? req.user.email : (req.employee ? req.employee.email : '-') }}
                                    </span>

                                    <span
                                        class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 w-fit px-1.5 py-0.5 rounded mt-1">
                                        {{ req.user ? req.user.department: (req.employee ? req.employee.department : '-') }}
                                    </span>
                                </div>
                            </TableCell>

                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="h-8 w-8 bg-white rounded border p-0.5 flex items-center justify-center shrink-0">
                                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${req.asset.asset_tag}`"
                                            class="w-full h-full object-contain" alt="QR" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm">{{ req.asset.name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ req.asset.asset_tag }}</div>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="w-4 h-4 text-blue-500" />
                                    <span class="font-medium">{{ formatDate(req.start_date) }}</span>
                                </div>
                            </TableCell>

                            <TableCell>
                                <div class="flex items-center gap-2 text-sm">
                                    <Calendar class="w-4 h-4 text-orange-500" />
                                    <span class="font-medium">{{ formatDate(req.due_date) }}</span>
                                </div>
                            </TableCell>

                            <TableCell>
                                <p class="text-sm text-muted-foreground italic truncate max-w-[200px]"
                                    :title="req.reason">
                                    "{{ req.reason }}"
                                </p>
                            </TableCell>

                            <TableCell>
                                <Badge :class="['border font-semibold', getStatusVariant(req.status)]">
                                    {{ req.status }}
                                </Badge>
                            </TableCell>

                            <TableCell class="text-right">
                                <div v-if="req.status === 'PENDING'" class="flex justify-end gap-2">
                                    <Button size="sm" variant="outline"
                                        class="text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 border-red-200"
                                        @click="openRejectModal(req)">
                                        <XCircle class="w-4 h-4 mr-1" /> Reject
                                    </Button>
                                    <Button size="sm" class="bg-green-600 hover:bg-green-700 text-white"
                                        @click="approve(req)">
                                        <CheckCircle2 class="w-4 h-4 mr-1" /> Approve
                                    </Button>
                                </div>
                                <div v-else class="text-xs text-muted-foreground">
                                    <span v-if="req.status === 'APPROVED'">Approved by Admin</span>
                                    <span v-if="req.status === 'REJECTED'">
                                        Rejected: {{ req.rejection_reason }}
                                    </span>
                                </div>
                            </TableCell>

                        </TableRow>

                        <TableRow v-if="requests.data.length === 0">
                            <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                                No loan requests found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>

        <Dialog :open="rejectModalOpen" @update:open="(val) => rejectModalOpen = val">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Reject Request</DialogTitle>
                    <DialogDescription>
                        Please provide a reason why this request is being rejected.
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <Textarea v-model="rejectForm.rejection_reason"
                        placeholder="Example: Limited stock, asset is broken, etc." />
                    <div v-if="rejectForm.errors.rejection_reason" class="text-red-500 text-xs mt-1">
                        {{ rejectForm.errors.rejection_reason }}
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="rejectModalOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="submitReject" :disabled="rejectForm.processing">
                        Reject Request
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>