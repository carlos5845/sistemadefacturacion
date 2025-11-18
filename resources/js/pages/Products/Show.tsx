import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/products';

interface Product {
    id: number;
    name: string;
    description: string | null;
    sale_price: string;
    purchase_price: string | null;
    has_igv: boolean;
    active: boolean;
    created_at: string;
    updated_at: string;
    category?: { name: string };
    unit?: { name: string; code: string };
    tax_type?: { name: string; code: string };
    inventory_stocks?: Array<{
        warehouse: { name: string };
        quantity: string;
    }>;
}

interface Props {
    product: Product;
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
];

export default function ProductsShow({ product }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(product)}>
            <Head title={product.name} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-semibold">{product.name}</h1>
                            <span
                                className={`rounded-full px-2 py-1 text-xs ${
                                    product.active
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                }`}
                            >
                                {product.active ? 'Activo' : 'Inactivo'}
                            </span>
                        </div>
                        <p className="text-muted-foreground">
                            Información detallada del producto
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={ProductController.edit.url(product.id)}>
                            <Button>Editar</Button>
                        </Link>
                        <Link href={index().url}>
                            <Button variant="outline">Volver</Button>
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Información General</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Nombre</dt>
                                <dd className="mt-1 text-sm font-medium">{product.name}</dd>
                            </div>
                            {product.description && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Descripción</dt>
                                    <dd className="mt-1 text-sm">{product.description}</dd>
                                </div>
                            )}
                            {product.category && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Categoría</dt>
                                    <dd className="mt-1 text-sm">{product.category.name}</dd>
                                </div>
                            )}
                            {product.unit && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Unidad de Medida</dt>
                                    <dd className="mt-1 text-sm">
                                        {product.unit.name} ({product.unit.code})
                                    </dd>
                                </div>
                            )}
                            {product.tax_type && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Tipo de Impuesto</dt>
                                    <dd className="mt-1 text-sm">{product.tax_type.name}</dd>
                                </div>
                            )}
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Afecta IGV</dt>
                                <dd className="mt-1 text-sm">{product.has_igv ? 'Sí' : 'No'}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="space-y-4 rounded-lg border p-6">
                        <h2 className="text-lg font-semibold">Precios</h2>
                        <dl className="space-y-3">
                            <div>
                                <dt className="text-sm font-medium text-muted-foreground">Precio de Venta</dt>
                                <dd className="mt-1 text-2xl font-bold">
                                    S/ {parseFloat(product.sale_price).toFixed(2)}
                                </dd>
                            </div>
                            {product.purchase_price && (
                                <div>
                                    <dt className="text-sm font-medium text-muted-foreground">Precio de Compra</dt>
                                    <dd className="mt-1 text-xl font-semibold">
                                        S/ {parseFloat(product.purchase_price).toFixed(2)}
                                    </dd>
                                </div>
                            )}
                        </dl>
                    </div>
                </div>

                {product.inventory_stocks && product.inventory_stocks.length > 0 && (
                    <div className="rounded-lg border p-6">
                        <h2 className="mb-4 text-lg font-semibold">Inventario</h2>
                        <div className="space-y-2">
                            {product.inventory_stocks.map((stock, index) => (
                                <div
                                    key={index}
                                    className="flex items-center justify-between rounded-md border p-3"
                                >
                                    <span className="font-medium">{stock.warehouse.name}</span>
                                    <span className="text-lg font-semibold">
                                        {parseFloat(stock.quantity).toFixed(2)} {product.unit?.code || 'NIU'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="rounded-lg border p-6">
                    <h2 className="mb-4 text-lg font-semibold">Información del Sistema</h2>
                    <dl className="space-y-3">
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Fecha de Creación</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(product.created_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-muted-foreground">Última Actualización</dt>
                            <dd className="mt-1 text-sm">
                                {new Date(product.updated_at).toLocaleString('es-PE')}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </AppLayout>
    );
}



