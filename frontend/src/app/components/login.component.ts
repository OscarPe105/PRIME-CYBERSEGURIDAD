import { Component, EventEmitter, Input, Output } from '@angular/core';
import { LogoComponent } from '../ui/logo.component';
import { FormsModule } from '@angular/forms';
import type { Credentials, MfaChallenge } from '../models/prime';

@Component({
  selector: 'prime-login',
  standalone: true,
  imports: [FormsModule, LogoComponent],
  templateUrl: './login.component.html',
})
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
