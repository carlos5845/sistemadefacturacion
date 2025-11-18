import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { CheckCircle2 } from 'lucide-react';

interface SuccessModalProps {
    open: boolean;
    onClose: () => void;
    title?: string;
    message: string;
}

export function SuccessModal({
    open,
    onClose,
    title = 'Éxito',
    message,
}: SuccessModalProps) {
    return (
        <Dialog open={open} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                            <CheckCircle2 className="h-6 w-6 text-green-600 dark:text-green-400" />
                        </div>
                        <DialogTitle>{title}</DialogTitle>
                    </div>
                    <DialogDescription className="pt-2">{message}</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button onClick={onClose} variant="default">
                        Aceptar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

