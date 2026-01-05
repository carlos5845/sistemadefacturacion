import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { BarChart3, CheckCircle2, FileText, ShieldCheck } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Bienvenido" />

            {/* Main Wrapper - Black Background */}
            <div className="min-h-screen bg-black text-zinc-100 selection:bg-blue-600 selection:text-white">
                {/* Navbar */}
                <header className="sticky top-0 z-50 border-b border-zinc-900 bg-black/50 backdrop-blur-sm">
                    <div className="container mx-auto flex h-16 items-center justify-between px-6">
                        {/* Logo */}
                        <div className="flex items-center gap-2 text-xl font-bold tracking-tighter">
                            <div className="flex size-8 items-center justify-center rounded-lg bg-blue-600">
                                <FileText className="size-5 text-white" />
                            </div>
                            <span>
                                Factura
                                <span className="text-blue-600">Pro</span>
                            </span>
                        </div>

                        {/* Auth Links */}
                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="inline-flex h-9 items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-blue-500 focus-visible:ring-1 focus-visible:ring-blue-600 focus-visible:outline-none"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="text-sm font-medium text-zinc-400 transition-colors hover:text-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={register()}
                                        className="inline-flex h-9 items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-medium text-black shadow transition-colors hover:bg-zinc-200 focus-visible:ring-1 focus-visible:ring-white focus-visible:outline-none"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                {/* Hero Section */}
                <section className="container mx-auto flex flex-col items-center justify-center px-6 py-24 text-center lg:py-32">
                    <div className="mb-6 inline-flex items-center rounded-full border border-zinc-800 bg-zinc-900/50 px-3 py-1 text-sm text-zinc-400">
                        <span className="mr-2 flex size-2 animate-pulse rounded-full bg-blue-500"></span>
                        Sistema de Facturación 2.0
                    </div>

                    <h1 className="mb-6 max-w-4xl text-5xl font-bold tracking-tight text-white lg:text-7xl">
                        Facturación simple, <br />
                        <span className="text-blue-600">
                            sin complicaciones.
                        </span>
                    </h1>

                    <p className="mb-10 max-w-2xl text-lg text-zinc-400">
                        Optimiza tu flujo de trabajo con nuestra plataforma
                        minimalista. Genera facturas, gestiona clientes y
                        controla tu inventario con una interfaz diseñada para la
                        eficiencia.
                    </p>

                    <div className="flex flex-col gap-4 sm:flex-row">
                        <Link
                            href={auth.user ? dashboard() : register()}
                            className="inline-flex h-12 items-center justify-center rounded-md bg-blue-600 px-8 text-sm font-medium text-white shadow transition-colors hover:bg-blue-500 focus-visible:ring-1 focus-visible:ring-blue-600 focus-visible:outline-none"
                        >
                            Comenzar Ahora
                        </Link>
                        <a
                            href="#features"
                            className="inline-flex h-12 items-center justify-center rounded-md border border-zinc-800 bg-transparent px-8 text-sm font-medium text-zinc-300 shadow-sm transition-colors hover:bg-zinc-900 focus-visible:ring-1 focus-visible:ring-zinc-300 focus-visible:outline-none"
                        >
                            Conocer más
                        </a>
                    </div>
                </section>

                {/* Features Grid */}
                <section
                    id="features"
                    className="container mx-auto border-t border-zinc-900 px-6 py-24"
                >
                    <div className="grid gap-8 md:grid-cols-3">
                        {/* Feature 1 */}
                        <div className="group rounded-2xl border border-zinc-900 bg-zinc-950 p-8 transition-colors hover:border-blue-900/50">
                            <div className="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-blue-900/20 text-blue-500">
                                <ShieldCheck className="size-5" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-white">
                                Seguridad Total
                            </h3>
                            <p className="text-zinc-400">
                                Tus datos están encriptados y protegidos.
                                Cumplimos con los estándares más altos de
                                seguridad.
                            </p>
                        </div>

                        {/* Feature 2 */}
                        <div className="group rounded-2xl border border-zinc-900 bg-zinc-950 p-8 transition-colors hover:border-blue-900/50">
                            <div className="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-blue-900/20 text-blue-500">
                                <BarChart3 className="size-5" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-white">
                                Reportes en Tiempo Real
                            </h3>
                            <p className="text-zinc-400">
                                Visualiza tus ventas, impuestos y rendimiento
                                con gráficos claros y precisos al instante.
                            </p>
                        </div>

                        {/* Feature 3 */}
                        <div className="group rounded-2xl border border-zinc-900 bg-zinc-950 p-8 transition-colors hover:border-blue-900/50">
                            <div className="mb-4 inline-flex size-10 items-center justify-center rounded-lg bg-blue-900/20 text-blue-500">
                                <CheckCircle2 className="size-5" />
                            </div>
                            <h3 className="mb-2 text-xl font-bold text-white">
                                Fácil de Usar
                            </h3>
                            <p className="text-zinc-400">
                                Interfaz intuitiva que no requiere capacitación.
                                Empieza a facturar en minutos.
                            </p>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-zinc-900 bg-black py-12">
                    <div className="container mx-auto flex flex-col items-center justify-between gap-6 px-6 md:flex-row">
                        <div className="text-sm text-zinc-500">
                            © 2024 FacturaPro. Todos los derechos reservados.
                        </div>
                        <div className="flex gap-6 text-sm text-zinc-500">
                            <a
                                href="#"
                                className="transition-colors hover:text-blue-500"
                            >
                                Términos
                            </a>
                            <a
                                href="#"
                                className="transition-colors hover:text-blue-500"
                            >
                                Privacidad
                            </a>
                            <a
                                href="#"
                                className="transition-colors hover:text-blue-500"
                            >
                                Contacto
                            </a>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
