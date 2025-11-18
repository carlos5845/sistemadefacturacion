import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { index, create } from '@/routes/companies';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Empresas',
        href: index().url,
    },
];

interface Company {
    id: number;
    ruc: string;
    business_name: string;
    trade_name: string | null;
    address: string | null;
    created_at: string;
}

interface Props {
    companies: {
        data: Company[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
    };
}

export default function CompaniesIndex({ companies, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(index().url, { search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Empresas" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Empresas</h1>
                        <p className="text-muted-foreground">
                            Gestiona las empresas del sistema
                        </p>
                    </div>
                    <Link href={create().url}>
                        <Button>Nueva Empresa</Button>
                    </Link>
                </div>

                <form onSubmit={handleSearch} className="flex gap-2">
                    <Input
                        type="text"
                        placeholder="Buscar por RUC o razón social..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-sm"
                    />
                    <Button type="submit">Buscar</Button>
                </form>

                <div className="rounded-lg border">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b">
                                <th className="px-4 py-3 text-left">RUC</th>
                                <th className="px-4 py-3 text-left">Razón Social</th>
                                <th className="px-4 py-3 text-left">Nombre Comercial</th>
                                <th className="px-4 py-3 text-left">Dirección</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {companies.data.length === 0 ? (
                                <tr>
                                    <td colSpan={5} className="px-4 py-8 text-center text-muted-foreground">
                                        No hay empresas registradas
                                    </td>
                                </tr>
                            ) : (
                                companies.data.map((company) => (
                                    <tr key={company.id} className="border-b">
                                        <td className="px-4 py-3">{company.ruc}</td>
                                        <td className="px-4 py-3">{company.business_name}</td>
                                        <td className="px-4 py-3">{company.trade_name || '-'}</td>
                                        <td className="px-4 py-3">{company.address || '-'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={CompanyController.show.url(company.id)}
                                                className="text-primary hover:underline"
                                            >
                                                Ver
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}



