import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { sendToSunat } from '@/routes/documents';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/documents';

interface DocumentItem {
    id: number;
    description: string;
    quantity: string;
    unit_price: string;
    total: string;
    igv: string;
    tax_type?: { name: string };
    product?: { name: string };
}

interface Document {
    id: number;
    document_type: string;
    series: string;
    number: number;
    issue_date: string;
    currency: string;
    total_taxed: string;
    total_igv: string;
    total: string;
    status: string;
    customer?: { name: string; identity_number: string };
    document_type_name?: string;
    document_type_obj?: { name: string };
    items: DocumentItem[];
    sunat_response?: {
        sunat_code: string;
        sunat_message: string;
    };
}

interface Props {
    document: Document;
}

const breadcrumbs = (document: Document): BreadcrumbItem[] => [
    {
        title: 'Documentos',
        href: index().url,
    },
    {
        title: `${document.series}-${document.number}`,
        href: DocumentController.show.url(document.id),
    },
];

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

export default function DocumentsShow({ document }: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || '';
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);
    const [isSending, setIsSending] = useState(false);

    useEffect(() => {
        if (flash?.success) {
            setShowSuccessModal(true);
        }
    }, [flash?.success]);

    useEffect(() => {
        if (flash?.error) {
            setShowErrorModal(true);
        }
    }, [flash?.error]);

    const handleSendToSunat = () => {
        if (confirm('¿Está seguro de enviar este documento a SUNAT?')) {
            setIsSending(true);
            router.post(
                sendToSunat(document.id).url,
                {},
                {
                    onFinish: () => {
                        setIsSending(false);
                    },
                    onError: () => {
                        setIsSending(false);
                    },
                },
            );
        }
    };

    const handleSuccessClose = () => {
        setShowSuccessModal(false);
        // Solo recargar si el mensaje es sobre envío a SUNAT
        if (
            successMessage.includes('SUNAT') ||
            successMessage.includes('enviado')
        ) {
            router.reload();
        }
    };

    const handleErrorClose = () => {
        setShowErrorModal(false);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs(document)}>
            <Head title={`${document.series}-${document.number}`} />

            <SuccessModal
                open={showSuccessModal}
                onClose={handleSuccessClose}
                title={
                    successMessage?.includes('creado')
                        ? 'Documento Creado'
                        : 'Documento Enviado a SUNAT'
                }
                message={
                    successMessage ||
                    'El documento ha sido enviado a SUNAT. El proceso se está ejecutando en segundo plano.'
                }
            />

            <ErrorModal
                open={showErrorModal}
                onClose={handleErrorClose}
                title="Error al Enviar a SUNAT"
                message={
                    errorMessage ||
                    'Ocurrió un error al enviar el documento a SUNAT. Por favor, intente nuevamente.'
                }
            />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-semibold">
                                {document.document_type_obj?.name ||
                                    document.document_type_name ||
                                    document.document_type}{' '}
                                {document.series}-{document.number}
                            </h1>
                            <span
                                className={`rounded-full px-2 py-1 text-xs ${getStatusBadge(document.status)}`}
                            >
                                {document.status}
                            </span>
                        </div>
                        <p className="text-muted-foreground">
                            Documento electrónico
                        </p>
                    </div>
                    <div className="flex gap-2">
                        {document.status === 'PENDING' && (
                            <>
                                <Button
                                    onClick={handleSendToSunat}
                                    disabled={isSending}
                                >
                                    {isSending
                                        ? 'Enviando...'
                                        : 'Enviar a SUNAT'}
                                </Button>
                                <Link
                                    href={DocumentController.edit.url(
                                        document.id,
                                    )}
                                >
                                    <Button variant="outline">Editar</Button>
                                </Link>
                            </>
                        )}
                        <Link href={index().url}>
                            <Button variant="outline">Volver</Button>
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">
                            Información del Documento
                        </h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Tipo
                                </dt>
                                <dd className="mt-1 text-sm">
                                    {document.document_type_obj?.name ||
                                        document.document_type_name ||
                                        document.document_type}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Serie-Número
                                </dt>
                                <dd className="mt-1 text-sm font-medium">
                                    {document.series}-{document.number}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Fecha de Emisión
                                </dt>
                                <dd className="mt-1 text-sm">
                                    {new Date(
                                        document.issue_date,
                                    ).toLocaleDateString('es-PE')}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Moneda
                                </dt>
                                <dd className="mt-1 text-sm">
                                    {document.currency}
                                </dd>
                            </div>
                            {document.customer && (
                                <>
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">
                                            Cliente
                                        </dt>
                                        <dd className="mt-1 text-sm">
                                            {document.customer.name}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-muted-foreground">
                                            Documento Cliente
                                        </dt>
                                        <dd className="mt-1 text-sm">
                                            {document.customer.identity_number}
                                        </dd>
                                    </div>
                                </>
                            )}
                        </dl>
                    </div>

                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Totales</h2>
                        <dl className="space-y-3">
                            <div className="flex justify-between">
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Subtotal
                                </dt>
                                <dd className="text-sm font-semibold">
                                    {document.currency}{' '}
                                    {parseFloat(document.total_taxed).toFixed(
                                        2,
                                    )}
                                </dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-sm font-medium text-muted-foreground">
                                    IGV
                                </dt>
                                <dd className="text-sm font-semibold">
                                    {document.currency}{' '}
                                    {parseFloat(document.total_igv).toFixed(2)}
                                </dd>
                            </div>
                            <div className="flex justify-between border-t pt-2 text-lg font-bold">
                                <dt>Total</dt>
                                <dd>
                                    {document.currency}{' '}
                                    {parseFloat(document.total).toFixed(2)}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {document.sunat_response && (
                    <div
                        className={`rounded-lg border p-6 ${
                            document.status === 'ACCEPTED'
                                ? 'bg-green-50 dark:bg-green-950'
                                : document.status === 'REJECTED'
                                  ? 'bg-red-50 dark:bg-red-950'
                                  : ''
                        }`}
                    >
                        <h2 className="mb-2 text-lg font-semibold">
                            Respuesta SUNAT
                        </h2>
                        <dl className="space-y-2">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Código
                                </dt>
                                <dd className="mt-1 font-mono text-sm">
                                    {document.sunat_response.sunat_code}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">
                                    Mensaje
                                </dt>
                                <dd className="mt-1 text-sm">
                                    {document.sunat_response.sunat_message}
                                </dd>
                            </div>
                        </dl>
                    </div>
                )}

                <div className="rounded-lg border p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Items del Documento
                    </h2>
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-4 py-2 text-left">
                                        Descripción
                                    </th>
                                    <th className="px-4 py-2 text-right">
                                        Cantidad
                                    </th>
                                    <th className="px-4 py-2 text-right">
                                        Precio Unit.
                                    </th>
                                    <th className="px-4 py-2 text-right">
                                        IGV
                                    </th>
                                    <th className="px-4 py-2 text-right">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {document.items.map((item) => (
                                    <tr key={item.id} className="border-b">
                                        <td className="px-4 py-2">
                                            {item.product && (
                                                <span className="text-xs text-muted-foreground">
                                                    {item.product.name} -{' '}
                                                </span>
                                            )}
                                            {item.description}
                                        </td>
                                        <td className="px-4 py-2 text-right">
                                            {parseFloat(item.quantity).toFixed(
                                                2,
                                            )}
                                        </td>
                                        <td className="px-4 py-2 text-right">
                                            {document.currency}{' '}
                                            {parseFloat(
                                                item.unit_price,
                                            ).toFixed(2)}
                                        </td>
                                        <td className="px-4 py-2 text-right">
                                            {document.currency}{' '}
                                            {parseFloat(item.igv).toFixed(2)}
                                        </td>
                                        <td className="px-4 py-2 text-right font-semibold">
                                            {document.currency}{' '}
                                            {parseFloat(item.total).toFixed(2)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
