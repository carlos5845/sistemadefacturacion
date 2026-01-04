import { usePage } from '@inertiajs/react';

export function useTranslation() {
    const { translations } = usePage().props as any;

    const t = (key: string, replace?: Record<string, string>) => {
        let translation = translations[key] || key;

        if (replace) {
            Object.keys(replace).forEach((search) => {
                translation = translation.replace(
                    `:${search}`,
                    replace[search],
                );
            });
        }

        return translation;
    };

    return { t };
}
