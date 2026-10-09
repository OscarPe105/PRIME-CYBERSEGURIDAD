# Especificación y criterios de validación del MVP PRIME

Defensa prevista para el 1 de diciembre de 2026. Esta especificación delimita la implementación objetivo y distingue el prototipo inicial de las capacidades pendientes. El estado real de la versión inicial se registra en `prime-mvp/README.md` y sus reportes de prueba. Ningún estado planificado equivale a una prueba aprobada.

## Requisitos del producto

| ID | Requisito | Evidencias |
|---|---|---|
| RF01 | Acceso con contraseña, enrolamiento y verificación TOTP; recuperación y revocación | E05 |
| RF02 | Permisos efectivos de Analista, Administrador y Auditor | E06 |
| RF03 | Cálculo con CVSS y DREAD; pesos, reglas y versiones explícitos | E02, E11 |
| RF04 | Importación CSV validada con lote, conteo, errores y archivo original | E04, E07, E08 |
| RF05 | Datos EPSS y KEV fechados, estados de fallo y no aplicabilidad | E16–E18 |
| RF06 | Historial inmutable de decisiones y CTI; nuevos triajes sin sobrescribir | E19 |
| RF07 | Tabla, detalle, búsqueda, filtros y orden estable | E04, E13 |
| RF08 | Propuesta de tratamiento, evidencia y revisión por otra persona | E13, E24 |
| RF09 | Exportación de resultados con datos de política y CTI | E04, E08 |
| RF10 | Auditoría y descarga autorizada de evidencias | E06, E19, E35 |
| RNF01 | Persistencia MySQL y despliegue limpio reproducible | E12, E30, E31 |
| RNF02 | Pruebas de seguridad y dependencias, SBOM | E09, E10, E32 |
| RNF03 | Medición del lote de 25/100/500 con condiciones registradas | E14, E15 |
| RNF04 | Respaldo/restauración de BD, adjuntos y claves bajo custodia | E28, E29 |
| INF01 | Segmentación real, controles permitidos/denegados y logs | E20–E23 |
| INF02 | Compatibilidad, reversión y modalidad LAPS verificadas | E24–E27 |
| DOC01 | Dataset y evaluación independiente, trazabilidad y evidencia original | E01, E03, E34, E35 |

## Arquitectura objetivo

Angular y Laravel pueden publicarse bajo un mismo origen mediante un proxy HTTPS. Las sesiones se mantendrán en backend; el navegador solo conservará la cookie de sesión y el mecanismo CSRF. Laravel tendrá acceso restringido a MySQL y almacenamiento privado de adjuntos. Un worker procesará lotes si la ingesta se vuelve asíncrona. Un servicio CTI recuperará EPSS por CVE y un catálogo KEV compartido, con snapshots versionados y conservación de fuentes.

El prototipo inicial procesa lotes de forma síncrona y utiliza sesiones de Laravel, sin afirmar que incorpora Sanctum. No hay JWT. El diseño objetivo puede adoptar Sanctum para una SPA; esa decisión no requiere migrar innecesariamente un mecanismo ya correcto. El prototipo sirve la interfaz compilada desde el mismo origen y se ejecuta en localhost, con SQLite inicial. Eso demuestra un flujo de software, no el despliegue definitivo MySQL/HTTPS.

El laboratorio de Windows/AD/firewall es una infraestructura separada del MVP. PRIME registra propuestas y evidencias, pero no aplica reglas ni prueba automáticamente la eficacia de un control. Una topología académica no se considerará implementada hasta presentar configuración y pruebas.

## Persistencia objetivo

| Entidad | Campos esenciales y relación |
|---|---|
| users y roles | Cuenta, hash de contraseña, rol, MFA confirmado, secreto cifrado, contador anti-reuso, recuperación con hashes |
| assets | ID, hostname, dirección, producto/versión, zona, dueño y criticidad declarada |
| findings | Hallazgo por activo, CVE nullable, tipo, descripción, contexto, procedencia, fecha, aplicabilidad |
| import_batches | Archivo original privado, hash, autor, fecha, filas, estado, errores |
| contextual_assessments | D/E/R/A/Ds, rúbrica, valores, justificación, autor y fecha |
| cti_snapshots | Fuente, respuesta original, hash, fecha del dato, consulta, versión, estados, valores |
| scoring_policies | Fórmula, pesos, umbrales, versión, aprobador y vigencia |
| triage_revisions | Hallazgo, política, entradas completas, snapshots, cálculos intermedios, prioridad, fecha y motivo |
| treatments | Propuesta, control, responsable, estado, verificación y riesgo aceptado/pendiente |
| evidence_files | Archivo privado, hash, tamaño, autor, prueba y relación al hallazgo |
| audit_events | Evento, actor, fecha, objeto, antes/después relevante y versión |

