import { Component, EventEmitter, Input, Output } from '@angular/core';
import { PAGE_SIZE, pageCount } from '../utils/paginate';
import { HlmButton } from './button';

/** Paginador de tablas: "Mostrando X–Y de N", Anterior/Siguiente y números de página. */
@Component({
  selector: 'prime-pagination',
  standalone: true,
  imports: [HlmButton],
  template: `
    @if (total > 0) {
      <nav
        class="flex flex-col items-center justify-between gap-3 border-t border-border bg-background px-5 py-3 sm:flex-row"
        aria-label="Paginación"
      >
        <p class="text-xs text-muted-foreground">Mostrando {{ from }}–{{ to }} de {{ total }}</p>
        @if (pages > 1) {
          <div class="flex items-center gap-1">
            <button
              hlmBtn
              variant="outline"
              size="sm"
              type="button"
              [disabled]="current === 1"
              (click)="go(current - 1)"
            >
              Anterior
            </button>
            @for (n of numbers; track n) {
              <button
                hlmBtn
                [variant]="n === current ? 'default' : 'ghost'"
                size="sm"
                type="button"
                [attr.aria-current]="n === current ? 'page' : null"
                (click)="go(n)"
              >
                {{ n }}
              </button>
            }
            <button
              hlmBtn
              variant="outline"
              size="sm"
              type="button"
              [disabled]="current === pages"
              (click)="go(current + 1)"
            >
              Siguiente
            </button>
          </div>
        }
      </nav>
    }
  `,
})
export class PaginationComponent {
  @Input() total = 0;
  @Input() page = 1;
  @Output() pageChange = new EventEmitter<number>();

  get pages(): number {
    return pageCount(this.total);
  }

  get current(): number {
    return Math.min(Math.max(1, this.page), this.pages);
  }

  get from(): number {
    return (this.current - 1) * PAGE_SIZE + 1;
  }

  get to(): number {
    return Math.min(this.current * PAGE_SIZE, this.total);
  }

  get numbers(): number[] {
    return Array.from({ length: this.pages }, (_, i) => i + 1);
  }

  go(page: number): void {
    this.pageChange.emit(Math.min(Math.max(1, page), this.pages));
  }
}
