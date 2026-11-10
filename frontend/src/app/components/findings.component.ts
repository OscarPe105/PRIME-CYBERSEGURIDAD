import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { PaginationComponent } from '../ui/pagination.component';
import { pageStart, paginate } from '../utils/paginate';
import { HlmButton } from '../ui/button';
import type { Finding, FindingFilters } from '../models/prime';

@Component({
  selector: 'prime-findings',
  standalone: true,
  imports: [FormsModule, HlmButton, PaginationComponent],
  templateUrl: './findings.component.html',
})
export class FindingsComponent {
  @Input() findings: Finding[] = [];
  @Input() policy = '';
  @Input() busy = false;
  @Input() canClear = false;
  @Output() clearRequested = new EventEmitter<void>();
  @Output() findingSelected = new EventEmitter<Finding>();

  @Input() filters: FindingFilters = { query: '', zone: '', status: '' };
  @Output() filtersChanged = new EventEmitter<FindingFilters>();

  page = 1;

  updateFilters(changes: Partial<FindingFilters>): void {
    this.page = 1;
    this.filtersChanged.emit({ ...this.filters, ...changes });
  }

  get hasFilters(): boolean {
    return !!(this.filters.query || this.filters.zone || this.filters.status);
  }

  clearFilters(): void {
    this.page = 1;
    this.filtersChanged.emit({ query: '', zone: '', status: '' });
  }

  get zones(): string[] {
    return [...new Set(this.findings.map((finding) => finding.zone))];
  }

  get filtered(): Finding[] {
    const query = this.filters.query.toLowerCase();
    return this.findings.filter(
      (finding) =>
        (!this.filters.zone || finding.zone === this.filters.zone) &&
        (!this.filters.status || finding.status === this.filters.status) &&
        (!query ||
          [finding.reference, finding.cve, finding.asset, finding.product]
            .join(' ')
            .toLowerCase()
            .includes(query)),
    );
  }

  get start(): number {
    return pageStart(this.filtered.length, this.page);
  }

  get paged(): Finding[] {
    return paginate(this.filtered, this.page);
  }

  get urgent(): number {
    return this.findings.filter((finding) => finding.calculation.kev_urgent).length;
  }

  get pending(): number {
    return this.findings.filter((finding) => finding.calculation.provisional).length;
  }

  get closed(): number {
    return this.findings.filter((finding) => finding.status === 'mitigado').length;
  }
}
