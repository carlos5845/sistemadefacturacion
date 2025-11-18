import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/companies';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Empresas',
        href: index().url,
    },
    {
        title: 'Nueva Empresa',
        href: CompanyController.create.url(),
    },
];

export default function CompaniesCreate() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Nueva Empresa" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Nueva Empresa</h1>
                    <p className="text-muted-foreground">
                        Registra una nueva empresa en el sistema
                    </p>
                </div>

                <Form
                    {...CompanyController.store.form()}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="ruc">RUC *</Label>
                                    <Input
                                        id="ruc"
                                        name="ruc"
                                        type="text"
                                        maxLength={11}
                                        placeholder="20123456789"
                                        required
                                        aria-invalid={errors.ruc ? 'true' : undefined}
                                    />
                                    <InputError message={errors.ruc} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="business_name">Razón Social *</Label>
                                    <Input
                                        id="business_name"
                                        name="business_name"
                                        type="text"
                                        placeholder="Empresa S.A.C."
                                        required
                                        aria-invalid={errors.business_name ? 'true' : undefined}
                                    />
                                    <InputError message={errors.business_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="trade_name">Nombre Comercial</Label>
                                    <Input
                                        id="trade_name"
                                        name="trade_name"
                                        type="text"
                                        placeholder="Nombre Comercial"
                                        aria-invalid={errors.trade_name ? 'true' : undefined}
                                    />
                                    <InputError message={errors.trade_name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="ubigeo">Ubigeo</Label>
                                    <Input
                                        id="ubigeo"
                                        name="ubigeo"
                                        type="text"
                                        maxLength={6}
                                        placeholder="150101"
                                        aria-invalid={errors.ubigeo ? 'true' : undefined}
                                    />
                                    <InputError message={errors.ubigeo} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="address">Dirección</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        type="text"
                                        placeholder="Av. Principal 123"
                                        aria-invalid={errors.address ? 'true' : undefined}
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="user_sol">Usuario SOL</Label>
                                    <Input
                                        id="user_sol"
                                        name="user_sol"
                                        type="text"
                                        placeholder="Usuario SOL"
                                        aria-invalid={errors.user_sol ? 'true' : undefined}
                                    />
                                    <InputError message={errors.user_sol} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_sol">Contraseña SOL</Label>
                                    <Input
                                        id="password_sol"
                                        name="password_sol"
                                        type="password"
                                        placeholder="Contraseña SOL"
                                        aria-invalid={errors.password_sol ? 'true' : undefined}
                                    />
                                    <InputError message={errors.password_sol} />
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
                                {recentlySuccessful && (
                                    <p className="text-sm text-green-600">
                                        Empresa creada exitosamente
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



