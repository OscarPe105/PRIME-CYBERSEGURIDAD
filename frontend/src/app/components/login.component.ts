import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import type { Credentials, MfaChallenge } from '../models/prime';
import template from './login.component.html?raw';

@Component({ selector: 'prime-login', standalone: true, imports: [FormsModule], template })
export class LoginComponent {
  @Input() mfa: MfaChallenge | null = null;
  @Input() error = '';
  @Input() busy = false;
  @Output() loginSubmitted = new EventEmitter<Credentials>();
  @Output() codeSubmitted = new EventEmitter<string>();

  email = '';
  password = '';
  code = '';

  submitLogin(): void {
    this.loginSubmitted.emit({ email: this.email, password: this.password });
    this.password = '';
  }

  submitCode(): void {
    this.codeSubmitted.emit(this.code);
    this.code = '';
  }
}
