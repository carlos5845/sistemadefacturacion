import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Eye, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/documents';

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

export default function DocumentsIndex({
    documents,
    documentTypes,
    filters,
    error,
}: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [documentType, setDocumentType] = useState(
        filters.document_type || '',
    );
    const [status, setStatus] = useState(filters.status || '');
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [documentToDelete, setDocumentToDelete] = useState<Document | null>(
        null,
    );
    const [isDeleting, setIsDeleting] = useState(false);
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };

    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);
    const [successMessage, setSuccessMessage] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    // Manejar mensajes flash
    useEffect(() => {
        if (flash?.success) {
            setSuccessMessage(flash.success);
            setShowSuccessModal(true);
        }
        if (flash?.error) {
            setErrorMessage(flash.error);
            setShowErrorModal(true);
        }
    }, [flash]);

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(
            index().url,
            {
                search,
                document_type: documentType || undefined,
                status: status || undefined,
            },
            { preserveState: true },
        );
    };

    const handleDeleteClick = (document: Document) => {
        setDocumentToDelete(document);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (!documentToDelete) {
            return;
        }

        setIsDeleting(true);
        router.delete(DocumentController.destroy.url(documentToDelete.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteDialogOpen(false);
                setDocumentToDelete(null);
                setIsDeleting(false);
                setSuccessMessage('Documento eliminado exitosamente.');
                setShowSuccessModal(true);
            },
            onError: (errors) => {
                setIsDeleting(false);
                const errorMsg =
                    typeof errors === 'string'
                        ? errors
                        : 'Error al eliminar el documento.';
                setErrorMessage(errorMsg);
                setShowErrorModal(true);
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
    };

    const getStatusBadge = (status: string) => {
        const colors: Record<string, string> = {
            PENDING:
                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            SENT: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            ACCEPTED:
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            REJECTED:
                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            CANCELED:
                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
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
                                <th className="px-4 py-3 text-left">
                                    Serie-Número
                                </th>
                                <th className="px-4 py-3 text-left">Cliente</th>
                                <th className="px-4 py-3 text-left">Fecha</th>
                                <th className="px-4 py-3 text-right">Total</th>
                                <th className="px-4 py-3 text-left">Estado</th>
                                <th className="px-4 py-3 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {documents.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No hay documentos registrados
                                    </td>
                                </tr>
                            ) : (
                                documents.data.map((document) => (
                                    <tr key={document.id} className="border-b">
                                        <td className="px-4 py-3">
                                            {document.document_type_name ||
                                                document.document_type}
                                        </td>
                                        <td className="px-4 py-3">
                                            {document.series}-{document.number}
                                        </td>
                                        <td className="px-4 py-3">
                                            {document.customer?.name || '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {new Date(
                                                document.issue_date,
                                            ).toLocaleDateString()}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            S/{' '}
                                            {parseFloat(document.total).toFixed(
                                                2,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`rounded-full px-2 py-1 text-xs ${getStatusBadge(document.status)}`}
                                            >
                                                {document.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <Link
                                                                href={DocumentController.show.url(
                                                                    document.id,
                                                                )}
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-primary transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                                <span className="sr-only">
                                                                    Ver
                                                                    documento
                                                                </span>
                                                            </Link>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>Ver documento</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <a
                                                                href={`/documents/${document.id}/print`}
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-primary transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                            >
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="24"
                                                                    height="24"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    strokeWidth="2"
                                                                    strokeLinecap="round"
                                                                    strokeLinejoin="round"
                                                                    className="h-4 w-4"
                                                                >
                                                                    <polyline points="6 9 6 2 18 2 18 9" />
                                                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                                                    <rect
                                                                        width="12"
                                                                        height="8"
                                                                        x="6"
                                                                        y="14"
                                                                    />
                                                                </svg>
                                                                <span className="sr-only">
                                                                    Imprimir
                                                                    documento
                                                                </span>
                                                            </a>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>
                                                                Imprimir
                                                                documento
                                                            </p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <button
                                                                onClick={() =>
                                                                    handleDeleteClick(
                                                                        document,
                                                                    )
                                                                }
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-800 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300"
                                                                type="button"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                                <span className="sr-only">
                                                                    Eliminar
                                                                    documento
                                                                </span>
                                                            </button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>
                                                                Eliminar
                                                                documento
                                                            </p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Modal de confirmación de eliminación */}
                <Dialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>¿Eliminar documento?</DialogTitle>
                            <DialogDescription>
                                ¿Estás seguro de que deseas eliminar el
                                documento{' '}
                                <strong>
                                    {documentToDelete?.series}-
                                    {documentToDelete?.number}
                                </strong>
                                ?
                                <br />
                                Esta acción no se puede deshacer.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setDeleteDialogOpen(false);
                                    setDocumentToDelete(null);
                                }}
                                disabled={isDeleting}
                            >
                                Cancelar
                            </Button>
                            <Button
                                variant="destructive"
                                onClick={handleDeleteConfirm}
                                disabled={isDeleting}
                            >
                                {isDeleting ? 'Eliminando...' : 'Eliminar'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* Modales de éxito y error */}
                <SuccessModal
                    open={showSuccessModal}
                    onClose={() => {
                        setShowSuccessModal(false);
                        setSuccessMessage('');
                    }}
                    message={successMessage}
                />
                <ErrorModal
                    open={showErrorModal}
                    onClose={() => {
                        setShowErrorModal(false);
                        setErrorMessage('');
                    }}
                    message={errorMessage}
                />
            </div>
        </AppLayout>
    );
}
