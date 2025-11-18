import FormField from '@/components/forms/FormField';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Option {
    value: string;
    label: string;
}

interface FormSelectProps {
    label: string;
    name: string;
    value: string;
    onValueChange: (value: string) => void;
    options: Option[];
    placeholder?: string;
    error?: string;
    required?: boolean;
    className?: string;
}

export default function FormSelect({
    label,
    name,
    value,
    onValueChange,
    options,
    placeholder = 'Seleccione...',
    error,
    required,
    className,
}: FormSelectProps) {
    return (
        <FormField label={label} error={error} required={required} className={className}>
            <Select name={name} value={value} onValueChange={onValueChange} required={required}>
                <SelectTrigger aria-invalid={error ? 'true' : undefined}>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FormField>
    );
}



