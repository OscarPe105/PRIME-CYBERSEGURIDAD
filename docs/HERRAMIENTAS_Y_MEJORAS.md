# PRIME: herramientas, trabajo realizado y mejoras

PRIME es un MVP académico de triaje de vulnerabilidades con política experimental `PRIME-1.0`. Los 25 casos de demostración son sintéticos. Defensa prevista: 1 de diciembre de 2026.

## 1. Arquitectura general

| Capa | Tecnología | Ubicación |
|---|---|---|
| Frontend | Angular 21, TypeScript 5.9, Vite 7, pnpm | `frontend/` |
| Backend | PHP 8.4+, Laravel 12 (API JSON + sesión con cookie) | `backend/` |
| Base de datos | SQLite (ejecutada); MySQL configurado pero no validado | `backend/database/` |
| Pruebas | PHPUnit 11 (backend), Vitest 3 + jsdom (frontend), Playwright (ensayo de navegador) | `backend/tests`, `frontend/tests` |
| Calidad de código | Laravel Pint (PHP), Prettier + `tsc --noEmit` (front) | `pint.json`, `package.json` |
| Despliegue local | Laravel sirve el build de Angular en el mismo origen (`prime.html` + `assets/`); `Iniciar-PRIME.ps1` | `backend/public/` |
| Documentación | Diagnóstico, plan de 35 evidencias, especificación, arquitectura | `docs/` |
| Evidencias | Reporte PHPUnit, capturas y registros de QA de navegador | `evidencias-iniciales/` |

## 2. Backend (Laravel)

**Controladores** (`backend/app/Http/Controllers`)
- `AuthController`: login, desafío MFA, sesión, logout.
- `ImportController`: carga de CSV.
- `FindingController`: listado, historial, enriquecimiento CTI, exportación CSV.
- `TreatmentController`: propuesta de tratamiento y revisión.
- `EvidenceController`: adjuntos privados y descarga.
- `AuditController`: bitácora de auditoría.

**Servicios** (`backend/app/Services`)
- `PrimeEngine`: cálculo del IRC con política versionada: `IRC = 0.60 CVSS + 0.40 DREAD_w`.
- `FindingService`: lógica de hallazgos e historial de revisiones.
- `CsvImporter` y `CsvSafety`: validación del CSV (UTF-8, 2 MB, 500 filas, duplicados, rangos) y protección contra inyección de fórmulas.
- `CtiClient`: consulta EPSS y KEV por CVE, con respuesta, hash y fechas conservados; catálogo KEV cacheado 24 h.
- `Totp` y `MfaService`: TOTP de 6 dígitos y 30 s, códigos de recuperación con hash, rechazo de reutilización.
- `AuditLogger`: registro de eventos.

**Seguridad implementada**
- Login con contraseña más MFA obligatorio. La sesión provisional dura 5 minutos y no da acceso a datos.
- Tres roles (analista, administrador, auditor), aplicados en backend con el middleware `RequireVerifiedRole`.
- Limitación de intentos (`throttle`) en login, MFA y CTI.
- Protección CSRF, y archivo original de importación y evidencias guardados de forma privada con hash.
- Revisión por una persona distinta de quien propone el tratamiento.

**Comandos**: `php artisan prime:user <correo> <rol>` crea una cuenta con contraseña aleatoria.

**Pruebas**: `tests/Unit/PrimeEngineTest.php` y `tests/Feature/PrimeFlowTest.php`.

## 3. Frontend (Angular)

**Componentes** (`frontend/src/app/components`)
- `login`: acceso, MFA y enrolamiento TOTP.
- `recovery`: códigos de recuperación.
- `findings`: tabla, búsqueda y filtros de hallazgos.
- `finding-detail`: detalle, historial, CTI, propuesta, evidencia y revisión.
- `activity`: auditoría.

**Otros**
- `services/prime-api.service.ts`: cliente HTTP de la API.
- `models/prime.ts`: tipos.
- `utils/`: formato y portapapeles.
- `styles/`: CSS por área (login, shell, findings, detail, responsive).
- `scripts/publish-build.mjs`: copia el build a `backend/public`.

**Pruebas**: `tests/app.test.ts` y `tests/prime-api.test.ts` (Vitest).

## 4. Qué se trabajó

- Autenticación completa con MFA, recuperación y roles.
- Importación segura de CSV y 25 casos sintéticos de demostración.
- Motor de puntaje (CVSS, DREAD, política `PRIME-1.0`) con historial versionado.
- Enriquecimiento CTI (EPSS, KEV) con trazabilidad.
- Flujo de tratamiento: propuesta, evidencia, revisión por otra persona.
- Auditoría y exportación CSV.
- Interfaz responsive y pruebas automatizadas con evidencias.

## 5. Qué se puede mejorar

**Por prioridad académica (antes de la defensa)**
1. Validar la política: vector/puntaje, rúbrica DREAD y pesos con datos reales. Hoy son una configuración académica.
2. Añadir un calculador CVSS v4.0 verificado. Hoy solo se valida estructura y rango, no se recalcula el puntaje.
3. Ejecutar y validar MySQL, que está configurado pero no probado.
4. Probar CTI real, ya que las pruebas usan CTI simulada.
5. Completar las 35 evidencias del plan (`docs/02_Plan_35_evidencias.md`).
6. Ampliar pruebas de MFA, CSV y RBAC. Añadir pruebas de seguridad y backups.

**Técnicas**
- Frontend: pasar de compilación JIT a AOT, partir el bundle (supera 500 kB) y usar enrutamiento con carga diferida.
- Backend: mover CTI e importaciones a colas (hoy es síncrono), y añadir un CI que corra pruebas, Pint y Prettier.
- HTTPS con `SESSION_SECURE_COOKIE=true`; hoy se usa HTTP en localhost.
- Rotación o política de bloqueo de cuentas tras fallos repetidos, no solo throttle por IP.
- Contraseña de administración: pantalla para gestionar usuarios y pesos, que hoy no existe.

**Funcionales**
- Multiempresa y propiedad por proyecto (hoy es un solo espacio de trabajo).
- Informe PDF y cálculo de riesgo residual.
- Conectores a escáneres, SIEM, IPS y LAPS, y laboratorio Windows.
- Recálculo o migración opcional de hallazgos antiguos a `PRIME-1.0`.

## 6. Cómo levantarlo

```bash
cd backend && php artisan serve --host=127.0.0.1 --port=8000
```

Interfaz en http://127.0.0.1:8000. Para modificar Angular: `cd frontend && pnpm build:backend`.
