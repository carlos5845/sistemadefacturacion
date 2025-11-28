import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useState } from 'react';

interface AddCategoryModalProps {
    open: boolean;
    onClose: () => void;
    onCategoryCreated: (category: { id: number; name: string }) => void;
}

export function AddCategoryModal({
    open,
    onClose,
    onCategoryCreated,
}: AddCategoryModalProps) {
    const [name, setName] = useState('');
    const [description, setDescription] = useState('');
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<{
        name?: string;
        description?: string;
    }>({});

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        try {
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') ||
                document
                    .querySelector('input[name="_token"]')
                    ?.getAttribute('value') ||
                '';

            if (!csrfToken) {
                setErrors({
                    name: 'No se pudo obtener el token CSRF. Por favor, recarga la página.',
                });
                setProcessing(false);
                return;
            }

            const response = await fetch('/product-categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name, description }),
                credentials: 'same-origin',
            });

            let data;
            try {
                data = await response.json();
            } catch (jsonError) {
                // Si la respuesta no es JSON válido
                const text = await response.text();
                setErrors({
                    name: `Error del servidor: ${response.status} ${response.statusText}. ${text.substring(0, 100)}`,
                });
                setProcessing(false);
                return;
            }

            if (!response.ok) {
                if (data.errors) {
                    // Manejar errores de validación
                    const validationErrors: { name?: string; description?: string } = {};
                    if (data.errors.name) {
                        validationErrors.name = Array.isArray(data.errors.name) 
                            ? data.errors.name[0] 
                            : data.errors.name;
                    }
                    if (data.errors.description) {
                        validationErrors.description = Array.isArray(data.errors.description)
                            ? data.errors.description[0]
                            : data.errors.description;
                    }
                    setErrors(validationErrors);
                } else {
                    setErrors({
                        name: data.message || data.error || `Error al crear la categoría (${response.status})`,
                    });
                }
                setProcessing(false);
                return;
            }

            // Categoría creada exitosamente
            if (data.category) {
                onCategoryCreated(data.category);
                setName('');
                setDescription('');
                setErrors({});
                onClose();
            } else {
                setErrors({
                    name: 'La categoría se creó pero no se recibió la información completa.',
                });
            }
        } catch (error) {
            console.error('Error creating category:', error);
            setErrors({
                name: error instanceof Error 
                    ? `Error de conexión: ${error.message}` 
                    : 'Error al crear la categoría. Verifique su conexión.',
            });
        } finally {
            setProcessing(false);
        }
    };

    const handleClose = () => {
        setName('');
        setDescription('');
        setErrors({});
        onClose();
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Nueva Categoría</DialogTitle>
                    <DialogDescription>
                        Agrega una nueva categoría de producto
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit}>
                    <div className="grid gap-4 py-4">
                        <div className="grid gap-2">
                            <Label htmlFor="category-name">Nombre *</Label>
                            <Input
                                id="category-name"
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                placeholder="Ej: Electrónica"
                                required
                                aria-invalid={errors.name ? 'true' : undefined}
                            />
                            <InputError message={errors.name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="category-description">
                                Descripción
                            </Label>
                            <textarea
                                id="category-description"
                                value={description}
                                onChange={(e) => setDescription(e.target.value)}
                                rows={3}
                                className="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                placeholder="Descripción opcional"
                            />
                            <InputError message={errors.description} />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={handleClose}
                        >
                            Cancelar
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Guardando...' : 'Guardar'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
