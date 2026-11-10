import { Component, Input } from '@angular/core';
import type { AuditEvent } from '../models/prime';
import { PaginationComponent } from '../ui/pagination.component';
import { pageStart, paginate } from '../utils/paginate';
import { date } from '../utils/format';

@Component({
  selector: 'prime-activity',
  standalone: true,
  imports: [PaginationComponent],
  templateUrl: './activity.component.html',
})
export class ActivityComponent {
  @Input() events: AuditEvent[] = [];
  page = 1;
  readonly date = date;

  get start(): number {
    return pageStart(this.events.length, this.page);
  }

  get paged(): AuditEvent[] {
    return paginate(this.events, this.page);
  }
}