La versión inicial simplifica activos, contexto y política en el registro del hallazgo y su cálculo JSON; conserva lotes, revisiones, evidencia y auditoría. La normalización completa, estados adicionales de tratamiento y políticas administrables son pendientes identificados, no tablas que ya existan por haberlas escrito en este documento.

La información global de un CVE no debe confundirse con el hallazgo contextual de un activo. Dos activos pueden compartir CVE y tener evaluaciones diferentes. Las tablas o JSON de trazabilidad deben conservar inputs originales; actualizar el catálogo no debe cambiar el pasado. El riesgo residual se modelará aparte de un cambio administrativo de estado.

## Permisos objetivo

| Operación | Analista | Administrador | Auditor |
|---|---|---|---|
| Lectura de hallazgos y datos CTI | Sí | Sí | Sí |
| CSV y consulta CTI | Sí | Sí | No |
| Proponer tratamiento y adjuntar evidencia | Sí | Sí | No |
| Aprobar cierre de propuesta ajena | No | Sí | No |
| Exportar y leer histórico | Sí | Sí | Sí |
| Leer auditoría | No | Sí | Sí |
| Gestionar cuentas y parámetros | No | Sí con trazabilidad | No |

La autorización se ejecutará en backend en todas las operaciones. El prototipo es un único espacio de trabajo, sin organizaciones múltiples: no se atribuirá aislamiento multiempresa. Si posteriormente se añade propiedad por proyecto, cada consulta y descarga deberá verificar el proyecto autorizado y probar acceso cruzado por ID.

La creación de cuentas inicial utiliza un comando local autorizado. No hay pantalla de administración de usuarios ni de edición de pesos en la primera versión. No inventar su aprobación en E06.

## Contrato inicial de API

La interfaz envía `Accept: application/json`, conserva sesión y adjunta `X-CSRF-TOKEN` en escrituras. El primer endpoint devuelve el token CSRF; no sustituye la autenticación. Se usan rutas bajo middleware web para CSRF/sesiones. Los errores 401, 403, 409, 419, 422, 429 y 5xx deben mostrarse sin convertirse en éxito silencioso.

| Método y ruta | Entrada | Resultado y acceso |
|---|---|---|
| GET /api/session | Sin cuerpo | CSRF y usuario completo solo si MFA fue verificado |
| POST /api/auth/login | email, password | Desafío provisional de cinco minutos; sin acceso a hallazgos |
| POST /api/auth/mfa | code | Sesión completa tras TOTP o recuperación; códigos de recuperación solo al enrolar |
| POST /api/auth/logout | CSRF | Revoca sesión |
| GET /api/findings | Sesión MFA | Matriz ordenada y política |
| POST /api/imports | multipart file | Lote y conteo; Analista/Administrador |
| POST /api/findings/{id}/cti | Sesión y CSRF | Snapshot y nueva revisión; Analista/Administrador |
| GET /api/findings/{id}/history | Sesión MFA | Revisiones y metadatos de evidencia |
| POST /api/findings/{id}/propose | treatment | Abierto → propuesto |
| POST /api/findings/{id}/evidence | multipart file | Archivo privado y hash |
| POST /api/findings/{id}/review | note | Propuesto → mitigado administrativo; Administrador diferente y evidencia previa |
| GET /api/evidence/{id} | Sesión MFA | Descarga como adjunto autorizado |
| GET /api/audit | Sesión MFA | Últimos eventos; Administrador/Auditor |
| GET /api/export | Sesión MFA | CSV de resultados, con representación segura para fórmulas |

No presentar estas rutas como OpenAPI completo: faltan esquemas formales, paginación y otras operaciones del objetivo. La primera versión admite CSV de máximo 500 filas y 2 MB; conserva el original. No procesa Excel, PDF de escáner ni conectores comerciales.

