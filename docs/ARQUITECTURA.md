# Organización del código

PRIME sirve la API Laravel y la interfaz Angular compilada desde `backend/public` en el mismo origen. La política vigente `PRIME-1.0` calcula `IRC = 0.60 CVSS + 0.40 DREAD ponderado`; EPSS se conserva como información complementaria y KEV como prioridad operativa separada. Las revisiones históricas conservan su versión y resultado original. No requiere nuevas migraciones.

## Backend

- `routes/web.php`: define los endpoints y los roles que pueden usarlos.
- `app/Http/Middleware/RequireVerifiedRole.php`: comprueba sesión, MFA y rol antes de ejecutar una operación protegida.
- `app/Http/Controllers/`: controladores separados para autenticación, hallazgos y exportación, importación, tratamiento, evidencias y auditoría.
- `app/Services/MfaService.php`: valida TOTP y consume códigos de recuperación dentro de una transacción. `Totp.php` contiene el algoritmo y la decodificación Base32.
- `app/Services/FindingService.php`: comparte la lectura ordenada de hallazgos entre listado y exportación, y crea revisiones sin sobrescribir las anteriores.
- `app/Services/PrimeEngine.php`: valida las dimensiones y aplica pesos y umbrales de PRIME 1.0. El campo `epss_applied` se mantiene por compatibilidad con revisiones históricas; en evaluaciones nuevas siempre es falso.
- `app/Services/CsvImporter.php`: valida archivo, cabecera y filas. `CsvSafety.php` concentra la detección de prefijos de fórmula usada también al exportar.
- `app/Services/CtiClient.php`: consulta EPSS y KEV de forma independiente; conserva las respuestas y la caché KEV.
- `app/Services/AuditLogger.php`: registra los eventos con el usuario actual.

Los controladores coordinan validación HTTP, persistencia y respuestas. Las operaciones de propuesta y revisión conservan las transacciones y los bloqueos de fila. El listado omite las respuestas CTI crudas; el historial conserva los snapshots originales.

## Frontend

- `src/main.ts`: arranque de Angular y carga de estilos.
- `src/app/app.component.*`: sesión, navegación y coordinación de operaciones asíncronas.
- `src/app/components/`: acceso, códigos de recuperación, listado y filtros, detalle del hallazgo y actividad. Cada componente tiene su plantilla HTML y comunica acciones mediante eventos tipados.
- `src/app/services/prime-api.service.ts`: peticiones HTTP, rotación del token CSRF, envío de archivos y errores del servidor.
- `src/app/models/prime.ts`: contratos de usuario, MFA, hallazgo, cálculo, CTI, revisión, evidencia y auditoría.
- `src/app/utils/`: presentación de fechas/CTI y utilidades de copia y descarga.
- `src/styles/`: estilos agrupados por área; `src/style.css` conserva su orden y deja los ajustes adaptables al final.

Las plantillas se importan con `?raw` porque este prototipo sigue usando Angular JIT con Vite. La compilación AOT y la optimización del tamaño del bundle siguen pendientes. Los componentes de presentación no llaman directamente a `fetch`.

## Comprobaciones y formato

Desde `backend`:

```sh
composer test
composer format:check
composer format
```

Desde `frontend`:

```sh
pnpm install --frozen-lockfile
pnpm check
pnpm test
pnpm format:check
pnpm format
pnpm build:backend
```

`build:backend` comprueba TypeScript, compila la interfaz y copia los assets y el HTML a Laravel. Publica los assets antes del HTML y conserva los bundles anteriores para no romper pestañas abiertas.

Las pruebas PHP usan SQLite en memoria y almacenamiento simulado; no deben ejecutarse contra la base de trabajo. Cubren cálculo, TOTP, permisos, importación, CTI, evidencias, tratamiento, cierre de sesión y códigos de recuperación. Las pruebas frontend ejecutan las plantillas Angular en un DOM simulado, comprueban los eventos entre componentes y verifican CSRF, errores y archivos en el cliente HTTP. No sustituyen una revisión visual en navegador.

## Entorno local con Podman

Si PHP no está instalado en el host y el contenedor `prime-local` está activo:

```sh
podman exec prime-local php artisan test
podman exec prime-local vendor/bin/pint --test
podman stop prime-local
podman start prime-local
```

La aplicación queda disponible en `http://127.0.0.1:8000`. Las credenciales locales, `.env`, la base SQLite y las dependencias están excluidas de Git.
