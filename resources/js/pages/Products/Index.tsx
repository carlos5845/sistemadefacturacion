import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { index, create } from '@/routes/products';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: index().url,
    },
];

interface Product {
    id: number;
    name: string;
    sale_price: string;
    active: boolean;
    category?: { name: string };
    unit?: { name: string };
}

interface Props {
    products: {
        data: Product[];
        links: any;
        meta: any;
    };
    categories: Array<{
        id: number;
        name: string;
    }>;
    filters: {
        search?: string;
        category_id?: number;
        active?: boolean;
    };
    error?: string;
}

export default function ProductsIndex({ products, categories, filters, error }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [categoryId, setCategoryId] = useState(filters.category_id?.toString() || '');
    const [active, setActive] = useState(filters.active?.toString() || '');

    const handleFilter = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(index().url, {
            search,
            category_id: categoryId || undefined,
            active: active !== '' ? active === 'true' : undefined,
        }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Productos" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Productos</h1>
                        <p className="text-muted-foreground">
                            Gestiona los productos de tu empresa
                        </p>
                    </div>
                    <Link href={create().url}>
                        <Button>Nuevo Producto</Button>
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
                        placeholder="Buscar por nombre..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-sm"
                    />
                    <select
                        value={categoryId}
                        onChange={(e) => setCategoryId(e.target.value)}
                        className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                    >
                        <option value="">Todas las categorías</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id.toString()}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                    <select
                        value={active}
                        onChange={(e) => setActive(e.target.value)}
                        className="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                    >
                        <option value="">Todos</option>
                        <option value="true">Activos</option>
                        <option value="false">Inactivos</option>
                    </select>
                    <Button type="submit">Filtrar</Button>
                </form>

                <div className="rounded-lg border">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b">
                                <th className="px-4 py-3 text-left">Nombre</th>
                                <th className="px-4 py-3 text-left">Categoría</th>
                                <th className="px-4 py-3 text-left">Unidad</th>
                                <th className="px-4 py-3 text-right">Precio Venta</th>
                                <th className="px-4 py-3 text-left">Estado</th>
                                <th className="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {products.data.length === 0 ? (
                                <tr>
                                    <td colSpan={6} className="px-4 py-8 text-center text-muted-foreground">
                                        No hay productos registrados
                                    </td>
                                </tr>
                            ) : (
                                products.data.map((product) => (
                                    <tr key={product.id} className="border-b">
                                        <td className="px-4 py-3 font-medium">{product.name}</td>
                                        <td className="px-4 py-3">{product.category?.name || '-'}</td>
                                        <td className="px-4 py-3">{product.unit?.name || '-'}</td>
                                        <td className="px-4 py-3 text-right">
                                            S/ {parseFloat(product.sale_price).toFixed(2)}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={`rounded-full px-2 py-1 text-xs ${
                                                    product.active
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                                }`}
                                            >
                                                {product.active ? 'Activo' : 'Inactivo'}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={ProductController.show.url(product.id)}
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



