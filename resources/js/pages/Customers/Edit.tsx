import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/customers';

interface Customer {
    id: number;
    identity_type: string;
    identity_number: string;
    name: string;
    address: string | null;
    email: string | null;
    phone: string | null;
}

interface Props {
    customer: Customer;
}

const breadcrumbs = (customer: Customer): BreadcrumbItem[] => [
    {
        title: 'Clientes',
        href: index().url,
    },
    {
        title: customer.name,
        href: CustomerController.show.url(customer.id),
    },
    {
        title: 'Editar',
        href: CustomerController.edit.url(customer.id),
    },
];

export default function CustomersEdit({ customer }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(customer)}>
            <Head title={`Editar: ${customer.name}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Editar Cliente</h1>
                    <p className="text-muted-foreground">
                        Actualiza la información del cliente
                    </p>
                </div>

                <Form
                    {...CustomerController.update.form(customer.id)}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                    defaults={{
                        identity_type: customer.identity_type,
                        identity_number: customer.identity_number,
                        name: customer.name,
                        address: customer.address || '',
                        email: customer.email || '',
                        phone: customer.phone || '',
                    }}
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="identity_type">Tipo de Documento *</Label>
                                    <select
                                        id="identity_type"
                                        name="identity_type"
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] md:text-sm"
                                        required
                                        aria-invalid={errors.identity_type ? 'true' : undefined}
                                    >
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">Carnet de Extranjería</option>
                                        <option value="PAS">Pasaporte</option>
                                    </select>
                                    <InputError message={errors.identity_type} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="identity_number">Número de Documento *</Label>
                                    <Input
                                        id="identity_number"
                                        name="identity_number"
                                        type="text"
                                        maxLength={15}
                                        required
                                        aria-invalid={errors.identity_number ? 'true' : undefined}
                                    />
                                    <InputError message={errors.identity_number} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="name">Nombre / Razón Social *</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        type="text"
                                        required
                                        aria-invalid={errors.name ? 'true' : undefined}
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="address">Dirección</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        type="text"
                                        aria-invalid={errors.address ? 'true' : undefined}
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        aria-invalid={errors.email ? 'true' : undefined}
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Teléfono</Label>
                                    <Input
                                        id="phone"
                                        name="phone"
                                        type="tel"
                                        maxLength={20}
                                        aria-invalid={errors.phone ? 'true' : undefined}
                                    />
                                    <InputError message={errors.phone} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Guardando...' : 'Guardar Cambios'}
                                </Button>
                                <Link href={CustomerController.show.url(customer.id)}>
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                                {recentlySuccessful && (
                                    <p className="text-sm text-green-600">
                                        Cliente actualizado exitosamente
                                    </p>
                                )}
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}



