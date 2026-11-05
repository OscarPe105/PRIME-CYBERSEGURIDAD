import '@angular/compiler';
import { provideZonelessChangeDetection } from '@angular/core';
import { bootstrapApplication } from '@angular/platform-browser';
import { PrimeApp } from './app/app.component';
import './style.css';

bootstrapApplication(PrimeApp, { providers: [provideZonelessChangeDetection()] }).catch((error) => {
  document.body.textContent = 'No se pudo iniciar PRIME. ' + String(error);
});
