import { provideZonelessChangeDetection } from '@angular/core';
import { bootstrapApplication } from '@angular/platform-browser';
import { PrimeApp } from './app/app.component';
import { applyTheme, storedTheme } from './app/utils/theme';
import './style.css';

applyTheme(storedTheme(), false);

bootstrapApplication(PrimeApp, { providers: [provideZonelessChangeDetection()] }).catch((error) => {
  document.body.textContent = 'No se pudo iniciar PRIME. ' + String(error);
});
