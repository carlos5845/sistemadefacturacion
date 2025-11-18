import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { index, create } from '@/routes/customers';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: index().url,
    },
];

interface Customer {
    id: number;
    identity_type: string;
    identity_number: string;
    name: string;
    email: string | null;
    phone: string | null;
    created_at: string;
}

interface Props {
    customers: {
        data: Customer[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
    };
    error?: string;
}

export default function CustomersIndex({ customers, filters, error }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(index().url, { search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Clientes" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Clientes</h1>
                        <p className="text-muted-foreground">
                            Gestiona los clientes de tu empresa
                        </p>
                    </div>
                    <Link href={create().url}>
                        <Button>Nuevo Cliente</Button>
                    </Link>
                </div>

                {error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSearch} className="flex gap-2">
                    <Input
                        type="text"
                        placeholder="Buscar por nombre o documento..."
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
                                <th className="px-4 py-3 text-left">Tipo</th>
                                <th className="px-4 py-3 text-left">Documento</th>
                                <th className="px-4 py-3 text-left">Nombre</th>
                                <th className="px-4 py-3 text-left">Email</th>
                                <th className="px-4 py-3 text-left">Teléfono</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {customers.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No hay clientes registrados
                                    </td>
                                </tr>
                            ) : (
                                customers.data.map((customer) => (
                                    <tr key={customer.id} className="border-b">
                                        <td className="px-4 py-3">{customer.identity_type}</td>
                                        <td className="px-4 py-3">{customer.identity_number}</td>
                                        <td className="px-4 py-3">{customer.name}</td>
                                        <td className="px-4 py-3">{customer.email || '-'}</td>
                                        <td className="px-4 py-3">{customer.phone || '-'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={CustomerController.show.url(customer.id)}
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

