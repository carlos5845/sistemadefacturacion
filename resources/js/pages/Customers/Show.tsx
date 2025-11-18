import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';

interface Customer {
    id: number;
    identity_type: string;
    identity_number: string;
    name: string;
    address: string | null;
    email: string | null;
    phone: string | null;
    created_at: string;
    updated_at: string;
    documents_count?: number;
}

interface Props {
    customer: Customer;
}

const breadcrumbs = (customer: Customer): BreadcrumbItem[] => [
    {
        title: 'Clientes',
        href: index().url,
    },
    {
        title: customer.name,
        href: CustomerController.show.url(customer.id),
    },
];

export default function CustomersShow({ customer }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(customer)}>
            <Head title={customer.name} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{customer.name}</h1>
                        <p className="text-muted-foreground">
                            Información detallada del cliente
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={CustomerController.edit.url(customer.id)}>
                            <Button>Editar</Button>
                        </Link>
                        <Link href={index().url}>
                            <Button variant="outline">Volver</Button>
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Información Personal</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Tipo de Documento</dt>
                                <dd className="mt-1 text-sm">{customer.identity_type}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Número de Documento</dt>
                                <dd className="mt-1 text-sm">{customer.identity_number}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Nombre / Razón Social</dt>
                                <dd className="mt-1 text-sm font-medium">{customer.name}</dd>
                            </div>
                            {customer.address && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Dirección</dt>
                                    <dd className="mt-1 text-sm">{customer.address}</dd>
                                </div>
                            )}
                            {customer.email && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Email</dt>
                                    <dd className="mt-1 text-sm">{customer.email}</dd>
                                </div>
                            )}
                            {customer.phone && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Teléfono</dt>
                                    <dd className="mt-1 text-sm">{customer.phone}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Estadísticas</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Documentos Emitidos</dt>
                                <dd className="mt-1 text-2xl font-bold">{customer.documents_count || 0}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div className="rounded-lg border p-6">
                    <h2 className="mb-4 text-lg font-semibold">Información del Sistema</h2>
                    <dl className="space-y-3">
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Fecha de Registro</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(customer.created_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Última Actualización</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(customer.updated_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}



