import CompanyController from '@/actions/App/Http/Controllers/CompanyController';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, Link } from '@inertiajs/react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { index } from '@/routes/companies';

interface Company {
    id: number;
    ruc: string;
    business_name: string;
    trade_name: string | null;
    address: string | null;
    ubigeo: string | null;
    user_sol: string | null;
    password_sol: string | null;
}

interface Props {
    company: Company;
}

const breadcrumbs = (company: Company): BreadcrumbItem[] => [
    {
        title: 'Empresas',
        href: index().url,
    },
    {
        title: company.business_name,
        href: CompanyController.show.url(company.id),
    },
    {
        title: 'Editar',
        href: CompanyController.edit.url(company.id),
    },
];

export default function CompaniesEdit({ company }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs(company)}>
            <Head title={`Editar: ${company.business_name}`} />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Editar Empresa</h1>
                    <p className="text-muted-foreground">
                        Actualiza la información de la empresa
                    </p>
                </div>

                <Form
                    {...CompanyController.update.form(company.id)}
                    className="space-y-6"
                    options={{
                        preserveScroll: true,
                    }}
                    defaults={{
                        ruc: company.ruc,
                        business_name: company.business_name,
                        trade_name: company.trade_name || '',
                        address: company.address || '',
                        ubigeo: company.ubigeo || '',
                        user_sol: company.user_sol || '',
                        password_sol: '',
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
                                        aria-invalid={errors.user_sol ? 'true' : undefined}
                                    />
                                    <InputError message={errors.user_sol} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_sol">Contraseña SOL (dejar vacío para no cambiar)</Label>
                                    <Input
                                        id="password_sol"
                                        name="password_sol"
                                        type="password"
                                        aria-invalid={errors.password_sol ? 'true' : undefined}
                                    />
                                    <InputError message={errors.password_sol} />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Guardando...' : 'Guardar Cambios'}
                                </Button>
                                <Link href={CompanyController.show.url(company.id)}>
                                    <Button type="button" variant="outline">
                                        Cancelar
                                    </Button>
                                </Link>
                                {recentlySuccessful && (
                                    <p className="text-sm text-green-600">
                                        Empresa actualizada exitosamente
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



