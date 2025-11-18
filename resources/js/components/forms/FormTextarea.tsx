import FormField from '@/components/forms/FormField';
import { cn } from '@/lib/utils';
import { type ComponentProps } from 'react';

interface FormTextareaProps extends ComponentProps<'textarea'> {
    label: string;
    error?: string;
    required?: boolean;
}

export default function FormTextarea({
    label,
    error,
    required,
    className,
    ...props
}: FormTextareaProps) {
    return (
        <FormField label={label} error={error} required={required} className={className}>
            <textarea
                {...props}
                className={cn(
                    'flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                    className
                )}
                aria-invalid={error ? 'true' : undefined}
                required={required}
            />
        </FormField>
    );
}



