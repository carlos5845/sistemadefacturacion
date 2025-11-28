import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { show } from '@/routes/documents';

interface Document {
    id: number;
    series: string;
    number: number;
    document_type: string;
}

interface Props {
    document: Document;
    xml: string;
    type: 'original' | 'signed';
}

const breadcrumbs = (document: Document, type: string): BreadcrumbItem[] => [
    {
        title: 'Documentos',
        href: '/documents',
    },
    {
        title: `${document.series}-${document.number}`,
        href: DocumentController.show.url(document.id),
    },
    {
        title: type === 'signed' ? 'XML Firmado' : 'XML Original',
        href: '#',
    },
];

export default function DocumentsXmlViewer({ document, xml, type }: Props) {
    const fileName =
        type === 'signed'
            ? `${document.series}-${document.number}-signed.xml`
            : `${document.series}-${document.number}.xml`;

    const downloadXml = () => {
        const blob = new Blob([xml], { type: 'application/xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs(document, type)}>
            <Head
                title={`${type === 'signed' ? 'XML Firmado' : 'XML Original'} - ${document.series}-${document.number}`}
            />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {type === 'signed'
                                ? 'XML Firmado UBL 2.1'
                                : 'XML Original UBL 2.1'}
                        </h1>
                        <p className="text-muted-foreground">
                            {document.series}-{document.number}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={downloadXml} variant="outline">
                            Descargar XML
                        </Button>
                        <Link href={DocumentController.show.url(document.id)}>
                            <Button variant="outline">Volver</Button>
                        </Link>
                    </div>
                </div>

                <div className="rounded-lg border bg-background">
                    <pre className="max-h-[calc(100vh-300px)] overflow-auto p-4 text-xs">
                        <code className="font-mono">{xml}</code>
                    </pre>
                </div>
            </div>
        </AppLayout>
    );
}

