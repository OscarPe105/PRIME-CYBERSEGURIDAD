/** Máximo de filas por página en cualquier tabla de PRIME (regla de UI-GUIDELINES.md). */
export const PAGE_SIZE = 10;

export function pageCount(total: number): number {
  return Math.max(1, Math.ceil(total / PAGE_SIZE));
}

/** Devuelve la página pedida (1-based) ajustada al rango válido. */
export function paginate<T>(items: readonly T[], page: number): T[] {
  const current = Math.min(Math.max(1, page), pageCount(items.length));
  return items.slice((current - 1) * PAGE_SIZE, current * PAGE_SIZE);
}

/** Número de fila (1-based) del primer registro de la página; la numeración continúa entre páginas. */
export function pageStart(total: number, page: number): number {
  const current = Math.min(Math.max(1, page), pageCount(total));
  return (current - 1) * PAGE_SIZE + 1;
}
