import type { CtiStatus } from '../models/prime';

const CTI_LABELS: Record<CtiStatus, string> = {
  ok: 'Disponible',
  pending: 'Pendiente',
  error: 'Error de consulta',
  missing: 'Sin dato',
  not_applicable: 'No aplica',
};

export function ctiStatus(status: CtiStatus): string {
  return CTI_LABELS[status] ?? status;
}

export function date(value: string): string {
  return new Date(value).toLocaleString('es-SV', { timeZone: 'America/El_Salvador' });
}
