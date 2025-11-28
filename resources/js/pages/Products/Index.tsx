import ProductController from '@/actions/App/Http/Controllers/ProductController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Eye, Trash2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { SuccessModal } from '@/components/success-modal';
import { ErrorModal } from '@/components/error-modal';
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
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [productToDelete, setProductToDelete] = useState<Product | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };

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
        router.get(index().url, {
            search,
            category_id: categoryId || undefined,
            active: active !== '' ? active === 'true' : undefined,
        }, { preserveState: true });
    };

    const handleDeleteClick = (product: Product) => {
        setProductToDelete(product);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (!productToDelete) {
            return;
        }

        setIsDeleting(true);
        router.delete(ProductController.destroy.url(productToDelete.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteDialogOpen(false);
                setProductToDelete(null);
                setIsDeleting(false);
                setSuccessMessage('Producto eliminado exitosamente.');
                setShowSuccessModal(true);
            },
            onError: (errors) => {
                setIsDeleting(false);
                const errorMsg = typeof errors === 'string' ? errors : 'Error al eliminar el producto.';
                setErrorMessage(errorMsg);
                setShowErrorModal(true);
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
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
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <Link
                                                                href={ProductController.show.url(product.id)}
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-primary transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                                <span className="sr-only">Ver producto</span>
                                                            </Link>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>Ver producto</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <button
                                                                onClick={() => handleDeleteClick(product)}
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300"
                                                                type="button"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                                <span className="sr-only">Eliminar producto</span>
                                                            </button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>Eliminar producto</p>
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
                <Dialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>¿Eliminar producto?</DialogTitle>
                            <DialogDescription>
                                ¿Estás seguro de que deseas eliminar el producto{' '}
                                <strong>{productToDelete?.name}</strong>?
                                <br />
                                Esta acción no se puede deshacer.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setDeleteDialogOpen(false);
                                    setProductToDelete(null);
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



