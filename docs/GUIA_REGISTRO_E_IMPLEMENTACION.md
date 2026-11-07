# Guía: registrar vulnerabilidades en PRIME e implementar lo que pide la tesis

Fuente de contexto: *Diseño y validación de una plataforma web de triaje contextual de vulnerabilidades basada en CVSS, DREAD y Zero Trust para Consultores Apex S.A.* (Grupo L, UTEC, noviembre 2026, 80 páginas).

## 1. Qué dice el documento

- PRIME es un MVP académico de triaje. Usa `IRC = 0.60·CVSS + 0.40·DREAD_w`, con bandas Bajo < 4, Medio 4–<7, Alto 7–<9, Crítico ≥ 9. La clasificación usa el valor sin redondear.
- EPSS y KEV son señales complementarias. No entran en la fórmula y se guardan con fecha y procedencia.
- El dataset son 25 hallazgos sintéticos, H01–H25. Resultado esperado (Tabla 10): **10 críticos, 11 altos, 4 medios, 0 bajos**.
- Ocho casos no tienen CVE: H13, H14, H17 y H21–H25. Los otros 17 sí.
- Casos de control citados: H01 (CVSS 9.3, DREAD 9.6, IRC 9.42), H13 (6.5 / 8.4 / 7.26), H18 (9.8 / 7.4 / 8.84), H24 (9.8 / 3.8 / 7.40).
- Roles: Analista, Administrador, Auditor. Tratamiento con revisión por otra persona.
- Arquitectura propuesta: Zero Trust, segmentación, Jump Host, controles compensatorios para sistemas legacy, LAPS, SIEM/IPS.

### Estado según la tesis (Tabla 11 y Anexo E, E01–E35)

| Estado | Áreas |
|---|---|
| Demostrado | Dataset y modelo definidos; flujo MFA positivo; dashboard, filtros, detalle, historial y actividad |
| Parcial | Pruebas funcionales, unitarias, de integración y E2E; carga CSV válida; trazabilidad CTI; despliegue reproducible |
| No ejecutado | RBAC completo, CSV/SQLi/XSS adversarial, SAST/DAST/SBOM, rendimiento, fallas CTI, microsegmentación, legacy, LAPS, backup, restauración |

## 2. Cómo registrar las vulnerabilidades aquí

### Vía A: importar un CSV (ya implementado)

Entra como Analista o Administrador y usa la importación. Reglas del importador: UTF-8, máximo 2 MB y 500 filas, IDs únicos (un archivo con IDs ya importados se rechaza), sin prefijos de fórmula (`=`, `+`, `-`, `@`).

Columnas (ver `frontend/public/ejemplo25.csv`):

```
id,cve,asset,zone,product,version,cvss_version,cvss_vector,cvss,damage,exploitability,reproducibility,affected_users,discoverability,justification
```

Pasos:
1. Copia `ejemplo25.csv` a `docs/dataset_H01-H25.csv`.
2. Sustituye los `DEMO-01…25` por `H01…H25`, con sus activos, zonas (Core, DMZ, Endpoints, Legacy, Desarrollo), CVSS y las cinco dimensiones DREAD de la tesis.
3. Deja `cve` vacío en H13, H14, H17 y H21–H25. EPSS y KEV quedan como "no aplica".
4. Importa. Comprueba que el tablero muestre 10 críticos, 11 altos y 4 medios.
5. Verifica H01 = 9.42, H13 = 7.26, H18 = 8.84, H24 = 7.40.

El PDF solo da CVSS y DREAD de esos cuatro casos. Los valores de los otros 21 hay que tomarlos de la tabla de datos del equipo, no se inventan. El `ejemplo25.csv` actual es un dato de ensayo: todos tienen CVSS 9.3, y DEMO-01 produce 6.58, que no coincide con H01.

### Vía B: formulario de alta manual (no existe, hay que construirlo)

Hoy la interfaz no permite registrar un hallazgo suelto. Para eso:

**Backend**
1. Añadir `POST /api/findings` en `routes/web.php`, restringido a `analista,administrador`.
2. Crear `StoreFindingRequest` con las mismas validaciones que `CsvImporter` (rangos, ID único, `CsvSafety`).
3. Reutilizar `FindingService`/`PrimeEngine` para calcular y guardar el IRC y la primera revisión.
4. Registrar el evento en `AuditLogger`.
5. Cubrirlo con un test en `tests/Feature/PrimeFlowTest.php`.

