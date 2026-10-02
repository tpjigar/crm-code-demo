import { Link } from '@inertiajs/react';
import { AlertTriangle, Building2, ClipboardList, LayoutDashboard, ShieldCheck, Users, UsersRound } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutDashboard },
    { title: 'Clients', href: '/admin/clients', icon: Building2 },
    { title: 'Contacts', href: '/admin/contacts', icon: Users },
    { title: 'Incidents', href: '/admin/incidents', icon: AlertTriangle },
    { title: 'Users', href: '/admin/users', icon: UsersRound },
    { title: 'Audit Log', href: '/admin/security/audit', icon: ClipboardList },
    { title: 'Active Sessions', href: '/admin/security/sessions', icon: ShieldCheck },
];

export function AdminSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
