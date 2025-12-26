import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

import { ErrorModal } from '@/components/error-modal';
import InputError from '@/components/input-error';
import { SuccessModal } from '@/components/success-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: index().url,
    },
    {
        title: 'Nuevo Cliente',
        href: CustomerController.create.url(),
    },
];

interface Props {
    error?: string;
}

export default function CustomersCreate({ error: propError }: Props) {
    const { flash } = usePage().props as {
        flash?: { success?: string; error?: string };
    };
    const successMessage = flash?.success || '';
    const errorMessage = flash?.error || propError || '';
    const [showSuccessModal, setShowSuccessModal] = useState(false);
    const [showErrorModal, setShowErrorModal] = useState(false);

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nuevo Cliente" />

            <SuccessModal
                open={showSuccessModal}
                onClose={handleSuccessClose}
                title="Cliente Creado"
                message={successMessage}
            />

            <ErrorModal
                open={showErrorModal}
                onClose={handleErrorClose}
                title="Error al Crear Cliente"
                message={errorMessage}
            />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Nuevo Cliente</h1>
                    <p className="text-muted-foreground">
                        Registra un nuevo cliente en el sistema
                    </p>
                </div>

                {propError && (
                    <div className="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-200">
                        {propError}
                    </div>
                )}

                <Form
                    {...CustomerController.store.form()}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                >
                    {({ processing, errors }) => (
                        <>
                            {errors.error && (
                                <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                                    {errors.error}
                                </div>
                            )}

                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="identity_type">
                                        Tipo de Documento *
                                    </Label>
                                    <select
                                        id="identity_type"
                                        name="identity_type"
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm"
                                        required
                                        aria-invalid={
                                            errors.identity_type
                                                ? 'true'
                                                : undefined
                                        }
                                    >
                                        <option value="">Seleccione...</option>
                                        <option value="1">DNI</option>
                                        <option value="6">RUC</option>
                                        <option value="4">
                                            Carnet de Extranjería
                                        </option>
                                        <option value="7">Pasaporte</option>
                                    </select>
                                    <InputError
                                        message={errors.identity_type}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="identity_number">
                                        Número de Documento *
                                    </Label>
                                    <Input
                                        id="identity_number"
                                        name="identity_number"
                                        type="text"
                                        maxLength={15}
                                        placeholder="12345678"
                                        required
                                        aria-invalid={
                                            errors.identity_number
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        message={errors.identity_number}
                                    />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="name">
                                        Nombre / Razón Social *
                                    </Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        type="text"
                                        placeholder="Juan Pérez"
                                        required
                                        aria-invalid={
                                            errors.name ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="address">Dirección</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        type="text"
                                        placeholder="Av. Principal 123"
                                        aria-invalid={
                                            errors.address ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        placeholder="cliente@example.com"
                                        aria-invalid={
                                            errors.email ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Teléfono</Label>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        placeholder="987654321"
                                        maxLength={20}
                                        aria-invalid={
                                            errors.phone ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.phone} />
                                </div>
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
