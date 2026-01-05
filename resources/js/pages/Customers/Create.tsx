import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
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
    const [searching, setSearching] = useState(false);

    const { data, setData, post, processing, errors } = useForm({
        identity_type: '',
        identity_number: '',
        name: '',
        address: '',
        email: '',
        phone: '',
    });

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

    const getIdentityType = (type: string) => {
        if (type === 'DNI') return 'dni';
        if (type === 'RUC') return 'ruc';
        return null;
    };

    const searchDocument = async () => {
        const type = getIdentityType(data.identity_type);
        if (!type || !data.identity_number) return;

        if (type === 'dni' && data.identity_number.length !== 8) return;
        if (type === 'ruc' && data.identity_number.length !== 11) return;

        setSearching(true);
        try {
            const response = await fetch(
                `/consult/${type}/${data.identity_number}`,
            );
            if (response.ok) {
                const result = await response.json();
                setData((prev) => ({
                    ...prev,
                    name: result.name || prev.name,
                    address:
                        type === 'ruc' && result.address
                            ? result.address
                            : prev.address,
                }));
            } else {
                console.error('Documento no encontrado');
            }
        } catch (error) {
            console.error('Error searching document', error);
        } finally {
            setSearching(false);
        }
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(CustomerController.store.url(), {
            preserveScroll: true,
        });
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

                <form onSubmit={submit} className="space-y-6">
                    {(errors as any).error && (
                        <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                            {(errors as any).error}
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
                                className="flex h-9 w-full rounded-md border border-zinc-300 bg-white px-3 py-1 text-base text-zinc-900 shadow-xs transition-[color,box-shadow] outline-none placeholder:text-zinc-400 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 md:text-sm dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-100 dark:placeholder:text-zinc-500"
                                required
                                aria-invalid={
                                    errors.identity_type ? 'true' : undefined
                                }
                                value={data.identity_type}
                                onChange={(e) =>
                                    setData('identity_type', e.target.value)
                                }
                            >
                                <option value="">Seleccione...</option>
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                            </select>
                            <InputError message={errors.identity_type} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="identity_number">
                                Número de Documento *
                            </Label>
                            <div className="relative">
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
                                    value={data.identity_number}
                                    onChange={(e) =>
                                        setData(
                                            'identity_number',
                                            e.target.value,
                                        )
                                    }
                                />
                                <Button
                                    type="button"
                                    size="icon"
                                    variant="ghost"
                                    className="absolute top-0 right-0 h-full px-3 py-2 hover:bg-transparent"
                                    onClick={searchDocument}
                                    disabled={searching}
                                >
                                    <Search
                                        className={`h-4 w-4 ${searching ? 'animate-spin' : ''}`}
                                    />
                                </Button>
                            </div>
                            <InputError message={errors.identity_number} />
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
                                aria-invalid={errors.name ? 'true' : undefined}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
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
                                value={data.address}
                                onChange={(e) =>
                                    setData('address', e.target.value)
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
                                aria-invalid={errors.email ? 'true' : undefined}
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
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
                                aria-invalid={errors.phone ? 'true' : undefined}
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
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
                </form>
            </div>
        </AppLayout>
    );
}