**Frontend**
1. Añadir `createFinding()` en `prime-api.service.ts` y el tipo en `models/prime.ts`.
2. Crear `finding-form.component.ts/html` (Angular Forms) con CVE, activo, zona, CVSS y las cinco DREAD.
3. Mostrar el IRC calculado tras guardar y enlazar al detalle.
4. Ocultar el botón para el rol Auditor, aunque el backend ya lo bloquee.
5. Ejecutar `pnpm build:backend` para publicar el build.

## 3. Paso a paso para implementar lo que pide la tesis

Orden recomendado: de menor a mayor esfuerzo, y cerrando primero lo que más puntúa en la defensa (1 de diciembre; ensayo final del 23 al 29 de noviembre).

1. **Dataset y modelo (E01, E02, E11).** Carga el dataset de la sección 2. Añade a `tests/Unit/PrimeEngineTest.php` los casos H01, H13, H18 y H24, más los límites 3.99/4.0, 6.99/7.0 y 8.99/9.0. Ejecuta `cd backend && php artisan test`.
2. **RBAC (E06).** Escribe una prueba de feature que recorra cada ruta con los tres roles y compare con la matriz esperada (Analista importa y propone; Administrador revisa; Auditor solo lee y ve auditoría). Guarda el resultado en `evidencias-iniciales/`.
3. **MFA negativa (E05).** Pruebas de código incorrecto, TOTP reutilizado, sesión provisional vencida (5 min), código de recuperación usado dos veces y límite de intentos.
4. **CSV e inyección (E07, E08, E09).** Crea archivos: válido, de 501 filas, de más de 2 MB, con IDs repetidos, con `=cmd|...` y con `<script>`. Verifica rechazo y que la exportación CSV neutralice fórmulas (CWE-1236).
5. **CTI real (E16–E19).** Configura `CtiClient` con EPSS (FIRST) y KEV (CISA), consulta dos veces el mismo CVE en momentos distintos y conserva ambas respuestas. Simula timeout y error para E18 (`Http::fake` en tests).
6. **Rendimiento (E14, E15).** Importa 25, 100 y 500 filas y mide el tiempo de la API (`php artisan tinker` o `curl -w`). Registra los tiempos en una tabla.
7. **Seguridad automatizada (E10, E32).**
   - `composer audit` y `pnpm audit` para dependencias.
   - SAST: PHPStan/Larastan para PHP y `tsc` + ESLint para el front.
   - DAST: OWASP ZAP contra `http://127.0.0.1:8000`.
   - SBOM: CycloneDX (`composer` y `pnpm`).
8. **MySQL (E30, E31).** Instala MySQL, usa `.env.mysql.example`, crea la base `prime` con un usuario limitado y corre `php artisan migrate`. Repite las pruebas con esa base.
9. **Backup y restauración (E28, E29).** Con SQLite: copia `database.sqlite` y `storage/app` con hash, restaura en otra carpeta y comprueba que los 25 casos y el historial coincidan. Con MySQL: `mysqldump` y restauración.
10. **Contrato de API (Anexo D).** La tesis nombra `/api/v1/triages…`; el código usa `/api/findings…`. Opciones: añadir alias `/api/v1/*` o actualizar el anexo para que coincida con el código. Elegir una y documentarla.
11. **Funciones pendientes del diseño.** Pantalla de usuarios y pesos (`PUT /config` solo Administrador, versionado), informe PDF y riesgo residual.
12. **Red y legacy (E20–E27).** Esto no se hace en la aplicación: monta un laboratorio con máquinas virtuales (Windows legacy, Jump Host, firewall), ejecuta Nmap antes y después de las reglas, guarda logs y prueba la reversión. Si no hay tiempo, mantenerlo como "diseñado, no validado", igual que hoy.
13. **Congelar versión.** Etiqueta en git la versión probada, regenera las evidencias y actualiza el Anexo E con estados reales.

## 4. Mejoras técnicas pendientes

Compilación AOT y partición del bundle de Angular, HTTPS con cookie `Secure`, colas para CTI e importaciones, CI que ejecute pruebas, Pint y Prettier, y bloqueo de cuenta tras fallos repetidos.
