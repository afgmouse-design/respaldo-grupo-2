import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

/**
 * Estandariza un campo de nombre para entidades geográficas:
 * 1. Remueve caracteres numéricos (0-9).
 * 2. Transforma automáticamente a Title Case (primera letra de cada palabra en mayúscula).
 */
export function formatNombre(val: string): string {
    if (!val) return '';
    // Elimina números
    const clean = val.replace(/[0-9]/g, '');
    // Convierte a formato Mayúscula Inicial por Palabra
    return clean.toLowerCase().replace(/(?:^|\s|-)\S/g, (a) => a.toUpperCase());
}
