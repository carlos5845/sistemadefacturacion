import FormField from '@/components/forms/FormField';
import { Input } from '@/components/ui/input';
import { type ComponentProps } from 'react';

interface FormInputProps extends ComponentProps<typeof Input> {
    label: string;
    error?: string;
    required?: boolean;
}

export default function FormInput({
    label,
    error,
    required,
    className,
    ...props
}: FormInputProps) {
    return (
        <FormField label={label} error={error} required={required} className={className}>
            <Input
                {...props}
                aria-invalid={error ? 'true' : undefined}
                required={required}
            />
        </FormField>
    );
}



