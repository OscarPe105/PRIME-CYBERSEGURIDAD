import {
  Component,
  EventEmitter,
  Input,
  Output,
  type OnChanges,
  type SimpleChanges,
} from '@angular/core';
import { FormsModule } from '@angular/forms';
import { canWrite, type Finding, type FindingHistory, type User } from '../models/prime';
import { ctiStatus, date } from '../utils/format';

@Component({
  selector: 'prime-finding-detail',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './finding-detail.component.html',
})
export class FindingDetailComponent implements OnChanges {
  @Input({ required: true }) finding!: Finding;
  @Input({ required: true }) user!: User;
  @Input() history: FindingHistory | null = null;
  @Input() busy = false;
  @Output() closed = new EventEmitter<void>();
  @Output() ctiRequested = new EventEmitter<void>();
  @Output() treatmentProposed = new EventEmitter<string>();
  @Output() evidenceAttached = new EventEmitter<Event>();
  @Output() reviewSubmitted = new EventEmitter<string>();

  treatment = '';
  reviewNote = '';
  readonly ctiStatus = ctiStatus;
  readonly date = date;

  get canWrite(): boolean {
    return canWrite(this.user);
  }

  ngOnChanges(changes: SimpleChanges): void {
    const change = changes['finding'];
    if (change && change.previousValue?.id !== this.finding.id) {
      this.treatment = this.finding.treatment ?? '';
      this.reviewNote = '';
    }
  }
}