## Datos CTI

Para un CVE válido se conservarán EPSS, percentil, fecha, consulta, fuente y respuesta. KEV tendrá versión y fecha del catálogo, consulta/recuperación, presencia, entrada y catálogo original. El catálogo se podrá cachear 24 horas con su antigüedad visible; la nueva revisión conserva el snapshot usado. El prototipo no implementa caché EPSS ni fallback KEV vencido y no debe afirmarlo en el documento.

Estados mínimos: `ok`, `pending`, `missing`, `error`, `not_applicable`. El booleano KEV solo se interpreta como ausencia cuando se examinó un catálogo válido; un error produce desconocido. EPSS no disponible no produce probabilidad cero. Los errores no detienen la conservación de una decisión provisional. Ensayar timeout, 429/500, JSON inválido y respuesta sin CVE con mocks; conservar también consultas reales por separado.

No hay consulta automática para todos los casos en el prototipo inicial: el detalle permite consultar un CVE y crear nueva revisión. La actualización por lotes, reintentos acotados, TTL por proveedor y trabajo asíncrono forman parte del avance posterior.

## Pruebas necesarias más allá del avance inicial

La suite inicial verifica comportamientos del motor, TOTP, sesión provisional, permisos, importación, histórico y revisión con SQLite y CTI simulada. Sus reportes son evidencia auténtica de esas pruebas, pero no validación científica de la fórmula.

Antes de defender se deben completar: correspondencia CVSS vector/puntaje mediante calculador verificado; recuperación y expiración en navegador; intentos concurrentes de TOTP y descarga/archivo; CSV con límites y variantes; SQLi/XSS/IDOR; cobertura efectiva de todas las rutas por rol; MySQL con persistencia y restore; DAST y SAST; SBOM; tiempos de lote; CTI real y fallos; red Windows/AD/firewall; evaluación independiente.

La primera versión valida estructura de vector CVSS v4.0 Base y rango del puntaje, pero no recalcula la correspondencia del puntaje a partir del vector. El conjunto demostrativo utiliza una declaración de ejemplo y debe sustituirse por vectores sustentados del dataset académico. Esta limitación es relevante para E01/E02 y debe resolverse antes de validar prioridades.

## Prueba de rendimiento propuesta

Elegir previamente un objetivo medible para 500 filas en un entorno especificado. Como propuesta inicial, evaluar la meta histórica de tres segundos con CTI local/cacheada, dejando explícito que no incluye consultas en vivo ni valoración humana. Ejecutar al menos cinco repeticiones por tamaño, conservar registros y reportar media, mínimo, máximo, variación y memoria. Si no se cumple la meta, reportar fallo y analizar costos de parsing, DB, clasificación y serialización.

Con lote síncrono, medir desde el envío hasta la respuesta que confirma procesamiento completo. Al incorporar cola, medir hasta el estado completado; no hasta el HTTP 202. Distinguir tiempo de pantalla, motor y flujo completo. No anunciar complejidad O(1) para todo el lote o un tiempo garantizado sin datos.

## Respaldo y despliegue

La instalación reproducible debe restaurar dependencias desde lockfiles, generar clave de aplicación, configurar MySQL, ejecutar migraciones y crear cuentas sin contraseñas fijas. Los respaldos deben incluir BD y adjuntos, y conservar la clave necesaria para descifrar MFA en custodia protegida. Una restauración debe validarse en un ambiente separado con login, lectura de hallazgos, archivos e historial.

El avance local se mantiene en localhost. La publicación externa es una fase posterior con HTTPS, sesiones seguras, configuración de producción, control de secretos, backup y pruebas del entorno final. La existencia de un archivo Docker/README no constituye evidencia de despliegue limpio hasta ejecutarlo.

## Criterio académico de aceptación

RF01–RF10, requisitos no funcionales y controles elegidos tendrán resultados reales enlazados a E01–E35. Cada excepción se identificará por requisito y se explicará su efecto en las conclusiones. La defensa debe describir con precisión qué se implementó, qué se probó y qué quedó pendiente. La documentación y la aplicación deben compartir una misma fórmula, rutas, roles y versiones.
