import { Component, Input } from '@angular/core';
import type { AuditEvent } from '../models/prime';
import { date } from '../utils/format';
import template from './activity.component.html?raw';

@Component({ selector: 'prime-activity', standalone: true, template })
export class ActivityComponent {
  @Input() events: AuditEvent[] = [];
  readonly date = date;
}
