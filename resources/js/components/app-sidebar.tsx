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
import { dashboard } from '@/routes';
import { index as companiesIndex } from '@/routes/companies';
import { index as customersIndex } from '@/routes/customers';
import { index as documentsIndex } from '@/routes/documents';
import { index as productsIndex } from '@/routes/products';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Building2, FileText, LayoutGrid, Package, Users } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Empresas',
        href: companiesIndex(),
        icon: Building2,
    },
    {
        title: 'Clientes',
        href: customersIndex(),
        icon: Users,
    },
    {
        title: 'Productos',
        href: productsIndex(),
        icon: Package,
    },
    {
        title: 'Documentos',
        href: documentsIndex(),
        icon: FileText,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
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
