import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'react';

interface FormFieldProps extends HTMLAttributes<HTMLDivElement> {
    label: string;
    error?: string;
    required?: boolean;
    children: React.ReactNode;
}

export default function FormField({
    label,
    error,
    required,
    children,
    className,
    ...props
}: FormFieldProps) {
    return (
        <div className={cn('grid gap-2', className)} {...props}>
            <Label htmlFor={props.id}>
                {label}
                {required && <span className="text-red-500"> *</span>}
            </Label>
            {children}
            <InputError message={error} />
        </div>
    );
}



