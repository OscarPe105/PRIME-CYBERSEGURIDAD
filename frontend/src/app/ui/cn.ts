import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/** Une clases de Tailwind resolviendo conflictos (patrón estilo shadcn/Spartan). */
export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}
