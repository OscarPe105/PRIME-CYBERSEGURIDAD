import { Component } from '@angular/core';

/**
 * Logo de PRIME con los colores de la paleta activa (variables --logo-*, definidas en themes.css).
 * El tamaño lo da la clase `brand-mark` del contenedor.
 */
@Component({
  selector: 'prime-logo',
  standalone: true,
  host: { class: 'brand-mark', role: 'img', 'aria-label': 'PRIME' },
  template: `
    <svg viewBox="0 0 64 64" width="100%" height="100%" fill="none" aria-hidden="true">
      <rect x="1" y="1" width="62" height="62" rx="17" fill="var(--logo-tile)" />
      <path
        d="M32 8 53 16v15c0 12-8 20-21 26C19 51 11 43 11 31V16L32 8Z"
        fill="var(--logo-shield)"
      />
      <path
        d="M24 45V20h12a9 9 0 0 1 0 18h-5v7h-7Zm7-18v5h5a2.5 2.5 0 0 0 0-5h-5Z"
        fill="var(--logo-letter)"
        fill-rule="evenodd"
        clip-rule="evenodd"
      />
      <path
        d="m45 11 8 3v8"
        stroke="var(--logo-accent)"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  `,
})
export class LogoComponent {}
