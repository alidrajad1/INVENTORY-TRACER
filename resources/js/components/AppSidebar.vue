<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, Laptop, Layers, LayoutGrid, MapPin, User, Users, Wrench } from 'lucide-vue-next';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel, 
    SidebarGroupContent 
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { route } from 'ziggy-js';
import AppLogo from './AppLogo.vue';

interface NavGroup {
    label: string;
    items: NavItem[];
}

const navGroups: NavGroup[] = [
    {
        label: 'Platform',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ]
    },
    {
        label: 'Management',
        items: [
            {
                title: 'Assets',
                href: route('assets.index'),
                icon: Laptop,
            },
            {
                title: 'Categories',
                href: route('categories.index'),
                icon: Layers
            },
            {
                title: 'Locations',
                href: route('locations.index'),
                icon: MapPin
            },
        ]
    },
    {
        label: 'People',
        items: [
            {
                title: 'Users',
                href: '/users',
                icon: User
            },
            {
                title: 'Employees',
                href: '/employees',
                icon: Users
            },
        ]
    },
    {
        label: 'System',
        items: [
            {
                title: 'Maintenance Logs',
                href: '/maintenance-logs',
                icon: Wrench,
            },
            {
                title: 'Audit Logs',
                href: '/audit-logs',
                icon: BookOpen,
            },
        ]
    }
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup v-for="(group, index) in navGroups" :key="index">
                <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                
                <SidebarGroupContent>
                    <NavMain :items="group.items" />
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>