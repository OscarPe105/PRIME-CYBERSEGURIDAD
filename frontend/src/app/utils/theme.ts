export interface Theme {
  id: string;
  label: string;
  /** Colores de la muestra: [fondo oscuro, color de marca, fondo claro]. */
  swatch: [string, string, string];
}

export const THEMES: Theme[] = [
  { id: 'bosque', label: 'Bosque (original)', swatch: ['#173e32', '#176c55', '#eef3f0'] },
  { id: 'oceano', label: 'Océano', swatch: ['#11304f', '#1d5fa8', '#eef2f7'] },
  { id: 'violeta', label: 'Violeta', swatch: ['#2b1b4a', '#6d3fb5', '#f1eef7'] },
  { id: 'grafito', label: 'Grafito', swatch: ['#1f2937', '#374151', '#f1f3f5'] },
  { id: 'ambar', label: 'Ámbar', swatch: ['#3b2410', '#b45309', '#f7f3ee'] },
];

export const DEFAULT_THEME = 'bosque';
const KEY = 'prime-theme';

export function storedTheme(): string {
  try {
    const id = localStorage.getItem(KEY);
    if (id && THEMES.some((theme) => theme.id === id)) return id;
  } catch {
    // Sin almacenamiento disponible: se usa la paleta por defecto.
  }
  return DEFAULT_THEME;
}

export function applyTheme(id: string, persist = true): void {
  document.documentElement.dataset['theme'] = id;
  if (!persist) return;
  try {
    localStorage.setItem(KEY, id);
  } catch {
    // La preferencia solo se conserva mientras la pestaña esté abierta.
  }
}
