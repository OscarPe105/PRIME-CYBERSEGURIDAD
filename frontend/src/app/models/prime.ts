export type Role = 'analista' | 'administrador' | 'auditor';
export type FindingStatus = 'abierto' | 'propuesto' | 'mitigado';
export type CtiStatus = 'ok' | 'pending' | 'error' | 'missing' | 'not_applicable';

export interface User {
  name: string;
  email: string;
  role: Role;
}

export interface Credentials {
  email: string;
  password: string;
}

export interface MfaChallenge {
  requires_mfa: boolean;
  enrollment: boolean;
  secret?: string;
  otpauth?: string;
}

export interface FindingInput {
  id: string;
  cve: string | null;
  asset: string;
  zone: string;
  product: string;
  version: string;
  cvss_version: string;
  cvss_vector: string;
  cvss: number;
  damage: number;
  exploitability: number;
  reproducibility: number;
  affected_users: number;
  discoverability: number;
  justification: string;
}

export interface Calculation {
  version: string;
  weights: Record<string, number>;
  dread: number;
  base: number;
  score: number;
  display_score: string;
  severity: string;
  priority: string;
  provisional: boolean;
  epss_applied: boolean;
  kev_urgent: boolean;
}

interface CtiSource<T> {
  status: CtiStatus;
  value: T | null;
  data_date?: string;
  source?: string;
  sha256?: string;
  raw?: string;
  error_type?: string;
}

export interface CtiSnapshot {
  queried_at: string;
  epss: CtiSource<number> & { percentile?: number | null };
  kev: CtiSource<boolean> & {
    catalog_version?: string;
    retrieved_at?: string;
    cached?: boolean;
    entry?: Record<string, unknown> | null;
  };
}

export interface Finding {
  id: number;
  reference: string;
  cve: string | null;
  asset: string;
  zone: string;
  product: string;
  product_version: string;
  status: FindingStatus;
  treatment: string | null;
  input: FindingInput;
  calculation: Calculation;
  cti: CtiSnapshot;
  revision_id: number;
}

export interface Revision {
  id: number;
  finding_id: number;
  policy_version: string;
  snapshot: CtiSnapshot;
  calculation: Calculation;
  created_at: string;
}

export interface Evidence {
  id: number;
  name: string;
  sha256: string;
  size: number;
  created_at: string;
}

export interface FindingHistory {
  revisions: Revision[];
  evidence: Evidence[];
}

export interface AuditEvent {
  id: number;
  user_id: number | null;
  event: string;
  details: string;
  created_at: string;
}

export function canWrite(user: User | null): boolean {
  return user?.role === 'analista' || user?.role === 'administrador';
}

export interface FindingFilters {
  query: string;
  zone: string;
  status: string;
}

export interface QueueArchive {
  id: number;
  count: number;
  created_at: string;
  user_name: string;
  user_email: string;
}

export interface ImportBatchInfo {
  id: number;
  count: number;
  created_at: string;
  user_name: string;
  user_email: string;
}

export interface ArchiveDetail {
  archive: QueueArchive;
  batches: ImportBatchInfo[];
  findings: Finding[];
}
