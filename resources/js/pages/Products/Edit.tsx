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

interface Product {
    id: number;
    name: string;
    description: string | null;
    category_id: number | null;
    unit_type: string;
    sale_price: string;
    purchase_price: string | null;
    tax_type: string;
    has_igv: boolean;
    active: boolean;
}

interface Props {
    product: Product;
    categories: Array<{ id: number; name: string }>;
    units: Array<{ code: string; name: string }>;
    taxTypes: Array<{ code: string; name: string }>;
}

const breadcrumbs = (product: Product): BreadcrumbItem[] => [
    {
        title: 'Productos',
        href: index().url,
    },
    {
        title: product.name,
        href: ProductController.show.url(product.id),
    },
    {
        title: 'Editar',
        href: ProductController.edit.url(product.id),
    },
];

export default function ProductsEdit({
    product,
    categories,
    units,
    taxTypes,
}: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || '';
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
        if (flash?.error) {
            setShowErrorModal(true);
        }
    }, [flash?.error]);

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
        <AppLayout breadcrumbs={breadcrumbs(product)}>
            <Head title={`Editar: ${product.name}`} />

            <SuccessModal
                open={showSuccessModal}
                onClose={handleSuccessClose}
                title="Producto Actualizado"
                message={successMessage}
            />

            <ErrorModal
                open={showErrorModal}
                onClose={handleErrorClose}
                title="Error al Actualizar Producto"
                message={errorMessage}
            />

            <AddCategoryModal
                open={showCategoryModal}
                onClose={() => setShowCategoryModal(false)}
                onCategoryCreated={handleCategoryCreated}
            />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Editar Producto</h1>
                    <p className="text-muted-foreground">
                        Actualiza la información del producto
                    </p>
                </div>

                <Form
                    {...ProductController.update.form(product.id)}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                    // @ts-ignore
                    defaults={{
                        name: product.name,
                        description: product.description || '',
                        category_id: product.category_id,
                        unit_type: product.unit_type,
                        sale_price: product.sale_price,
                        purchase_price: product.purchase_price || '',
                        tax_type: product.tax_type,
                        has_igv: product.has_igv,
                        active: product.active,
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
                                        required
                                        defaultValue={product.name}
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
                                        defaultValue={product.description || ''}
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
                                        defaultValue={product.category_id || ''}
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
                                        defaultValue={product.unit_type}
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
                                        required
                                        defaultValue={product.sale_price}
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
                                        defaultValue={
                                            product.purchase_price || ''
                                        }
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
                                        defaultValue={product.tax_type}
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
                                        defaultValue={
                                            product.has_igv ? '1' : '0'
                                        }
                                    >
                                        <option value="0">No</option>
                                        <option value="1">Sí</option>
                                    </select>
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="active">Estado</Label>
                                    <select
                                        id="active"
                                        name="active"
                                        className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                        defaultValue={
                                            product.active ? '1' : '0'
                                        }
                                    >
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Guardando...'
                                        : 'Guardar Cambios'}
                                </Button>
                                <Link
                                    href={ProductController.show.url(
                                        product.id,
                                    )}
                                >
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
