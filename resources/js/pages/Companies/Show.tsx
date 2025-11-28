import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Pencil, ArrowLeft } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/companies';

interface Company {
    id: number;
    ruc: string;
    business_name: string;
    trade_name: string | null;
    address: string | null;
    ubigeo: string | null;
    created_at: string;
    updated_at: string;
    users_count?: number;
    customers_count?: number;
    products_count?: number;
    documents_count?: number;
}

interface Props {
    company: Company;
}

const breadcrumbs = (company: Company): BreadcrumbItem[] => [
    {
        title: 'Empresas',
        href: index().url,
    },
    {
        title: company.business_name,
        href: CompanyController.show.url(company.id),
    },
];

export default function CompaniesShow({ company }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(company)}>
            <Head title={company.business_name} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">{company.business_name}</h1>
                        <p className="text-muted-foreground">
                            Información detallada de la empresa
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <TooltipProvider>
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Link href={CompanyController.edit.url(company.id)}>
                                        <Button>
                                            <Pencil className="h-4 w-4 mr-2" />
                                            Editar
                                        </Button>
                                    </Link>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Editar empresa</p>
                                </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                        <Link href={index().url}>
                            <Button variant="outline">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Información General</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">RUC</dt>
                                <dd className="mt-1 text-sm">{company.ruc}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Razón Social</dt>
                                <dd className="mt-1 text-sm">{company.business_name}</dd>
                            </div>
                            {company.trade_name && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Nombre Comercial</dt>
                                    <dd className="mt-1 text-sm">{company.trade_name}</dd>
                                </div>
                            )}
                            {company.address && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Dirección</dt>
                                    <dd className="mt-1 text-sm">{company.address}</dd>
                                </div>
                            )}
                            {company.ubigeo && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Ubigeo</dt>
                                    <dd className="mt-1 text-sm">{company.ubigeo}</dd>
                                </div>
                            )}
                        </dl>
                    </div>

                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Estadísticas</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Usuarios</dt>
                                <dd className="mt-1 text-2xl font-bold">{company.users_count || 0}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Clientes</dt>
                                <dd className="mt-1 text-2xl font-bold">{company.customers_count || 0}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Productos</dt>
                                <dd className="mt-1 text-2xl font-bold">{company.products_count || 0}</dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Documentos</dt>
                                <dd className="mt-1 text-2xl font-bold">{company.documents_count || 0}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div className="rounded-lg border p-6">
                    <h2 className="mb-4 text-lg font-semibold">Información del Sistema</h2>
                    <dl className="space-y-3">
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Fecha de Creación</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(company.created_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Última Actualización</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(company.updated_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}



