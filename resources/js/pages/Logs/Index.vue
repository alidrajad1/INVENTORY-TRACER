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
import { FileClock, User, ArrowRight } from 'lucide-vue-next';

defineProps({ logs: Object });

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};

// Helper warna badge berdasarkan aksi
const getVariant = (action) => {
    switch (action) {
        case 'created': return 'default'; // Hitam/Primary
        case 'updated': return 'secondary'; // Abu-abu/Biru muda
        case 'deleted': return 'destructive'; // Merah
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

    <AppLayout>
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
                <CardHeader class="pb-3 border-b">
                    <CardTitle class="text-lg">Riwayat Terbaru</CardTitle>
                    <CardDescription>
                        Menampilkan {{ logs.data.length }} aktivitas terakhir.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[180px]">Waktu</TableHead>
                                <TableHead class="w-[150px]">User</TableHead>
                                <TableHead class="w-[100px]">Aksi</TableHead>
                                <TableHead class="w-[200px]">Target Data</TableHead>
                                <TableHead>Detail Perubahan</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in logs.data" :key="log.id" class="group hover:bg-muted/50">
                                
                                <TableCell class="align-top font-mono text-xs text-muted-foreground">
                                    {{ formatDate(log.created_at) }}
                                </TableCell>

                                <TableCell class="align-top">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-secondary flex items-center justify-center">
                                            <User class="w-3 h-3 text-muted-foreground" />
                                        </div>
                                        <span class="font-medium text-sm">
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
                                        <span class="font-semibold text-sm">
                                            {{ log.subject_type?.split('\\').pop() }}
                                        </span>
                                        <span class="text-xs text-muted-foreground font-mono">
                                            ID: #{{ log.subject_id }}
                                        </span>
                                    </div>
                                </TableCell>

                                <TableCell class="align-top">
                                    <ScrollArea class="h-full max-h-[150px] w-full rounded-md border p-2 bg-muted/30">
                                        <div v-if="log.properties && log.properties.attributes" class="space-y-1.5">
                                            
                                            <div v-for="(value, key) in log.properties.attributes" :key="key" 
                                                class="text-xs grid grid-cols-[1fr_auto_1fr] gap-2 items-center">
                                                
                                                <span class="font-semibold text-muted-foreground min-w-[80px]">
                                                    {{ key }}
                                                </span>

                                                <template v-if="log.description === 'updated' && log.properties.old">
                                                    <span class="text-red-500/80 line-through truncate max-w-[150px]" :title="log.properties.old[key]">
                                                        {{ log.properties.old[key] ?? 'null' }}
                                                    </span>
                                                    
                                                    <ArrowRight class="w-3 h-3 text-muted-foreground mx-1" />
                                                    
                                                    <span class="text-green-600 font-medium truncate max-w-[150px]" :title="value">
                                                        {{ value ?? 'null' }}
                                                    </span>
                                                </template>

                                                <template v-else>
                                                    <span class="col-span-2 text-foreground font-medium truncate">
                                                        {{ value }}
                                                    </span>
                                                </template>
                                            </div>

                                        </div>
                                        <div v-else class="text-xs text-muted-foreground italic">
                                            Tidak ada data atribut yang tercatat.
                                        </div>
                                    </ScrollArea>
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