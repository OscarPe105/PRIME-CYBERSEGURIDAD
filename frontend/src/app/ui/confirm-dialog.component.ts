import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  BrnAlertDialog,
  BrnAlertDialogContent,
  BrnAlertDialogDescription,
  BrnAlertDialogOverlay,
  BrnAlertDialogTitle,
} from '@spartan-ng/brain/alert-dialog';
import { HlmButton } from './button';

/**
 * Diálogo de confirmación (patrón AlertDialog de shadcn/Spartan):
 * overlay oscuro, contenido centrado, título, descripción y pie con Cancelar/Confirmar.
 * Accesible: role="alertdialog", foco atrapado, Esc cancela.
 */
@Component({
  selector: 'prime-confirm-dialog',
  standalone: true,
  imports: [
    BrnAlertDialog,
    BrnAlertDialogContent,
    BrnAlertDialogDescription,
    BrnAlertDialogOverlay,
    BrnAlertDialogTitle,
    HlmButton,
  ],
  template: `
    <brn-alert-dialog [state]="open ? 'open' : 'closed'" (closed)="cancelled.emit()">
      <brn-alert-dialog-overlay class="bg-black/50" />
      <ng-template brnAlertDialogContent>
        <div
          class="grid w-[calc(100vw-2rem)] max-w-lg gap-4 rounded-lg border border-border bg-background p-6 text-foreground shadow-lg"
        >
          <div class="grid gap-2">
            <h2 brnAlertDialogTitle class="text-lg font-semibold leading-none">{{ title }}</h2>
            <div brnAlertDialogDescription class="text-sm text-muted-foreground">
              <ng-content />
            </div>
          </div>
          <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button
              hlmBtn
              variant="outline"
              type="button"
              [disabled]="busy"
              (click)="cancelled.emit()"
            >
              Cancelar
            </button>
            <button
              hlmBtn
              [variant]="destructive ? 'destructive' : 'default'"
              type="button"
              [disabled]="busy"
              (click)="confirmed.emit()"
            >
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </ng-template>
    </brn-alert-dialog>
  `,
})
export class ConfirmDialogComponent {
  @Input() open = false;
  @Input() title = '';
  @Input() confirmLabel = 'Confirmar';
  @Input() destructive = false;
  @Input() busy = false;
  @Output() confirmed = new EventEmitter<void>();
  @Output() cancelled = new EventEmitter<void>();
}
