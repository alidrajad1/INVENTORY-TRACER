<script setup lang="ts">
import { Laptop, CheckCircle2, Wrench } from 'lucide-vue-next';

defineProps<{
    stats: { total: number; available: number; maintenance: number };
}>();
</script>

<template>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div
            class="relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-md">
                    <Laptop class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <span class="text-sm font-medium text-muted-foreground">Total Assets</span>
            </div>
            <div class="text-2xl font-bold">{{ stats.total }}</div>
            <p class="text-xs text-muted-foreground">Assets total</p>
        </div>

        <div
            class="relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-md">
                    <CheckCircle2 class="w-4 h-4 text-green-600 dark:text-green-400" />
                </div>
                <span class="text-sm font-medium text-muted-foreground">Available</span>
            </div>
            <div class="text-2xl font-bold">{{ stats.available }}</div>
            <p class="text-xs text-muted-foreground">Assets available for use</p>
        </div>

        <div
            class="relative overflow-hidden rounded-xl border bg-card text-card-foreground shadow-sm p-6 flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-md">
                    <Wrench class="w-4 h-4 text-red-600 dark:text-red-400" />
                </div>
                <span class="text-sm font-medium text-muted-foreground">In Maintenance</span>
            </div>
            <div class="text-2xl font-bold">{{ stats.maintenance }}</div>
            <p class="text-xs text-muted-foreground">Assets is under maintenance</p>
        </div>
    </div>
</template>