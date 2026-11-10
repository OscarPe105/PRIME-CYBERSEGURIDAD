import { Component, signal } from '@angular/core';
import { THEMES, applyTheme, storedTheme } from '../utils/theme';

@Component({
  selector: 'prime-settings',
  standalone: true,
  templateUrl: './settings.component.html',
})
export class SettingsComponent {
  readonly themes = THEMES;
  readonly active = signal(storedTheme());

  choose(id: string): void {
    applyTheme(id);
    this.active.set(id);
  }
}
