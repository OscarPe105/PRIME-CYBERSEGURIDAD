import { Injectable } from '@angular/core';
import type {
  ArchiveDetail,
  AuditEvent,
  Credentials,
  CtiStatus,
  Finding,
  FindingHistory,
  MfaChallenge,
  QueueArchive,
  User,
} from '../models/prime';

export class ApiError extends Error {
  constructor(
    message: string,
    readonly status: number,
  ) {
    super(message);
  }
}

interface ResponseMetadata {
  csrf?: string;
  message?: string;
  errors?: Record<string, string[]>;
}

@Injectable({ providedIn: 'root' })
export class PrimeApiService {
  private csrf = '';

  session() {
    return this.request<{ user: User | null }>('/session');
  }

  login(credentials: Credentials) {
    return this.request<MfaChallenge>('/auth/login', 'POST', credentials);
  }

  verify(code: string) {
    return this.request<{ user: User; recovery_codes: string[] }>('/auth/mfa', 'POST', { code });
  }

  logout() {
    return this.request<void>('/auth/logout', 'POST');
  }

  findings() {
    return this.request<{ findings: Finding[]; policy: string }>('/findings');
  }

  importCsv(file: File) {
    return this.request<{ batch_id: number; count: number }>(
      '/imports',
      'POST',
      this.fileBody(file),
    );
  }

  history(id: number) {
    return this.request<FindingHistory>(`/findings/${id}/history`);
  }

  enrich(id: number) {
    return this.request<{ updated: boolean; epss_status: CtiStatus; kev_status: CtiStatus }>(
      `/findings/${id}/cti`,
      'POST',
    );
  }

  propose(id: number, treatment: string) {
    return this.request<{ status: 'propuesto' }>(`/findings/${id}/propose`, 'POST', { treatment });
  }

  attach(id: number, file: File) {
    return this.request<{ id: number }>(`/findings/${id}/evidence`, 'POST', this.fileBody(file));
  }

  review(id: number, note: string) {
    return this.request<{ status: 'mitigado'; message: string }>(`/findings/${id}/review`, 'POST', {
      note,
    });
  }

  clearQueue() {
    return this.request<{ archive_id: number; count: number }>('/queue/clear', 'POST');
  }

  archives() {
    return this.request<{ archives: QueueArchive[] }>('/queue/archives');
  }

  archive(id: number) {
    return this.request<ArchiveDetail>(`/queue/archives/${id}`);
  }

  audit() {
    return this.request<{ events: AuditEvent[] }>('/audit');
  }

  private fileBody(file: File): FormData {
    const body = new FormData();
    body.append('file', file);
    return body;
  }

  private async request<T>(path: string, method = 'GET', body?: unknown): Promise<T> {
    const form = body instanceof FormData;
    const headers: Record<string, string> = { Accept: 'application/json' };
    if (method !== 'GET') {
      headers['X-CSRF-TOKEN'] = this.csrf;
      if (!form) headers['Content-Type'] = 'application/json';
    }

    const response = await fetch('/api' + path, {
      method,
      credentials: 'same-origin',
      headers,
      body: method === 'GET' ? undefined : form ? body : JSON.stringify(body ?? {}),
    });
    const data: T & ResponseMetadata = await response.json().catch(() => ({
      message: 'Respuesta inesperada del servidor.',
    }));
    if (data.csrf) this.csrf = data.csrf;
    if (!response.ok) {
      const message = data.errors
        ? Object.values(data.errors).flat().join(' ')
        : data.message || 'La operación no pudo completarse.';
      throw new ApiError(message, response.status);
    }
    return data;
  }
}
