<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Plus, List, Users, ClipboardList, PlusCircle, FileText, Calendar, UserCheck, UsersRound, User } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import NavMain from './NavMain.vue';
import NavFooter from './NavFooter.vue';
import NavUser from './NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

interface NavItem {
    title: string;
    href: string;
    icon: any;
}

const mainNavItems: NavItem[] = [
    {
        title: 'Vue d\'ensemble',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Liste des demandes',
        href: '/demandes',
        icon: ClipboardList,
    },
    {
        title: 'Créer une demande',
        href: '/demandes/create',
        icon: PlusCircle,
    },
    {
        title: 'Nouvel Attachement',
        href: '/attachements/create',
        icon: Plus,
    },
    {
        title: 'Liste des Attachements',
        href: '/attachements',
        icon: FileText,
    },
    {
        title: 'Clients',
        href: '/clients',
        icon: Users,
    },
    {
        title: 'Employés',
        href: '/employes',
        icon: User,
    },
    {
        title: '👥 Équipes',
        href: '/equipes',
        icon: UsersRound,
    },
    {
        title: '📅 Planning',
        href: '/planning',
        icon: Calendar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
