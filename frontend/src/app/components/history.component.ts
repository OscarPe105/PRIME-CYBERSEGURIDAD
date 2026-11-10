import { Component, EventEmitter, Input, Output } from '@angular/core';
import type { ArchiveDetail, Finding, QueueArchive } from '../models/prime';
import { PaginationComponent } from '../ui/pagination.component';
import { pageStart, paginate } from '../utils/paginate';
import { HlmButton } from '../ui/button';
import { date } from '../utils/format';

@Component({
  selector: 'prime-history',
  standalone: true,
  imports: [HlmButton, PaginationComponent],
  templateUrl: './history.component.html',
})
export class HistoryComponent {
  @Input() archives: QueueArchive[] = [];
  @Input() detail: ArchiveDetail | null = null;
  @Output() opened = new EventEmitter<number>();
  @Output() back = new EventEmitter<void>();
  listPage = 1;
  findingsPage = 1;
  readonly date = date;

  get listStart(): number {
    return pageStart(this.archives.length, this.listPage);
  }

  get findingsStart(): number {
    return pageStart(this.detail?.findings.length ?? 0, this.findingsPage);
  }

  get pagedArchives(): QueueArchive[] {
    return paginate(this.archives, this.listPage);
  }

  get pagedFindings(): Finding[] {
    return paginate(this.detail?.findings ?? [], this.findingsPage);
  }

  openArchive(id: number): void {
    this.findingsPage = 1;
    this.opened.emit(id);
  }
}
