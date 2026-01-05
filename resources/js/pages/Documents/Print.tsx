import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Printer } from 'lucide-react';

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
    customer?: {
        name: string;
        identity_number: string;
        address?: string;
    };
    company?: {
        name: string;
        ruc: string;
        address?: string;
        logo_path?: string;
    };
    document_type_name?: string;
    document_type_obj?: { name: string };
    items: DocumentItem[];
    hash?: string | null;
}

interface Props {
    document: Document;
}

export default function DocumentsPrint({ document }: Props) {
    // Ensure light mode for this page

    const documentType =
        document.document_type_obj?.name ||
        document.document_type_name ||
        document.document_type ||
        'DOCUMENTO';
    const documentTitle = `${documentType.toUpperCase()} ELECTRÓNICA`;

    return (
        <div className="min-h-screen bg-gray-100 p-8 font-sans text-sm text-black dark:bg-gray-100">
            <Head title={`Imprimir ${document.series}-${document.number}`} />

            {/* Toolbar - Hidden when printing */}
            <div className="mx-auto mb-6 flex max-w-3xl items-center justify-between print:hidden">
                <Link href={`/documents`}>
                    <Button
                        variant="outline"
                        className="border-gray-300 bg-white text-black hover:bg-gray-50"
                    >
                        <ArrowLeft className="mr-2 h-4 w-4" />
                        Volver al listado
                    </Button>
                </Link>
                <div className="flex gap-2">
                    <Button
                        onClick={() => window.print()}
                        className="bg-blue-600 text-white shadow-sm hover:bg-blue-700"
                    >
                        <Printer className="mr-2 h-4 w-4" />
                        Imprimir Documento
                    </Button>
                </div>
            </div>

            {/* Document Preview Container */}
            <div className="mx-auto max-w-3xl bg-white shadow-lg print:w-full print:max-w-none print:shadow-none">
                <div className="p-12 print:p-0">
                    {/* Header */}
                    <div className="mb-8 grid grid-cols-3 gap-8">
                        {/* Logo & Company Info */}
                        <div className="col-span-2">
                            <div className="mb-4">
                                {document.company?.logo_path ? (
                                    <img
                                        src={`/storage/${document.company.logo_path}`}
                                        alt="Logo"
                                        className="h-16 object-contain"
                                    />
                                ) : (
                                    <div className="flex h-16 w-16 items-center justify-center rounded border border-gray-200 bg-gray-100 text-xl font-bold text-gray-600">
                                        {document.company?.name?.substring(
                                            0,
                                            1,
                                        ) || 'E'}
                                    </div>
                                )}
                            </div>
                            <h1 className="text-lg font-bold text-black">
                                {document.company?.name || 'EMPRESA DEMO'}
                            </h1>
                            <p className="text-sm text-gray-600">
                                {document.company?.address ||
                                    'Dirección de la Empresa'}
                            </p>
                        </div>

                        {/* RUC Box */}
                        <div className="flex flex-col justify-center gap-1 rounded-lg border-2 border-black p-4 text-center">
                            <p className="font-bold text-black">
                                R.U.C. {document.company?.ruc || '00000000000'}
                            </p>
                            <p className="bg-gray-100 p-1 font-bold text-black">
                                {documentTitle}
                            </p>
                            <p className="font-bold text-black">
                                {document.series}-
                                {String(document.number).padStart(8, '0')}
                            </p>
                        </div>
                    </div>

                    {/* Client Info */}
                    <div className="mb-6 rounded border border-gray-200 bg-gray-50 px-4 py-3 text-black">
                        <div className="grid grid-cols-[100px_1fr] gap-y-1">
                            <span className="font-semibold">Fecha:</span>
                            <span>
                                {new Date(
                                    document.issue_date,
                                ).toLocaleDateString('es-PE')}
                            </span>

                            {document.customer ? (
                                <>
                                    <span className="font-semibold">
                                        Señor(es):
                                    </span>
                                    <span>{document.customer.name}</span>

                                    <span className="font-semibold">
                                        {document.document_type === '01'
                                            ? 'RUC:'
                                            : 'DNI/Doc:'}
                                    </span>
                                    <span>
                                        {document.customer.identity_number}
                                    </span>

                                    {document.customer.address && (
                                        <>
                                            <span className="font-semibold">
                                                Dirección:
                                            </span>
                                            <span>
                                                {document.customer.address}
                                            </span>
                                        </>
                                    )}
                                </>
                            ) : (
                                <>
                                    <span className="font-semibold">
                                        Cliente:
                                    </span>
                                    <span>SIN CLIENTE</span>
                                </>
                            )}

                            <span className="font-semibold">Moneda:</span>
                            <span>{document.currency}</span>
                        </div>
                    </div>

                    {/* Items Table */}
                    <table className="mb-6 w-full text-sm text-black">
                        <thead>
                            <tr className="border-b-2 border-black">
                                <th className="w-16 py-2 text-center">Cant.</th>
                                <th className="py-2 text-left">Descripción</th>
                                <th className="w-24 py-2 text-right">
                                    P. Unit
                                </th>
                                <th className="w-24 py-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {document.items.map((item) => (
                                <tr
                                    key={item.id}
                                    className="border-b border-gray-200"
                                >
                                    <td className="py-2 text-center">
                                        {parseFloat(item.quantity).toFixed(2)}
                                    </td>
                                    <td className="px-2 py-2">
                                        {item.description}
                                    </td>
                                    <td className="py-2 text-right">
                                        {parseFloat(item.unit_price).toFixed(2)}
                                    </td>
                                    <td className="py-2 text-right">
                                        {parseFloat(item.total).toFixed(2)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {/* Totals */}
                    <div className="mb-8 flex justify-end text-black">
                        <div className="w-64 space-y-2">
                            <div className="flex justify-between">
                                <span className="font-semibold">Subtotal:</span>
                                <span>
                                    {document.currency}{' '}
                                    {parseFloat(document.total_taxed).toFixed(
                                        2,
                                    )}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="font-semibold">
                                    I.G.V. (18%):
                                </span>
                                <span>
                                    {document.currency}{' '}
                                    {parseFloat(document.total_igv).toFixed(2)}
                                </span>
                            </div>
                            <div className="flex justify-between border-t border-black pt-2 text-lg font-bold">
                                <span>Total:</span>
                                <span>
                                    {document.currency}{' '}
                                    {parseFloat(document.total).toFixed(2)}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="border-t border-gray-300 pt-4 text-center text-xs text-gray-500">
                        <p>Representación Impresa de la {documentTitle}</p>
                        {document.hash && (
                            <p className="mt-1 font-mono">
                                Hash: {document.hash}
                            </p>
                        )}
                        <p className="mt-2 text-[10px]">
                            Generado por Facturación Electrónica
                        </p>
                    </div>
                </div>
            </div>

            {/* Print style override */}
            <style>{`
                @media print {
                    @page { margin: 0.5cm; size: A4; }
                    body { margin: 0; background-color: white !important; -webkit-print-color-adjust: exact; }
                    .min-h-screen { padding: 0 !important; background-color: white !important; }
                }
            `}</style>
        </div>
    );
}
