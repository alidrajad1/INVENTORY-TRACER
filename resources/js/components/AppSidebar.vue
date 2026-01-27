<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, Laptop, Layers, LayoutGrid, MapPin, User, Users, Wrench, Clock, Inbox } from 'lucide-vue-next';

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
import audits from '@/routes/audits';

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
            {
                title: 'Loan Requests',
                href: route('loan-requests.index'),
                icon: Inbox
            },
        ]
    },
    {
        label: 'People',
        items: [
            {
                title: 'Users',
                href: route('users.index'),
                icon: User
            },
            {
                title: 'Employees',
                href: route('employees.index'),
                icon: Users
            },
        ]
    },
    {
        label: 'System',
        items: [
            {
                title: 'Maintenance Assets',
                href: route('maintenances.index'),
                icon: Wrench,
            },
            {
                title: 'Audit Assets',
                href: route('audits.index'),
                icon: BookOpen,
            },
            {
                title: 'Activity Logs',
                href: '/logs',
                icon: Clock,
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