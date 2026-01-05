import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { AddCategoryModal } from '@/components/add-category-modal';
import { ErrorModal } from '@/components/error-modal';
import InputError from '@/components/input-error';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/products';

interface Props {
    categories?: Array<{ id: number; name: string }>;
    units?: Array<{ code: string; name: string }>;
    taxTypes?: Array<{ code: string; name: string }>;
    error?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: index().url,
    },
    {
        title: 'Nuevo Producto',
        href: ProductController.create.url(),
    },
];

export default function ProductsCreate({
    categories = [],
    units = [],
    taxTypes = [],
    error: propError,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || propError || '';
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);
    const [showCategoryModal, setShowCategoryModal] = useState(false);
    const [categoriesList, setCategoriesList] = useState(categories);

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
        router.visit(index().url);
    };

    const handleErrorClose = () => {
        setShowErrorModal(false);
    };

    const handleCategoryCreated = (category: { id: number; name: string }) => {
        setCategoriesList([...categoriesList, category]);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nuevo Producto" />

            <SuccessModal
                open={showSuccessModal}
                onClose={handleSuccessClose}
                title="Producto Creado"
                message={successMessage}
            />

            <ErrorModal
                open={showErrorModal}
                onClose={handleErrorClose}
                title="Error al Crear Producto"
                message={errorMessage}
            />

            <AddCategoryModal
                open={showCategoryModal}
                onClose={() => setShowCategoryModal(false)}
                onCategoryCreated={handleCategoryCreated}
            />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Nuevo Producto</h1>
                    <p className="text-muted-foreground">
                        Registra un nuevo producto en el sistema
                    </p>
                </div>

                {propError && (
                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200">
                        {propError}
                    </div>
                )}

                <Form
                    {...ProductController.store.form()}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="name">
                                        Nombre del Producto *
                                    </Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        type="text"
                                        placeholder="Producto ejemplo"
                                        required
                                        aria-invalid={
                                            errors.name ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="description">
                                        Descripción
                                    </Label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows={3}
                                        className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        placeholder="Descripción del producto"
                                        aria-invalid={
                                            errors.description
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <div className="grid gap-2">
                                    <div className="flex items-center justify-between">
                                        <Label htmlFor="category_id">
                                            Categoría
                                        </Label>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() =>
                                                setShowCategoryModal(true)
                                            }
                                            className="h-7 text-xs"
                                        >
                                            + Agregar
                                        </Button>
                                    </div>
                                    <select
                                        id="category_id"
                                        name="category_id"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        aria-invalid={
                                            errors.category_id
                                                ? 'true'
                                                : undefined
                                        }
                                    >
                                        <option value="">Sin categoría</option>
                                        {categoriesList.map((category) => (
                                            <option
                                                key={category.id}
                                                value={category.id}
                                            >
                                                {category.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.category_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="unit_type">
                                        Unidad de Medida *
                                    </Label>
                                    <select
                                        id="unit_type"
                                        name="unit_type"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        required
                                        aria-invalid={
                                            errors.unit_type
                                                ? 'true'
                                                : undefined
                                        }
                                    >
                                        <option value="">
                                            Seleccione una unidad
                                        </option>
                                        {units.map((unit) => (
                                            <option
                                                key={unit.code}
                                                value={unit.code}
                                            >
                                                {unit.name} ({unit.code})
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.unit_type} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="sale_price">
                                        Precio de Venta *
                                    </Label>
                                    <Input
                                        id="sale_price"
                                        name="sale_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        required
                                        aria-invalid={
                                            errors.sale_price
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError message={errors.sale_price} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="purchase_price">
                                        Precio de Compra
                                    </Label>
                                    <Input
                                        id="purchase_price"
                                        name="purchase_price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        aria-invalid={
                                            errors.purchase_price
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        message={errors.purchase_price}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="tax_type">
                                        Tipo de Impuesto *
                                    </Label>
                                    <select
                                        id="tax_type"
                                        name="tax_type"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        required
                                        aria-invalid={
                                            errors.tax_type ? 'true' : undefined
                                        }
                                    >
                                        <option value="">
                                            Seleccione un tipo
                                        </option>
                                        {taxTypes.map((taxType) => (
                                            <option
                                                key={taxType.code}
                                                value={taxType.code}
                                            >
                                                {taxType.name}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.tax_type} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="has_igv">Afecta IGV</Label>
                                    <select
                                        id="has_igv"
                                        name="has_igv"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                    >
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>

                                <input type="hidden" name="active" value="1" />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Guardando...' : 'Guardar'}
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
