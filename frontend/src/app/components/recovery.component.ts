import {
  ChangeDetectorRef,
  Component,
  EventEmitter,
  Input,
  Output,
  inject,
  type OnDestroy,
} from '@angular/core';
import { copyText, downloadText } from '../utils/clipboard';
import template from './recovery.component.html?raw';

@Component({ selector: 'prime-recovery', standalone: true, template })
export class RecoveryComponent implements OnDestroy {
  @Input() recovery: string[] = [];
  @Output() dismissed = new EventEmitter<void>();
  copied = false;
  notice = '';
  private readonly cdr = inject(ChangeDetectorRef);
  private copyTimer?: ReturnType<typeof setTimeout>;
  private destroyed = false;

  async copyRecovery(): Promise<void> {
    try {
      await copyText(this.recovery.join('\n'));
      if (this.destroyed) return;
      this.copied = true;
      this.notice = '';
      clearTimeout(this.copyTimer);
      this.copyTimer = setTimeout(() => {
        this.copied = false;
        this.cdr.markForCheck();
      }, 2000);
    } catch {
      this.notice = 'No se pudo copiar. Descarga el archivo o copia los códigos a mano.';
    } finally {
      if (!this.destroyed) this.cdr.markForCheck();
    }
  }

  downloadRecovery(): void {
    const text = this.recovery.map((code, index) => `${index + 1}. ${code}`).join('\n') + '\n';
    downloadText(text, 'prime-codigos-recuperacion.txt');
  }

  ngOnDestroy(): void {
    this.destroyed = true;
    clearTimeout(this.copyTimer);
  }
}
