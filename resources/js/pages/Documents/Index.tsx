import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { index, create } from '@/routes/documents';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documentos',
        href: index().url,
    },
];

interface Document {
    id: number;
    document_type: string;
    series: string;
    number: number;
    issue_date: string;
    total: string;
    status: string;
    customer?: {
        name: string;
    };
    document_type_name?: string;
}

interface Props {
    documents: {
        data: Document[];
        links: any;
        meta: any;
    };
    documentTypes: Array<{
        code: string;
        name: string;
    }>;
    filters: {
        search?: string;
        document_type?: string;
        status?: string;
    };
    error?: string;
}

export default function DocumentsIndex({ documents, documentTypes, filters, error }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [documentType, setDocumentType] = useState(filters.document_type || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(index().url, { search, document_type: documentType || undefined, status: status || undefined }, { preserveState: true });
    };

    const getStatusBadge = (status: string) => {
        const colors: Record<string, string> = {
            PENDING: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            SENT: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            ACCEPTED: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            REJECTED: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            CANCELED: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
        return colors[status] || colors.PENDING;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Documentos" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Documentos</h1>
                        <p className="text-muted-foreground">
                            Gestiona facturas, boletas y notas
                        </p>
                    </div>
                    <Link href={create().url}>
                        <Button>Nuevo Documento</Button>
                    </Link>
                </div>

                {error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                        {error}
                    </div>
                )}

                <form onSubmit={handleFilter} className="flex gap-2">
                    <Input
                        type="text"
                        placeholder="Buscar por serie o número..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-sm"
                    />
                    <select
                        value={documentType}
                        onChange={(e) => setDocumentType(e.target.value)}
                        className="rounded-md border px-3 py-2"
                    >
                        <option value="">Todos los tipos</option>
                        {documentTypes.map((type) => (
                            <option key={type.code} value={type.code}>
                                {type.name}
                            </option>
                        ))}
                    </select>
                    <select
                        value={status}
                        onChange={(e) => setStatus(e.target.value)}
                        className="rounded-md border px-3 py-2"
                    >
                        <option value="">Todos los estados</option>
                        <option value="PENDING">Pendiente</option>
                        <option value="SENT">Enviado</option>
                        <option value="ACCEPTED">Aceptado</option>
                        <option value="REJECTED">Rechazado</option>
                        <option value="CANCELED">Cancelado</option>
                    </select>
                    <Button type="submit">Filtrar</Button>
                </form>

                <div className="rounded-lg border">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b">
                                <th className="px-4 py-3 text-left">Tipo</th>
                                <th className="px-4 py-3 text-left">Serie-Número</th>
                                <th className="px-4 py-3 text-left">Cliente</th>
                                <th className="px-4 py-3 text-left">Fecha</th>
                                <th className="px-4 py-3 text-right">Total</th>
                                <th className="px-4 py-3 text-left">Estado</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {documents.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-8 text-center text-muted-foreground">
                                        No hay documentos registrados
                                    </td>
                                </tr>
                            ) : (
                                documents.data.map((document) => (
                                    <tr key={document.id} className="border-b">
                                        <td className="px-4 py-3">{document.document_type_name || document.document_type}</td>
                                        <td className="px-4 py-3">{document.series}-{document.number}</td>
                                        <td className="px-4 py-3">{document.customer?.name || '-'}</td>
                                        <td className="px-4 py-3">{new Date(document.issue_date).toLocaleDateString()}</td>
                                        <td className="px-4 py-3 text-right">S/ {parseFloat(document.total).toFixed(2)}</td>
                                        <td className="px-4 py-3">
                                            <span className={`rounded-full px-2 py-1 text-xs ${getStatusBadge(document.status)}`}>
                                                {document.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={DocumentController.show.url(document.id)}
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

