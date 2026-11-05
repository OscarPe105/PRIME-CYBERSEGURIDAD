import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import type { Finding, FindingFilters } from '../models/prime';
import template from './findings.component.html?raw';

@Component({ selector: 'prime-findings', standalone: true, imports: [FormsModule], template })
export class FindingsComponent {
  @Input() findings: Finding[] = [];
  @Input() policy = '';
  @Input() busy = false;
  @Output() findingSelected = new EventEmitter<Finding>();

  @Input() filters: FindingFilters = { query: '', zone: '', status: '' };
  @Output() filtersChanged = new EventEmitter<FindingFilters>();

  updateFilters(changes: Partial<FindingFilters>): void {
    this.filtersChanged.emit({ ...this.filters, ...changes });
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
