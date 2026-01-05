import DocumentController from '@/actions/App/Http/Controllers/DocumentController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import InputError from '@/components/input-error';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/documents';

interface Product {
    id: number;
    name: string;
    description: string | null;
    sale_price: number;
    tax_type: string;
    has_igv: boolean;
}

interface Props {
    customers?: Array<{ id: number; name: string }>;
    documentTypes?: Array<{ code: string; name: string }>;
    products?: Product[];
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

interface DocumentForm {
    company_id: string;
    customer_id: string;
    document_type: string;
    series: string;
    number: string;
    issue_date: string;
    currency: string;
    total_taxed: number;
    total_igv: number;
    total: number;
    items: DocumentItem[];
}

export default function DocumentsCreate({
    customers = [],
    documentTypes = [],
    products = [],
    error: propError,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || propError || '';
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);

    // Initialize form with Inertia's useForm hook using initial data structure
    // We start with default values consistent with the controller's expectation
    const { data, setData, post, processing, errors, reset } =
        useForm<DocumentForm>({
            company_id: '',
            customer_id: '',
            document_type: '',
            series: '',
            number: '',
            issue_date: new Date().toISOString().split('T')[0],
            currency: 'PEN',
            total_taxed: 0,
            total_igv: 0,
            total: 0,
            items: [],
        });

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

    // Calcular totales cada vez que cambian los items
    useEffect(() => {
        const { totalTaxed, totalIgv, total } = calculateTotals();
        setData((prevData) => ({
            ...prevData,
            total_taxed: totalTaxed, // Ensure these match the interface types
            total_igv: totalIgv,
            total: total,
            items: items.map((item) => ({
                ...item,
                // Asegurar que product_id sea null si es inválido
                product_id: item.product_id ? item.product_id : null,
            })),
        }));
    }, [items]);

    // Efecto para obtener serie y número automáticamente
    useEffect(() => {
        if (data.document_type) {
            // Usamos fetch directamente al endpoint creado
            // Asumiendo que `route` esté disponible globalmente por Ziggy, si no, usar URL relativa
            const url = `/documents/next-number?document_type=${data.document_type}&series=${data.series || ''}`;

            fetch(url)
                .then((res) => res.json())
                .then((response) => {
                    if (response.series && response.number) {
                        setData((prev) => ({
                            ...prev,
                            series: response.series,
                            number: response.number,
                        }));
                    }
                })
                .catch((err) =>
                    console.error('Error fetching next number:', err),
                );
        }
    }, [data.document_type]);

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

        // Auto-fill logic when product is selected
        if (field === 'product_id') {
            const product = products.find((p) => p.id == value);
            if (product) {
                newItems[index].description =
                    product.description || product.name;
                newItems[index].unit_price = Number(product.sale_price);
                newItems[index].tax_type = product.tax_type;
                // Recalculate based on new price and tax type
                const calculated = calculateItemTotal({
                    ...newItems[index],
                    unit_price: Number(product.sale_price),
                    tax_type: product.tax_type,
                });
                newItems[index].total = calculated.total;
                newItems[index].igv = calculated.igv;
            }
        } else if (
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

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        post('/documents');
                    }}
                    className="space-y-6"
                >
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
                                value={data.document_type}
                                onChange={(e) =>
                                    setData('document_type', e.target.value)
                                }
                                aria-invalid={
                                    errors.document_type ? 'true' : undefined
                                }
                            >
                                <option value="">Seleccione un tipo</option>
                                {documentTypes.map((type) => (
                                    <option key={type.code} value={type.code}>
                                        {type.name}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.document_type} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="customer_id">Cliente</Label>
                            <select
                                id="customer_id"
                                name="customer_id"
                                className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                value={data.customer_id}
                                onChange={(e) =>
                                    setData('customer_id', e.target.value)
                                }
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
                                value={data.series}
                                onChange={(e) =>
                                    setData('series', e.target.value)
                                }
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
                                value={data.number}
                                onChange={(e) =>
                                    setData('number', e.target.value)
                                }
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
                                value={data.issue_date}
                                onChange={(e) =>
                                    setData('issue_date', e.target.value)
                                }
                                aria-invalid={
                                    errors.issue_date ? 'true' : undefined
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
                                value={data.currency}
                                onChange={(e) =>
                                    setData('currency', e.target.value)
                                }
                            >
                                <option value="PEN">Soles (PEN)</option>
                                <option value="USD">Dólares (USD)</option>
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
                                        <Label>Producto</Label>
                                        <select
                                            className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                            value={item.product_id || ''}
                                            onChange={(e) =>
                                                updateItem(
                                                    index,
                                                    'product_id',
                                                    e.target.value,
                                                )
                                            }
                                        >
                                            <option value="">
                                                Seleccione un producto
                                                (opcional)
                                            </option>
                                            {products.map((product) => (
                                                <option
                                                    key={product.id}
                                                    value={product.id}
                                                >
                                                    {product.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
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
                                            value={item.unit_price || ''}
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
                                            <option value="30">Inafecto</option>
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
                                                    S/ {item.total.toFixed(2)}
                                                </span>
                                                {item.igv > 0 && (
                                                    <span className="ml-2 text-sm text-muted-foreground">
                                                        (IGV: S/{' '}
                                                        {item.igv.toFixed(2)})
                                                    </span>
                                                )}
                                            </div>
                                            {items.length > 1 && (
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() =>
                                                        removeItem(index)
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
                                    <dd>S/ {totals.totalTaxed.toFixed(2)}</dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt>IGV:</dt>
                                    <dd>S/ {totals.totalIgv.toFixed(2)}</dd>
                                </div>
                                <div className="flex justify-between border-t pt-2 text-lg font-semibold">
                                    <dt>Total:</dt>
                                    <dd>S/ {totals.total.toFixed(2)}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Guardando...' : 'Guardar Documento'}
                        </Button>
                        <Link href={index().url}>
                            <Button type="button" variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
