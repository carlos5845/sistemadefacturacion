import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Meta {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface Props {
    links: PaginationLink[];
    meta?: Meta;
}

export default function Pagination({ links, meta }: Props) {
    if (links.length <= 1) return null;

    return (
        <div className="flex flex-col items-center justify-between gap-4 py-4 sm:flex-row">
            {meta && (
                <div className="text-sm whitespace-nowrap text-muted-foreground">
                    Mostrando{' '}
                    <span className="font-medium">{meta.from || 0}</span> a{' '}
                    <span className="font-medium">{meta.to || 0}</span> de{' '}
                    <span className="font-medium">{meta.total}</span> resultados
                </div>
            )}

            <div className="flex flex-wrap justify-center gap-2">
                {links.map((link, key) => {
                    let label = link.label;
                    let icon = null;

                    // Clean label and detect icons
                    // We check for various common Laravel pagination outputs including translation keys
                    if (
                        label.includes('&laquo;') ||
                        label.includes('Previous') ||
                        label.includes('Anterior') ||
                        label.includes('pagination.previous')
                    ) {
                        label = '';
                        icon = <ChevronLeft className="h-4 w-4" />;
                    } else if (
                        label.includes('&raquo;') ||
                        label.includes('Next') ||
                        label.includes('Siguiente') ||
                        label.includes('pagination.next')
                    ) {
                        label = '';
                        icon = <ChevronRight className="h-4 w-4" />;
                    }

                    // Handle "..." separator
                    if (link.url === null && link.label === '...') {
                        return (
                            <Button
                                key={key}
                                variant="ghost"
                                size="icon"
                                disabled
                                className="h-9 w-9 cursor-default"
                            >
                                <MoreHorizontal className="h-4 w-4" />
                                <span className="sr-only">Más páginas</span>
                            </Button>
                        );
                    }

                    // Active state classes for styling
                    const activeClasses = link.active
                        ? 'bg-zinc-900 text-zinc-50 hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90'
                        : 'text-muted-foreground hover:text-foreground';

                    // Render disabled button (for current page or disabled prev/next)
                    if (link.url === null) {
                        // If it's a Previous/Next button and disabled (null url), don't render it at all
                        // This matches the user request to only show them "provided there is missing data"
                        if (icon) return null;

                        return (
                            <Button
                                key={key}
                                variant="ghost"
                                size="icon"
                                disabled
                                className={cn(
                                    'h-9 w-9',
                                    !icon && 'min-w-[2.25rem] px-3 font-medium',
                                )}
                            >
                                {icon || (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: label,
                                        }}
                                    />
                                )}
                            </Button>
                        );
                    }

                    // Render clickable link using asChild
                    // Note: We avoid nested interactive elements by using asChild on Button
                    // and putting the Link inside.
                    return (
                        <Button
                            key={key}
                            variant={link.active ? 'default' : 'ghost'}
                            size={icon ? 'icon' : 'default'}
                            asChild
                            className={cn(
                                'h-9 w-9 p-0',
                                !icon && 'min-w-[2.25rem] px-3 font-medium',
                                link.active ? activeClasses : '',
                            )}
                        >
                            <Link
                                href={link.url}
                                preserveState
                                preserveScroll
                                only={[
                                    'companies',
                                    'customers',
                                    'products',
                                    'documents',
                                ]} // Optimize partial reloads if possible, but optional
                            >
                                {icon || (
                                    <span
                                        dangerouslySetInnerHTML={{
                                            __html: label,
                                        }}
                                    />
                                )}
                            </Link>
                        </Button>
                    );
                })}
            </div>
        </div>
    );
}
