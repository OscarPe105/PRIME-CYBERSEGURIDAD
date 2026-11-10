import { ChangeDetectorRef, Component, HostListener, inject, type OnInit } from '@angular/core';
import { AccountComponent } from './components/account.component';
import { SettingsComponent } from './components/settings.component';
import { HistoryComponent } from './components/history.component';
import { LogoComponent } from './ui/logo.component';
import { ConfirmDialogComponent } from './ui/confirm-dialog.component';
import { ActivityComponent } from './components/activity.component';
import { FindingDetailComponent } from './components/finding-detail.component';
import { FindingsComponent } from './components/findings.component';
import { LoginComponent } from './components/login.component';
import { RecoveryComponent } from './components/recovery.component';
import {
  canWrite,
  type ArchiveDetail,
  type AuditEvent,
  type Credentials,
  type Finding,
  type FindingHistory,
  type FindingFilters,
  type MfaChallenge,
  type QueueArchive,
  type User,
} from './models/prime';
import { ApiError, PrimeApiService } from './services/prime-api.service';
import { ctiStatus } from './utils/format';

@Component({
  selector: 'prime-app',
  standalone: true,
  imports: [
    AccountComponent,
    LogoComponent,
    HistoryComponent,
    ConfirmDialogComponent,
    SettingsComponent,
    LoginComponent,
    RecoveryComponent,
    FindingsComponent,
    FindingDetailComponent,
    ActivityComponent,
  ],
  templateUrl: './app.component.html',
})
export class PrimeApp implements OnInit {
  private readonly cdr = inject(ChangeDetectorRef);
  private readonly api = inject(PrimeApiService);

  user: User | null = null;
  mfa: MfaChallenge | null = null;
  recovery: string[] = [];
  error = '';
  notice = '';
  busy = false;
  findings: Finding[] = [];
  policy = '';
  filters: FindingFilters = { query: '', zone: '', status: '' };
  selected: Finding | null = null;
  history: FindingHistory | null = null;
  events: AuditEvent[] = [];
  menuOpen = false;
  clearOpen = false;
  archives: QueueArchive[] = [];
  archiveDetail: ArchiveDetail | null = null;
  view: 'hallazgos' | 'actividad' | 'cuenta' | 'configuracion' | 'historial' = 'hallazgos';

  async ngOnInit(): Promise<void> {
    await this.task(async () => {
      const session = await this.api.session();
      this.user = session.user;
      if (this.user) await this.load();
    });
  }

  openView(view: 'cuenta' | 'configuracion'): void {
    this.view = view;
    this.menuOpen = false;
  }

  @HostListener('document:click', ['$event'])
  closeMenuOutside(event: Event): void {
    if (this.menuOpen && !(event.target as HTMLElement).closest('.user-menu'))
      this.menuOpen = false;
  }

  @HostListener('document:keydown.escape')
  closeMenuEscape(): void {
    this.menuOpen = false;
  }

  get canWrite(): boolean {
    return canWrite(this.user);
  }

  async login(credentials: Credentials): Promise<void> {
    await this.task(async () => {
      this.mfa = await this.api.login(credentials);
    });
  }

  async verify(code: string): Promise<void> {
    await this.task(async () => {
      const response = await this.api.verify(code);
      this.user = response.user;
      this.recovery = response.recovery_codes;
      this.mfa = null;
      await this.load();
    });
  }

  async logout(): Promise<void> {
    await this.task(async () => {
      await this.api.logout();
      this.clearSession();
    });
  }

  async upload(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    await this.task(async () => {
      const response = await this.api.importCsv(file);
      this.notice = `Lote ${response.batch_id}: ${response.count} hallazgos importados. Los CVE están pendientes de consulta CTI.`;
      await this.load();
    });
    input.value = '';
  }

  async select(finding: Finding): Promise<void> {
    await this.task(async () => {
      this.selected = finding;
      this.history = null;
      this.history = await this.api.history(finding.id);
    });
  }

  async enrich(): Promise<void> {
    const id = this.selected?.id;
    if (id === undefined) return;
    await this.task(async () => {
      const response = await this.api.enrich(id);
      this.notice = `Consulta registrada. EPSS: ${ctiStatus(response.epss_status)}. KEV: ${ctiStatus(response.kev_status)}.`;
      await this.load();
      await this.loadHistory(id);
    });
  }

  async propose(treatment: string): Promise<void> {
    const id = this.selected?.id;
    if (id === undefined) return;
    await this.task(async () => {
      await this.api.propose(id, treatment);
      this.notice =
        'Propuesta registrada. Otra persona con rol Administrador debe revisar la evidencia.';
      await this.load();
    });
  }

  async attach(event: Event): Promise<void> {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    const id = this.selected?.id;
    if (!file || id === undefined) return;
    await this.task(async () => {
      await this.api.attach(id, file);
      await this.loadHistory(id);
      this.notice = 'Evidencia almacenada con huella SHA256.';
    });
    input.value = '';
  }

  async review(note: string): Promise<void> {
    const id = this.selected?.id;
    if (id === undefined) return;
    await this.task(async () => {
      const response = await this.api.review(id, note);
      this.notice = response.message;
      await this.load();
    });
  }

  async openHistory(): Promise<void> {
    await this.task(async () => {
      this.archiveDetail = null;
      this.archives = (await this.api.archives()).archives;
      this.view = 'historial';
    });
  }

  async openArchive(id: number): Promise<void> {
    await this.task(async () => {
      this.archiveDetail = await this.api.archive(id);
    });
  }

  async clearQueue(): Promise<void> {
    await this.task(async () => {
      const response = await this.api.clearQueue();
      this.clearOpen = false;
      this.selected = null;
      this.notice = `Cola limpiada: ${response.count} hallazgos guardados en el historial de cola de tratamiento.`;
      await this.load();
    });
    this.clearOpen = false;
  }

  async audit(): Promise<void> {
    await this.task(async () => {
      const response = await this.api.audit();
      this.events = response.events;
      this.view = 'actividad';
    });
  }

  private async load(): Promise<void> {
    const response = await this.api.findings();
    this.findings = response.findings;
    this.policy = response.policy;
    const selectedId = this.selected?.id;
    this.selected = this.findings.find((finding) => finding.id === selectedId) ?? null;
  }

  private async loadHistory(id: number): Promise<void> {
    const history = await this.api.history(id);
    if (this.selected?.id === id) this.history = history;
  }

  private clearSession(): void {
    this.user = null;
    this.mfa = null;
    this.findings = [];
    this.recovery = [];
    this.selected = null;
    this.history = null;
    this.events = [];
    this.view = 'hallazgos';
    this.menuOpen = false;
    this.clearOpen = false;
    this.archives = [];
    this.archiveDetail = null;
    this.filters = { query: '', zone: '', status: '' };
  }

  private async task(action: () => Promise<void>): Promise<void> {
    this.busy = true;
    this.error = '';
    this.notice = '';
    try {
      await action();
    } catch (error) {
      if (error instanceof ApiError && error.status === 401) this.clearSession();
      this.error = error instanceof Error ? error.message : String(error);
    } finally {
      this.busy = false;
      this.cdr.markForCheck();
    }
  }
}
