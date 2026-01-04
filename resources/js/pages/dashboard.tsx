import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { useTranslation } from '@/hooks/use-translation';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { create as createCustomer } from '@/routes/customers';
import { create as createDocument } from '@/routes/documents';
import { create as createProduct } from '@/routes/products';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface RecentDocument {
    id: number;
    document_type: string;
    series: string;
    number: number;
    issue_date: string;
    total: string;
    status: string;
    customer?: { name: string };
    document_type_name?: string;
}

interface Props {
    stats: {
        total_customers: number;
        total_products: number;
        total_documents: number;
        pending_documents: number;
        accepted_documents: number;
        rejected_documents: number;
        total_sales: string;
    };
    recentDocuments: RecentDocument[];
    error?: string;
}

const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
        PENDING:
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        SENT: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        ACCEPTED:
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        REJECTED: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        CANCELED:
            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return colors[status] || colors.PENDING;
};

export default function Dashboard({ stats, recentDocuments, error }: Props) {
    const { t } = useTranslation();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('Dashboard')} />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {t('Dashboard')}
                        </h1>
                        <p className="text-muted-foreground">
                            Resumen de tu sistema de facturación
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={createDocument().url}>
                            <Button>Nuevo Documento</Button>
                        </Link>
                    </div>
                </div>

                {error && (
                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200">
                        <p className="font-semibold">Atención</p>
                        <p>{error}</p>
                        <Link
                            href="/companies/create"
                            className="mt-2 inline-block text-sm underline"
                        >
                            Crear una empresa ahora
                        </Link>
                    </div>
                )}

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-lg border p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    Clientes
                                </p>
                                <p className="text-3xl font-bold">
                                    {stats.total_customers}
                                </p>
                            </div>
                            <Link href={createCustomer().url}>
                                <Button variant="outline" size="sm">
                                    Nuevo
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-lg border p-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-muted-foreground">
                                    Productos
                                </p>
                                <p className="text-3xl font-bold">
                                    {stats.total_products}
                                </p>
                            </div>
                            <Link href={createProduct().url}>
                                <Button variant="outline" size="sm">
                                    Nuevo
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-lg border p-6">
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">
                                Documentos
                            </p>
                            <p className="text-3xl font-bold">
                                {stats.total_documents}
                            </p>
                            <div className="mt-2 flex gap-2 text-xs">
                                <span className="text-green-600">
                                    {stats.accepted_documents} aceptados
                                </span>
                                <span className="text-yellow-600">
                                    {stats.pending_documents} pendientes
                                </span>
                                <span className="text-red-600">
                                    {stats.rejected_documents} rechazados
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-lg border p-6">
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">
                                Ventas Totales
                            </p>
                            <p className="text-3xl font-bold">
                                S/ {parseFloat(stats.total_sales).toFixed(2)}
                            </p>
                            <p className="mt-1 text-xs text-muted-foreground">
                                Documentos aceptados
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-6">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold">
                            Documentos Recientes
                        </h2>
                        <Link href={createDocument().url}>
                            <Button variant="outline" size="sm">
                                Ver Todos
                            </Button>
                        </Link>
                    </div>

                    {recentDocuments.length === 0 ? (
                        <p className="text-center text-muted-foreground">
                            No hay documentos registrados
                        </p>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="px-4 py-2 text-left">
                                            Tipo
                                        </th>
                                        <th className="px-4 py-2 text-left">
                                            Serie-Número
                                        </th>
                                        <th className="px-4 py-2 text-left">
                                            Cliente
                                        </th>
                                        <th className="px-4 py-2 text-left">
                                            Fecha
                                        </th>
                                        <th className="px-4 py-2 text-right">
                                            Total
                                        </th>
                                        <th className="px-4 py-2 text-left">
                                            Estado
                                        </th>
                                        <th className="px-4 py-2 text-right">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {recentDocuments.map((document) => (
                                        <tr
                                            key={document.id}
                                            className="border-b"
                                        >
                                            <td className="px-4 py-2">
                                                {document.document_type_name ||
                                                    document.document_type}
                                            </td>
                                            <td className="px-4 py-2 font-medium">
                                                {document.series}-
                                                {document.number}
                                            </td>
                                            <td className="px-4 py-2">
                                                {document.customer?.name || '-'}
                                            </td>
                                            <td className="px-4 py-2">
                                                {new Date(
                                                    document.issue_date,
                                                ).toLocaleDateString('es-PE')}
                                            </td>
                                            <td className="px-4 py-2 text-right">
                                                S/{' '}
                                                {parseFloat(
                                                    document.total,
                                                ).toFixed(2)}
                                            </td>
                                            <td className="px-4 py-2">
                                                <span
                                                    className={`rounded-full px-2 py-1 text-xs ${getStatusBadge(document.status)}`}
                                                >
                                                    {document.status}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2 text-right">
                                                <Link
                                                    href={DocumentController.show.url(
                                                        document.id,
                                                    )}
                                                    className="text-primary hover:underline"
                                                >
                                                    Ver
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
