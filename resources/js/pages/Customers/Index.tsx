import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Eye, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { create, index } from '@/routes/customers';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: index().url,
    },
];

interface Customer {
    id: number;
    identity_type: string;
    identity_number: string;
    name: string;
    email: string | null;
    phone: string | null;
    created_at: string;
}

interface Props {
    customers: {
        data: Customer[];
        links: any;
        meta: any;
    };
    filters: {
        search?: string;
    };
    error?: string;
}

export default function CustomersIndex({ customers, filters, error }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
    const [customerToDelete, setCustomerToDelete] = useState<Customer | null>(
        null,
    );
    const [isDeleting, setIsDeleting] = useState(false);
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };

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

    const getIdentityTypeLabel = (type: string) => {
        if (type === '1' || type === 'DNI') return 'DNI';
        if (type === '6' || type === 'RUC') return 'RUC';
        if (type === '4' || type === 'CE') return 'CE';
        if (type === '7' || type === 'PAS') return 'PAS';
        return type;
    };

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(index().url, { search }, { preserveState: true });
    };

    const handleDeleteClick = (customer: Customer) => {
        setCustomerToDelete(customer);
        setDeleteDialogOpen(true);
    };

    const handleDeleteConfirm = () => {
        if (!customerToDelete) {
            return;
        }

        setIsDeleting(true);
        router.delete(CustomerController.destroy.url(customerToDelete.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteDialogOpen(false);
                setCustomerToDelete(null);
                setIsDeleting(false);
                setSuccessMessage('Cliente eliminado exitosamente.');
                setShowSuccessModal(true);
            },
            onError: (errors) => {
                setIsDeleting(false);
                const errorMsg =
                    typeof errors === 'string'
                        ? errors
                        : 'Error al eliminar el cliente.';
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
            <Head title="Clientes" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold">Clientes</h1>
                        <p className="text-muted-foreground">
                            Gestiona los clientes de tu empresa
                        </p>
                    </div>
                    <Link href={create().url}>
                        <Button>Nuevo Cliente</Button>
                    </Link>
                </div>

                {error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSearch} className="flex gap-2">
                    <Input
                        type="text"
                        placeholder="Buscar por nombre o documento..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="max-w-sm"
                    />
                    <Button type="submit">Buscar</Button>
                </form>

                <div className="rounded-lg border">
                    <table className="w-full">
                        <thead>
                            <tr className="border-b">
                                <th className="px-4 py-3 text-left">Tipo</th>
                                <th className="px-4 py-3 text-left">
                                    Documento
                                </th>
                                <th className="px-4 py-3 text-left">Nombre</th>
                                <th className="px-4 py-3 text-left">Email</th>
                                <th className="px-4 py-3 text-left">
                                    Teléfono
                                </th>
                                <th className="px-4 py-3 text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {customers.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="px-4 py-8 text-center text-muted-foreground"
                                    >
                                        No hay clientes registrados
                                    </td>
                                </tr>
                            ) : (
                                customers.data.map((customer) => (
                                    <tr key={customer.id} className="border-b">
                                        <td className="px-4 py-3">
                                            {getIdentityTypeLabel(
                                                customer.identity_type,
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {customer.identity_number}
                                        </td>
                                        <td className="px-4 py-3">
                                            {customer.name}
                                        </td>
                                        <td className="px-4 py-3">
                                            {customer.email || '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {customer.phone || '-'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center justify-end gap-2">
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <Link
                                                                href={CustomerController.show.url(
                                                                    customer.id,
                                                                )}
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-primary transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                                            >
                                                                <Eye className="h-4 w-4" />
                                                                <span className="sr-only">
                                                                    Ver cliente
                                                                </span>
                                                            </Link>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>Ver cliente</p>
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger asChild>
                                                            <button
                                                                onClick={() =>
                                                                    handleDeleteClick(
                                                                        customer,
                                                                    )
                                                                }
                                                                className="inline-flex items-center justify-center rounded-md p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-800 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none dark:text-red-400 dark:hover:bg-red-950 dark:hover:text-red-300"
                                                                type="button"
                                                            >
                                                                <Trash2 className="h-4 w-4" />
                                                                <span className="sr-only">
                                                                    Eliminar
                                                                    cliente
                                                                </span>
                                                            </button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>
                                                            <p>
                                                                Eliminar cliente
                                                            </p>
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
                <Dialog
                    open={deleteDialogOpen}
                    onOpenChange={setDeleteDialogOpen}
                >
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>¿Eliminar cliente?</DialogTitle>
                            <DialogDescription>
                                ¿Estás seguro de que deseas eliminar a{' '}
                                <strong>{customerToDelete?.name}</strong>?
                                <br />
                                Esta acción no se puede deshacer.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setDeleteDialogOpen(false);
                                    setCustomerToDelete(null);
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
