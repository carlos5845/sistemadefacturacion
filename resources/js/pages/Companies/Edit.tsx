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
    has_password_sol?: boolean;
    has_certificate?: boolean;
    has_certificate_password?: boolean;
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
                        certificate: '',
                        certificate_password: '',
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
                                        defaultValue={company.ruc}
                                        aria-invalid={
                                            errors.ruc ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.ruc} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="business_name">
                                        Razón Social *
                                    </Label>
                                    <Input
                                        id="business_name"
                                        name="business_name"
                                        type="text"
                                        required
                                        defaultValue={company.business_name}
                                        aria-invalid={
                                            errors.business_name
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        message={errors.business_name}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="trade_name">
                                        Nombre Comercial
                                    </Label>
                                    <Input
                                        id="trade_name"
                                        name="trade_name"
                                        type="text"
                                        defaultValue={company.trade_name || ''}
                                        aria-invalid={
                                            errors.trade_name
                                                ? 'true'
                                                : undefined
                                        }
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
                                        defaultValue={company.ubigeo || ''}
                                        aria-invalid={
                                            errors.ubigeo ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.ubigeo} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="address">Dirección</Label>
                                    <Input
                                        id="address"
                                        name="address"
                                        type="text"
                                        defaultValue={company.address || ''}
                                        aria-invalid={
                                            errors.address ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="user_sol">
                                        Usuario SOL
                                    </Label>
                                    <Input
                                        id="user_sol"
                                        name="user_sol"
                                        type="text"
                                        defaultValue={company.user_sol || ''}
                                        aria-invalid={
                                            errors.user_sol ? 'true' : undefined
                                        }
                                    />
                                    <InputError message={errors.user_sol} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_sol">
                                        Contraseña SOL (dejar vacío para no
                                        cambiar)
                                    </Label>
                                    <Input
                                        id="password_sol"
                                        name="password_sol"
                                        type="password"
                                        placeholder={
                                            company.has_password_sol
                                                ? '•••••••• (configurada)'
                                                : ''
                                        }
                                        aria-invalid={
                                            errors.password_sol
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    {company.has_password_sol && (
                                        <p className="text-xs text-blue-600 dark:text-blue-400">
                                            ℹ️ Ya existe una contraseña SOL
                                            configurada. Déjela vacía para
                                            mantener la actual.
                                        </p>
                                    )}
                                    <InputError message={errors.password_sol} />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="certificate_file">
                                        Certificado Digital PFX/P12 (Requerido
                                        por SUNAT) - Opcional
                                    </Label>
                                    <input
                                        id="certificate_file"
                                        name="certificate_file"
                                        type="file"
                                        accept=".p12,.pfx"
                                        className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1 file:text-sm file:font-medium file:text-primary-foreground file:transition-colors hover:file:bg-primary/90 focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        aria-invalid={
                                            errors.certificate_file
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    {company.has_certificate && (
                                        <p className="text-xs text-blue-600 dark:text-blue-400">
                                            ℹ️ Ya existe un certificado
                                            configurado. Déjelo vacío para
                                            mantener el actual o suba un nuevo
                                            archivo PFX/P12 para reemplazarlo.
                                        </p>
                                    )}
                                    <p className="text-xs text-muted-foreground">
                                        <strong>Requerido por SUNAT:</strong>{' '}
                                        Suba el archivo de certificado digital
                                        en formato PFX/P12 (.p12 o .pfx).
                                        <br />
                                        <strong>Nota:</strong> El certificado
                                        debe ser emitido por una entidad
                                        certificadora autorizada por SUNAT.
                                        <br />
                                        <strong>Para desarrollo:</strong> Puede
                                        dejarlo vacío. El sistema funcionará en
                                        modo simulación y generará XML sin
                                        firmar.
                                        <br />
                                        Si ya tiene un certificado configurado,
                                        déjelo vacío para mantener el actual.
                                    </p>
                                    <InputError
                                        message={errors.certificate_file}
                                    />
                                </div>

                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="certificate">
                                        Certificado Digital (PEM) - Alternativa
                                        - Opcional
                                    </Label>
                                    <textarea
                                        id="certificate"
                                        name="certificate"
                                        rows={6}
                                        className="flex min-h-[120px] w-full rounded-md border border-input bg-transparent px-3 py-2 font-mono text-xs shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                        placeholder="-----BEGIN CERTIFICATE-----&#10;...&#10;-----END CERTIFICATE-----&#10;&#10;-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"
                                        defaultValue=""
                                        aria-invalid={
                                            errors.certificate
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    <p className="text-xs text-muted-foreground">
                                        <strong>Alternativa:</strong> Si
                                        prefiere, puede pegar el contenido del
                                        certificado en formato PEM.
                                        <br />
                                        <strong>Recomendado:</strong> Use el
                                        formato PFX/P12 (opción anterior) que es
                                        el requerido por SUNAT.
                                    </p>
                                    <InputError message={errors.certificate} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="certificate_password">
                                        Contraseña del Certificado (solo si está
                                        subiendo un nuevo certificado)
                                    </Label>
                                    <Input
                                        id="certificate_password"
                                        name="certificate_password"
                                        type="password"
                                        placeholder={
                                            company.has_certificate_password
                                                ? '•••••••• (configurada)'
                                                : ''
                                        }
                                        aria-invalid={
                                            errors.certificate_password
                                                ? 'true'
                                                : undefined
                                        }
                                    />
                                    {company.has_certificate_password && (
                                        <p className="text-xs text-blue-600 dark:text-blue-400">
                                            ℹ️ Ya existe una contraseña de
                                            certificado configurada. Solo
                                            ingrésela si está subiendo un nuevo
                                            certificado.
                                        </p>
                                    )}
                                    <InputError
                                        message={errors.certificate_password}
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing
                                        ? 'Guardando...'
                                        : 'Guardar Cambios'}
                                </Button>
                                <Link
                                    href={CompanyController.show.url(
                                        company.id,
                                    )}
                                >
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
