<script setup lang="ts">
defineProps<{
    stats: { total: number; available: number; maintenance: number };
}>();
</script>

<template>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 flex flex-col justify-center">
            <div class="text-sm font-medium text-muted-foreground">Total Assets</div>
            <div class="text-3xl font-bold mt-2">{{ stats.total }}</div>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 flex flex-col justify-center">
            <div class="text-sm font-medium text-muted-foreground">Available</div>
            <div class="text-3xl font-bold mt-2">{{ stats.available }}</div>
        </div>
        <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 flex flex-col justify-center">
            <div class="text-sm font-medium text-muted-foreground">In Maintenance</div>
            <div class="text-3xl font-bold mt-2">{{ stats.maintenance }}</div>
        </div>
    </div>
</template>