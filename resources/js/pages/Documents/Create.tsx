import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import InputError from '@/components/input-error';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/documents';

interface Props {
    customers?: Array<{ id: number; name: string }>;
    documentTypes?: Array<{ code: string; name: string }>;
    error?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Documentos',
        href: index().url,
    },
    {
        title: 'Nuevo Documento',
        href: DocumentController.create.url(),
    },
];

interface DocumentItem {
    product_id: number | null;
    description: string;
    quantity: number;
    unit_price: number;
    total: number;
    tax_type: string;
    igv: number;
}

export default function DocumentsCreate({
    customers = [],
    documentTypes = [],
    error: propError,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || propError || '';
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);

    const [items, setItems] = useState<DocumentItem[]>([
        {
            product_id: null,
            description: '',
            quantity: 1,
            unit_price: 0,
            total: 0,
            tax_type: '10',
            igv: 0,
        },
    ]);

    useEffect(() => {
        if (flash?.success) {
            setShowSuccessModal(true);
        }
    }, [flash?.success]);

    useEffect(() => {
        if (flash?.error || propError) {
            setShowErrorModal(true);
        }
    }, [flash?.error, propError]);

    const handleSuccessClose = () => {
        setShowSuccessModal(false);
        // Redirigir a la vista del documento después de crear
        if (flash?.success?.includes('creado')) {
            // El documento redirige automáticamente a su página de detalle
            // No necesitamos hacer nada aquí
        }
    };

    const handleErrorClose = () => {
        setShowErrorModal(false);
    };

    const calculateItemTotal = (item: DocumentItem) => {
        const subtotal = item.quantity * item.unit_price;
        const igvAmount = item.tax_type === '10' ? subtotal * 0.18 : 0;
        return {
            total: subtotal + igvAmount,
            igv: igvAmount,
        };
    };

    const updateItem = (
        index: number,
        field: keyof DocumentItem,
        value: any,
    ) => {
        const newItems = [...items];
        newItems[index] = { ...newItems[index], [field]: value };

        if (
            field === 'quantity' ||
            field === 'unit_price' ||
            field === 'tax_type'
        ) {
            const calculated = calculateItemTotal(newItems[index]);
            newItems[index].total = calculated.total;
            newItems[index].igv = calculated.igv;
        }

        setItems(newItems);
    };

    const addItem = () => {
        setItems([
            ...items,
            {
                product_id: null,
                description: '',
                quantity: 1,
                unit_price: 0,
                total: 0,
                tax_type: '10',
                igv: 0,
            },
        ]);
    };

    const removeItem = (index: number) => {
        if (items.length > 1) {
            setItems(items.filter((_, i) => i !== index));
        }
    };

    const calculateTotals = () => {
        const totalTaxed = items.reduce((sum, item) => {
            const subtotal = item.quantity * item.unit_price;
            return sum + subtotal;
        }, 0);
        const totalIgv = items.reduce((sum, item) => sum + item.igv, 0);
        const total = totalTaxed + totalIgv;

        return { totalTaxed, totalIgv, total };
    };

    const totals = calculateTotals();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nuevo Documento" />

            <SuccessModal
                open={showSuccessModal}
                onClose={handleSuccessClose}
                title="Documento Creado"
                message={successMessage}
            />

            <ErrorModal
                open={showErrorModal}
                onClose={handleErrorClose}
                title="Error al Crear Documento"
                message={errorMessage}
            />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Nuevo Documento</h1>
                    <p className="text-muted-foreground">
                        Crea un nuevo documento electrónico
                    </p>
                </div>

                {propError && (
                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200">
                        {propError}
                    </div>
                )}

                <Form
                    {...DocumentController.store.form()}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                >
                    {({ processing, errors }) => (
                        <>
                            {/* Campos ocultos para totales - se actualizan automáticamente cuando cambian los items */}
                            <input
                                type="hidden"
                                name="total_taxed"
                                value={totals.totalTaxed.toFixed(2)}
                            />
                            <input
                                type="hidden"
                                name="total_igv"
                                value={totals.totalIgv.toFixed(2)}
                            />
                            <input
                                type="hidden"
                                name="total"
                                value={totals.total.toFixed(2)}
                            />

                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="document_type">
                                        Tipo de Documento *
                                    </Label>
                                    <select
                                        id="document_type"
                                        name="document_type"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        required
                                        aria-invalid={
                                            errors.document_type
                                                ? 'true'
                                                : undefined
                                        }
                                    >
                                        <option value="">
                                            Seleccione un tipo
                                        </option>
                                        {documentTypes.map((type) => (
                                            <option
                                                key={type.code}
                                                value={type.code}
                                            >
                                                {type.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.document_type}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="customer_id">Cliente</Label>
                                    <select
                                        id="customer_id"
                                        name="customer_id"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                    >
                                        <option value="">Sin cliente</option>
                                        {customers.map((customer) => (
                                            <option
                                                key={customer.id}
                                                value={customer.id}
                                            >
                                                {customer.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="series">Serie *</Label>
                                    <Input
                                        id="series"
                                        name="series"
                                        type="text"
                                        maxLength={4}
                                        placeholder="F001"
                                        required
                                        aria-invalid={
                                            errors.series ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.series} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="number">Número *</Label>
                                    <Input
                                        id="number"
                                        name="number"
                                        type="number"
                                        min="1"
                                        required
                                        aria-invalid={
                                            errors.number ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.number} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="issue_date">
                                        Fecha de Emisión *
                                    </Label>
                                    <Input
                                        id="issue_date"
                                        name="issue_date"
                                        type="date"
                                        required
                                        defaultValue={
                                            new Date()
                                                .toISOString()
                                                .split('T')[0]
                                        }
                                        aria-invalid={
                                            errors.issue_date
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError message={errors.issue_date} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="currency">Moneda *</Label>
                                    <select
                                        id="currency"
                                        name="currency"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        required
                                        defaultValue="PEN"
                                    >
                                        <option value="PEN">Soles (PEN)</option>
                                        <option value="USD">
                                            Dólares (USD)
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div className="space-y-4 rounded-lg border p-6">
                                <div className="flex items-center justify-between">
                                    <h2 className="text-lg font-semibold">
                                        Items del Documento
                                    </h2>
                                    <Button
                                        type="button"
                                        onClick={addItem}
                                        variant="outline"
                                        size="sm"
                                    >
                                        Agregar Item
                                    </Button>
                                </div>

                                <div className="space-y-4">
                                    {items.map((item, index) => (
                                        <div
                                            key={index}
                                            className="grid gap-4 rounded-md border p-4 md:grid-cols-6"
                                        >
                                            <div className="grid gap-2 md:col-span-2">
                                                <Label>Descripción *</Label>
                                                <Input
                                                    name={`items[${index}][description]`}
                                                    value={item.description}
                                                    onChange={(e) =>
                                                        updateItem(
                                                            index,
                                                            'description',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Descripción del item"
                                                    required
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label>Cantidad *</Label>
                                                <Input
                                                    name={`items[${index}][quantity]`}
                                                    type="number"
                                                    step="0.01"
                                                    min="0.01"
                                                    value={item.quantity || ''}
                                                    onChange={(e) =>
                                                        updateItem(
                                                            index,
                                                            'quantity',
                                                            parseFloat(
                                                                e.target.value,
                                                            ) || 0,
                                                        )
                                                    }
                                                    required
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label>Precio Unit. *</Label>
                                                <Input
                                                    name={`items[${index}][unit_price]`}
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    value={
                                                        item.unit_price || ''
                                                    }
                                                    onChange={(e) =>
                                                        updateItem(
                                                            index,
                                                            'unit_price',
                                                            parseFloat(
                                                                e.target.value,
                                                            ) || 0,
                                                        )
                                                    }
                                                    required
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label>Tipo Impuesto *</Label>
                                                <select
                                                    name={`items[${index}][tax_type]`}
                                                    className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                                    value={item.tax_type}
                                                    onChange={(e) =>
                                                        updateItem(
                                                            index,
                                                            'tax_type',
                                                            e.target.value,
                                                        )
                                                    }
                                                    required
                                                >
                                                    <option value="10">
                                                        Gravado 18%
                                                    </option>
                                                    <option value="20">
                                                        Exonerado
                                                    </option>
                                                    <option value="30">
                                                        Inafecto
                                                    </option>
                                                    <option value="40">
                                                        Exportación
                                                    </option>
                                                </select>
                                            </div>
                                            <input
                                                type="hidden"
                                                name={`items[${index}][product_id]`}
                                                value={item.product_id || ''}
                                            />
                                            <input
                                                type="hidden"
                                                name={`items[${index}][total]`}
                                                value={item.total.toFixed(2)}
                                            />
                                            <input
                                                type="hidden"
                                                name={`items[${index}][igv]`}
                                                value={item.igv.toFixed(2)}
                                            />
                                            <div className="grid gap-2 md:col-span-6">
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <span className="text-sm text-muted-foreground">
                                                            Total:{' '}
                                                        </span>
                                                        <span className="font-semibold">
                                                            S/{' '}
                                                            {item.total.toFixed(
                                                                2,
                                                            )}
                                                        </span>
                                                        {item.igv > 0 && (
                                                            <span className="ml-2 text-sm text-muted-foreground">
                                                                (IGV: S/{' '}
                                                                {item.igv.toFixed(
                                                                    2,
                                                                )}
                                                                )
                                                            </span>
                                                        )}
                                                    </div>
                                                    {items.length > 1 && (
                                                        <Button
                                                            type="button"
                                                            variant="destructive"
                                                            size="sm"
                                                            onClick={() =>
                                                                removeItem(
                                                                    index,
                                                                )
                                                            }
                                                        >
                                                            Eliminar
                                                        </Button>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <InputError message={errors.items} />
                            </div>

                            <div className="rounded-lg border p-6">
                                <div className="flex justify-end">
                                    <dl className="w-64 space-y-2">
                                        <div className="flex justify-between">
                                            <dt>Subtotal:</dt>
                                            <dd>
                                                S/{' '}
                                                {totals.totalTaxed.toFixed(2)}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between">
                                            <dt>IGV:</dt>
                                            <dd>
                                                S/ {totals.totalIgv.toFixed(2)}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between border-t pt-2 text-lg font-semibold">
                                            <dt>Total:</dt>
                                            <dd>
                                                S/ {totals.total.toFixed(2)}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Guardando...'
                                        : 'Guardar Documento'}
                                </Button>
                                <Link href={index().url}>
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
