<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow
} from '@/components/ui/table';
import {
    Card, CardContent, CardHeader, CardTitle, CardDescription
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { FileClock, User } from 'lucide-vue-next';

defineProps({ logs: Object });

const breadcrumbs = [{ title: 'Activity Logs', href: route('logs.index') }];

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

const getVariant = (action) => {
    switch (action) {
        case 'created': return 'default';
        case 'updated': return 'secondary';
        case 'deleted': return 'destructive';
        default: return 'outline';
    }
};

const getActionLabel = (description) => {
    if (description === 'created') return 'Created';
    if (description === 'updated') return 'Updated';
    if (description === 'deleted') return 'Deleted';
    return description;
};
</script>

<template>

    <Head title="Activity Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6 px-4 space-y-6">

            <div class="flex items-center gap-2">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <FileClock class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Log Aktivitas</h2>
                    <p class="text-muted-foreground text-sm">Memantau riwayat perubahan data dalam sistem.</p>
                </div>
            </div>

            <Card>
                <CardContent class="p-0 overflow-x-auto">
                    <Table class="min-w-[1000px]">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[180px] whitespace-nowrap">Waktu</TableHead>
                                <TableHead class="w-[180px] whitespace-nowrap">User</TableHead>
                                <TableHead class="w-[100px] whitespace-nowrap">Aksi</TableHead>
                                <TableHead class="w-[180px] whitespace-nowrap">Target</TableHead>
                                <TableHead class="min-w-[400px]">Detail Perubahan</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id" class="group hover:bg-muted/50">

                                <TableCell class="align-top font-mono text-xs text-muted-foreground whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </TableCell>

                                <TableCell class="align-top">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 rounded-full bg-secondary flex items-center justify-center shrink-0">
                                            <User class="w-3 h-3 text-muted-foreground" />
                                        </div>
                                        <span class="font-medium text-sm truncate max-w-[140px]"
                                            :title="log.causer?.name">
                                            {{ log.causer ? log.causer.name : 'System' }}
                                        </span>
                                    </div>
                                </TableCell>

                                <TableCell class="align-top">
                                    <Badge :variant="getVariant(log.description)" class="capitalize text-[10px]">
                                        {{ getActionLabel(log.description) }}
                                    </Badge>
                                </TableCell>

                                <TableCell class="align-top">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm truncate max-w-[150px]">
                                            {{ log.subject_type?.split('\\').pop() }}
                                        </span>
                                        <span class="text-xs text-muted-foreground font-mono">
                                            #{{ log.subject_id }}
                                        </span>
                                    </div>
                                </TableCell>

                                <TableCell class="align-top py-4 pr-6">
                                        <div v-if="log.properties && log.properties.attributes"
                                            class="text-sm space-y-3 text-slate-700 dark:text-slate-300">

                                            <div v-for="(value, key) in log.properties.attributes" :key="key"
                                                class="leading-loose border-b last:border-0 border-dashed border-slate-200 dark:border-slate-700 pb-2 last:pb-0">

                                                <span v-if="log.description === 'updated' && log.properties.old">
                                                    Mengubah <strong class="font-semibold text-foreground">{{ key
                                                        }}</strong>
                                                    dari
                                                    <span
                                                        class="inline-block my-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 px-2 py-0.5 rounded text-xs font-mono border border-red-200 dark:border-red-800">
                                                        {{ log.properties.old[key] ?? 'kosong' }}
                                                    </span>
                                                    menjadi
                                                    <span
                                                        class="inline-block my-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded text-xs font-mono border border-green-200 dark:border-green-800">
                                                        {{ value ?? 'kosong' }}
                                                    </span>.
                                                </span>

                                                <span v-else>
                                                    {{ log.description === 'created' ? 'Menetapkan' : 'Menghapus' }}
                                                    <strong class="font-semibold text-foreground">{{ key }}</strong>:
                                                    <span
                                                        class="inline-block my-1 font-mono text-xs bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded">
                                                        {{ value }}
                                                    </span>.
                                                </span>
                                            </div>

                                        </div>
                                        <div v-else class="text-xs text-muted-foreground italic">
                                            - Tidak ada detail perubahan -
                                        </div>
                                </TableCell>

                            </TableRow>

                            <TableRow v-if="logs.data.length === 0">
                                <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                    Belum ada aktivitas yang tercatat.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>