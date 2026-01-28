<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Laptop, Users, Wrench, AlertTriangle, Activity, 
    ArrowUpRight, ArrowDownLeft, Wrench as WrenchIcon, ClipboardCheck, 
    FileQuestion, CalendarClock 
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';

const props = defineProps<{
    stats: any;
    activities: any[];
    latest_requests: any[];
}>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('id-ID', {
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
    });
};

const getActionIcon = (action: string) => {
    switch(action) {
        case 'assign': return { icon: ArrowUpRight, color: 'text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-500/20' };
        case 'return': return { icon: ArrowDownLeft, color: 'text-orange-600 dark:text-orange-400 bg-orange-100 dark:bg-orange-500/20' };
        case 'maintenance': return { icon: WrenchIcon, color: 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-500/20' };
        case 'audit': return { icon: ClipboardCheck, color: 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-500/20' };
        default: return { icon: Activity, color: 'text-gray-500 bg-gray-100 dark:bg-gray-800' };
    }
};

const getStatusVariant = (status: string) => {
    switch(status) {
        case 'APPROVED': return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400';
        case 'REJECTED': return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400';
        default: return 'bg-yellow-100 text-yellow-700 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400';
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
                    <p class="text-muted-foreground">Assets Management Overview.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                
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
                        <CardTitle class="text-sm font-medium">Asset Requests</CardTitle>
                        <FileQuestion class="h-4 w-4 text-yellow-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                            {{ stats.pending_requests }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Pending Approvals
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Deployed</CardTitle>
                        <Users class="h-4 w-4 text-blue-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.borrowed }}</div>
                        <p class="text-xs text-muted-foreground">
                            Assets in use
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Maintenance</CardTitle>
                        <Wrench class="h-4 w-4 text-red-500" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.maintenance }}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-muted-foreground">Assets being repaired</span>
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
                            Need verification
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                
                <Card class="h-full">
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
                                        <span class="text-xs text-muted-foreground"> {{ formatDate(activity.created_at) }}</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        <span class="font-semibold text-foreground/80"> {{ activity.asset?.name }}</span>
                                        <span v-if="activity.employee"> 
                                            {{ activity.action === 'assign' ? ' to ' : ' from ' }} 
                                            <span class="text-primary font-medium"> {{ activity.employee.name }}</span>
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

                <Card class="h-full">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle>Recent Loan Requests</CardTitle>
                        <Link :href="route('loan-requests.index')" class="text-xs text-primary hover:underline">
                            View All
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="req in latest_requests" :key="req.id" class="flex items-start justify-between border-b border-border last:border-0 pb-4 last:pb-0">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium">{{ req.user?.name || req.employee?.name }}</p>
                                        <Badge class="text-[10px] px-1.5 h-5" :class="getStatusVariant(req.status)">
                                            {{ req.status }}
                                        </Badge>
                                    </div>
                                    <p class="text-xs text-muted-foreground">
                                        Requesting: <span class="font-semibold text-foreground">{{ req.asset?.name }}</span>
                                    </p>
                                    <div class="flex items-center gap-1 text-[10px] text-muted-foreground">
                                        <CalendarClock class="w-3 h-3" />
                                        Return: {{ new Date(req.due_date).toLocaleDateString('id-ID') }}
                                    </div>
                                </div>
                                
                                <div v-if="req.status === 'PENDING'" class="flex gap-1">
                                    <Link :href="route('loan-requests.index')" class="text-xs bg-primary/10 hover:bg-primary/20 text-primary px-2 py-1 rounded transition-colors">
                                        Review
                                    </Link>
                                </div>
                            </div>

                            <div v-if="latest_requests.length === 0" class="text-center py-6 text-muted-foreground text-sm">
                                No pending requests.
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>