import { provideZonelessChangeDetection } from '@angular/core';
import { TestBed, type ComponentFixture } from '@angular/core/testing';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { PrimeApp } from '../src/app/app.component';

const user = { name: 'Analista', email: 'analista@prime.test', role: 'analista' };
const finding = {
  id: 1,
  reference: 'TEST-01',
  cve: null,
  asset: 'HOST-DEMO',
  zone: 'Laboratorio',
  product: 'Servicio sintético',
  product_version: '1.0',
  status: 'abierto',
  treatment: null,
  input: {
    cvss: 5,
    cvss_version: '4.0',
    justification: 'Caso de prueba',
    cvss_vector: 'CVSS:4.0',
    damage: 5,
    exploitability: 5,
    reproducibility: 5,
    affected_users: 5,
    discoverability: 5,
  },
  calculation: {
    version: 'PRIME-1.0',
    display_score: '5.00',
    dread: 5,
    base: 5,
    priority: 'Medio',
    severity: 'Medio',
    provisional: false,
    kev_urgent: false,
  },
  cti: {
    queried_at: '2026-10-03T12:00:00Z',
    epss: { status: 'not_applicable', value: null },
    kev: { status: 'not_applicable', value: null },
  },
};

const json = (data: unknown, status = 200) => new Response(JSON.stringify(data), { status });

function startApp() {
  const fixture = TestBed.createComponent(PrimeApp);
  fixture.detectChanges();
  return fixture;
}

async function settle(fixture: ComponentFixture<PrimeApp>): Promise<void> {
  await vi.waitFor(() => expect(fixture.componentInstance.busy).toBe(false));
  await fixture.whenStable();
}

beforeEach(() => {
  TestBed.configureTestingModule({
    imports: [PrimeApp],
    providers: [provideZonelessChangeDetection()],
  });
});
afterEach(() => vi.unstubAllGlobals());

describe('Component integration', () => {
  it('renders login and enrollment, submits credentials and enters the workspace after MFA', async () => {
    const fetch = vi.fn(async (path: string, options?: RequestInit) => {
      if (path === '/api/session') return json({ csrf: 'initial', user: null });
      if (path === '/api/auth/login')
        return json({ csrf: 'challenge', enrollment: true, secret: 'TEST-SECRET' });
      if (path === '/api/auth/mfa')
        return json({ csrf: 'verified', user, recovery_codes: ['recovery-one', 'recovery-two'] });
      if (path === '/api/findings') return json({ findings: [], policy: 'test-policy' });
      throw new Error(`Unexpected request: ${path}`);
    });
    vi.stubGlobal('fetch', fetch);
    const fixture = startApp();
    await settle(fixture);
    const root: HTMLElement = fixture.nativeElement;
    expect(root.textContent).toContain('Bienvenido a PRIME');

    function fill(name: string, value: string) {
      const field = root.querySelector<HTMLInputElement>(`input[name="${name}"]`)!;
      field.value = value;
      field.dispatchEvent(new Event('input', { bubbles: true }));
    }
    fill('email', user.email);
    fill('password', 'test-password');
    root
      .querySelector('form')!
      .dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await settle(fixture);
    expect(root.textContent).toContain('TEST-SECRET');
    expect(
      JSON.parse(fetch.mock.calls.find(([path]) => path === '/api/auth/login')![1]!.body as string),
    ).toEqual({ email: user.email, password: 'test-password' });
    fill('code', '123456');
    root
      .querySelector('form')!
      .dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    await settle(fixture);
    expect(root.textContent).toContain('Hallazgos y prioridades');
    expect(root.textContent).toContain('recovery-one');
    const dismiss = [...root.querySelectorAll('button')].find((button) =>
      button.textContent?.includes('Ya los guardé'),
    )!;
    dismiss.click();
    await settle(fixture);
    expect(root.querySelector('prime-recovery')).toBeNull();
  });

  it('filters findings, opens the detail and sends a treatment proposal through component outputs', async () => {
    const fetch = vi.fn(async (path: string, options?: RequestInit) => {
      if (path === '/api/session') return json({ csrf: 'session', user });
      if (path === '/api/findings') return json({ findings: [finding], policy: 'test-policy' });
      if (path === '/api/findings/1/history') return json({ revisions: [], evidence: [] });
      if (path === '/api/findings/1/propose') {
        expect(JSON.parse(options?.body as string)).toEqual({ treatment: 'Propuesta de prueba' });
        return json({ status: 'propuesto' });
      }
      throw new Error(`Unexpected request: ${path}`);
    });
    vi.stubGlobal('fetch', fetch);
    const fixture = startApp();
    await settle(fixture);
    const root: HTMLElement = fixture.nativeElement;
    const search = root.querySelector<HTMLInputElement>('[aria-label="Buscar hallazgo"]')!;
    search.value = 'no-match';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    await settle(fixture);
    expect(root.textContent).toContain('Sin coincidencias');
    search.value = 'host-demo';
    search.dispatchEvent(new Event('input', { bubbles: true }));
    await settle(fixture);
    root.querySelector<HTMLButtonElement>('.detail-button')!.click();
    await settle(fixture);
    expect(root.textContent).toContain('Contexto y fundamento');
    expect(root.textContent).toContain('PRIME-1.0');
    expect(root.textContent).toContain('EPSS no modifica el IRC.');
    const textarea = root.querySelector<HTMLTextAreaElement>('textarea')!;
    textarea.value = 'Propuesta de prueba';
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    await settle(fixture);
    [...root.querySelectorAll('button')]
      .find((button) => button.textContent?.includes('Registrar propuesta'))!
      .click();
    await settle(fixture);
    expect(fetch.mock.calls.some(([path]) => path === '/api/findings/1/propose')).toBe(true);
    expect(root.textContent).toContain('Propuesta registrada.');
  });

  it('clears private state and returns to login when a request reports an expired session', async () => {
    const fetch = vi.fn(async (path: string, options?: RequestInit) => {
      if (path === '/api/session') return json({ csrf: 'session', user });
      if (path === '/api/findings') return json({ findings: [finding], policy: 'test-policy' });
      return json({ message: 'Inicie sesión y valide MFA.' }, 401);
    });
    vi.stubGlobal('fetch', fetch);
    const fixture = startApp();
    await settle(fixture);
    fixture.nativeElement.querySelector('.detail-button').click();
    await settle(fixture);
    expect(fixture.nativeElement.textContent).toContain('Bienvenido a PRIME');
    expect(fixture.componentInstance.findings).toEqual([]);
    expect(fixture.componentInstance.selected).toBeNull();
  });

  it('renders administrator activity with the existing audit payload', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn(async (path: string, options?: RequestInit) => {
        if (path === '/api/session')
          return json({ csrf: 'session', user: { ...user, role: 'administrador' } });
        if (path === '/api/findings') return json({ findings: [], policy: 'test-policy' });
        if (path === '/api/audit')
          return json({
            events: [
              {
                id: 1,
                user_id: 1,
                event: 'csv_imported',
                details: '{"count":1}',
                created_at: '2026-10-03T12:00:00Z',
              },
            ],
          });
        throw new Error(`Unexpected request: ${path}`);
      }),
    );
    const fixture = startApp();
    await settle(fixture);
    fixture.nativeElement.querySelector('[aria-label="Actividad"]').click();
    await settle(fixture);
    expect(fixture.nativeElement.textContent).toContain('csv_imported');
    expect(fixture.nativeElement.textContent).toContain('Registro de actividad');
  });
});
