<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Laptop, Users, Wrench, AlertTriangle, Activity, 
    ArrowUpRight, ArrowDownLeft, Scan, ClipboardCheck, CheckCircle2 
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';

const props = defineProps<{
    stats: any;
    activities: any[];
    top_categories: any[];
}>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('id-ID', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};

const getActionIcon = (action: string) => {
    switch(action) {
        // Gunakan bg-opacity/10 agar bagus di dark mode juga
        case 'assign': return { icon: ArrowUpRight, color: 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-500/20' };
        case 'return': return { icon: ArrowDownLeft, color: 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-500/20' };
        case 'maintenance': return { icon: Wrench, color: 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-500/20' };
        case 'audit': return { icon: ClipboardCheck, color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/20' };
        default: return { icon: Activity, color: 'text-gray-500 bg-gray-100 dark:bg-gray-800' };
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="flex flex-col gap-6 p-4">
            
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
                    <p class="text-muted-foreground">Assets Management Overiview.</p>
                </div>
                <div class="flex gap-2">
                    <!-- <Link :href="route('scan.tool')">
                        <Button class="shadow-md">
                            <Scan class="mr-2 h-4 w-4" /> Scan QR
                        </Button>
                    </Link> -->
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Assets</CardTitle>
                        <Laptop class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.available }} Available for use
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Deployed / Borrowed</CardTitle>
                        <Users class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.borrowed }}</div>
                        <p class="text-xs text-muted-foreground">
                            Assets currently with employees
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">In Maintenance</CardTitle>
                        <Wrench class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.maintenance }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <Badge variant="outline" class="text-[10px] border-red-200 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800">
                                {{ stats.maintenance_pending }} Pending Approval
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card :class="{'border-orange-500/50 bg-orange-50/50 dark:bg-orange-900/10': stats.audit_overdue > 0}">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Audit Overdue</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-orange-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ stats.audit_overdue }}</div>
                        <p class="text-xs text-muted-foreground">
                            Assets need physical verification
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-7">
                
                <Card class="col-span-4">
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <div v-for="(activity, index) in activities" :key="activity.id" class="flex relative">
                                <div v-if="index !== activities.length - 1" class="absolute left-4 top-10 bottom-[-24px] w-px bg-border"></div>
                                
                                <div class="flex-none mr-4">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                                        :class="getActionIcon(activity.action).color">
                                        <component :is="getActionIcon(activity.action).icon" class="w-4 h-4" />
                                    </div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium leading-none capitalize text-foreground">
                                            {{ activity.action.replace('_', ' ') }}
                                        </p>
                                        <span class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        <span class="font-semibold text-foreground/80">{{ activity.asset?.name }}</span>
                                        <span v-if="activity.employee"> 
                                            {{ activity.action === 'assign' ? 'to' : 'from' }} 
                                            <span class="text-primary font-medium">{{ activity.employee.name }}</span>
                                        </span>
                                    </p>
                                    <p v-if="activity.notes" class="text-xs text-muted-foreground italic">
                                        "{{ activity.notes }}"
                                    </p>
                                </div>
                            </div>
                            
                            <div v-if="activities.length === 0" class="text-center py-8 text-muted-foreground text-sm">
                                No recent activity found.
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="col-span-3 space-y-6">
                    
                    <Card>
                        <CardHeader>
                            <CardTitle>Top Categories</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="cat in top_categories" :key="cat.id" class="flex items-center">
                                    <div class="w-full space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span class="font-medium">{{ cat.name }}</span>
                                            <span class="text-muted-foreground">{{ cat.assets_count }} items</span>
                                        </div>
                                        <div class="h-2 w-full bg-secondary rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full" 
                                                :style="{ width: Math.min((cat.assets_count / stats.total) * 100, 100) + '%' }">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="bg-slate-900 text-slate-50 border-0 dark:bg-slate-800">
                        <CardHeader>
                            <CardTitle class="text-slate-50">Actions Needed</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div v-if="stats.audit_overdue > 0" class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                                <div class="text-sm">
                                    <p class="font-bold text-orange-400">{{ stats.audit_overdue }} Assets</p>
                                    <p class="text-xs text-slate-400">Overdue for Audit</p>
                                </div>
                                <Link :href="route('audits.index')">
                                    <Button size="sm" variant="secondary" class="h-8 bg-white/10 hover:bg-white/20 text-white border-0">Audit</Button>
                                </Link>
                            </div>

                            <div v-if="stats.maintenance_pending > 0" class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-white/10">
                                <div class="text-sm">
                                    <p class="font-bold text-red-400">{{ stats.maintenance_pending }} Tickets</p>
                                    <p class="text-xs text-slate-400">Pending Approval</p>
                                </div>
                                <Button size="sm" variant="secondary" class="h-8 bg-white/10 hover:bg-white/20 text-white border-0">View</Button>
                            </div>
                            
                            <div v-if="stats.audit_overdue === 0 && stats.maintenance_pending === 0" class="text-center py-4 text-sm text-slate-400">
                                <CheckCircle2 class="w-8 h-8 mx-auto mb-2 text-emerald-500" />
                                All systems healthy!
                            </div>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>
    </AppLayout>
</template>