import { afterEach, describe, expect, it, vi } from 'vitest';
import { PrimeApiService } from '../src/app/services/prime-api.service';

const response = (data: unknown, status = 200) => new Response(JSON.stringify(data), { status });

afterEach(() => vi.unstubAllGlobals());

describe('API transport', () => {
  it('rotates CSRF tokens between session, login and MFA requests', async () => {
    const fetch = vi
      .fn()
      .mockResolvedValueOnce(response({ csrf: 'session-token', user: null }))
      .mockResolvedValueOnce(
        response({ csrf: 'challenge-token', requires_mfa: true, enrollment: false }),
      )
      .mockResolvedValueOnce(response({ csrf: 'verified-token', recovery_codes: [] }));
    vi.stubGlobal('fetch', fetch);
    const api = new PrimeApiService();
    await api.session();
    await api.login({ email: 'test@prime.test', password: 'test' });
    await api.verify('123456');
    expect(fetch.mock.calls[1][1]).toMatchObject({
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': 'session-token', 'Content-Type': 'application/json' },
    });
    expect(fetch.mock.calls[2][1].headers['X-CSRF-TOKEN']).toBe('challenge-token');
  });

  it('uploads files as multipart data without overriding the browser boundary', async () => {
    const fetch = vi.fn().mockResolvedValue(response({ batch_id: 1, count: 1 }));
    vi.stubGlobal('fetch', fetch);
    const file = new File(['id,cve'], 'input.csv');
    await new PrimeApiService().importCsv(file);
    const options = fetch.mock.calls[0][1];
    expect(options.body).toBeInstanceOf(FormData);
    expect(options.body.get('file')).toBe(file);
    expect(options.headers).not.toHaveProperty('Content-Type');
  });

  it('preserves HTTP status and combines validation errors', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(response({ errors: { code: ['Código incorrecto.'] } }, 422)),
    );
    await expect(new PrimeApiService().verify('bad')).rejects.toMatchObject({
      status: 422,
      message: 'Código incorrecto.',
    });
  });
});
