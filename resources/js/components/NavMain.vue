<script setup lang="ts">
import { type NavItem } from '@/types';
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';

defineProps<{
  items: NavItem[]
}>()
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem v-for="item in items" :key="item.title">
      <SidebarMenuButton as-child>
        <Link :href="item.href">
          <component :is="item.icon" />
          <span>{{ item.title }}</span>
        </Link>
      </SidebarMenuButton>
    </SidebarMenuItem>
  </SidebarMenu>
</template>