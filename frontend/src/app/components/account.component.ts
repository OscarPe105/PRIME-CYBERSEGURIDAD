import { Component, Input } from '@angular/core';
import type { Role, User } from '../models/prime';

interface Permission {
  label: string;
  roles: Role[];
}

@Component({
  selector: 'prime-account',
  standalone: true,
  templateUrl: './account.component.html',
})
export class AccountComponent {
  @Input({ required: true }) user!: User;

  readonly permissions: Permission[] = [
    {
      label: 'Ver hallazgos, detalle e historial',
      roles: ['analista', 'administrador', 'auditor'],
    },
    { label: 'Exportar CSV', roles: ['analista', 'administrador', 'auditor'] },
    { label: 'Importar CSV y consultar CTI', roles: ['analista', 'administrador'] },
    { label: 'Proponer tratamiento y adjuntar evidencia', roles: ['analista', 'administrador'] },
    { label: 'Revisar tratamientos de otra persona', roles: ['administrador'] },
    { label: 'Ver registro de actividad', roles: ['administrador', 'auditor'] },
  ];

  get initial(): string {
    return this.user.name.charAt(0).toUpperCase();
  }

  allowed(permission: Permission): boolean {
    return permission.roles.includes(this.user.role);
  }
}
