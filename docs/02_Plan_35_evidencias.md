# Plan priorizado de las 35 evidencias de PRIME

Defensa: **1 de diciembre de 2026**. Punto de partida confirmado por el equipo: documento teórico sin implementación base. El prototipo inicial que se desarrolle a partir de esta revisión no convierte en aprobadas las pruebas históricas del PDF.

Este plan conserva la numeración, nombres, contenido requerido y formatos recomendados del archivo Partes a trabajar.docx. Se añaden dependencias, responsables sugeridos y criterios de aceptación. Una evidencia se acepta cuando su contenido es verificable y corresponde a la versión probada; no basta con crear el archivo o incluir una captura.

## Prioridades y fechas

P0: coherencia y validez de algoritmo/dataset, autenticación y autorización, integridad de datos, trazabilidad y resultados reales. P1: completar validación, laboratorio, reproducibilidad, rendimiento, SBOM y documentación. Las 35 evidencias siguen siendo necesarias; prioridad significa orden de trabajo.

| Etapa | Fechas de 2026 | Entregable y condición de salida |
|---|---|---|
| Preparación | 3–4 octubre | Diagnóstico, inventario de recursos y prototipo inicial identificado como experimental. |
| S1 | 5–11 octubre | E01/E02 diseñadas y congeladas para prueba; rúbrica, arquitectura, instrumento evaluador y matriz de trazabilidad. |
| S2 | 12–18 octubre | MVP con IAM/MFA/RBAC y CSV; pruebas reales E04–E08; versiones fijadas. |
| S3 | 19–25 octubre | CTI, snapshots, historial y E2E; E12/E13/E16–E19. |
| S4 | 26 octubre–1 noviembre | Evaluación independiente E03 y seguridad de entradas; pruebas unitarias e integración corregidas. |
| S5 | 2–8 noviembre | Laboratorio E20–E29 con controles, compatibilidad, reversión y respaldos. |
| S6 | 9–15 noviembre | Rendimiento, SAST/DAST, despliegue limpio, MySQL, versiones, SBOM y arquitectura real. |
| S7 | 16–22 noviembre | Resultados y conclusiones corregidos; trazabilidad E34 y paquete bruto E35; anexos completos. |
| S8 | 23–29 noviembre | Versión congelada, ensayo de defensa, demostración en vivo y alternativa offline documentada. |
| Reserva | 30 noviembre | Restauración de ensayo, revisión de enlaces y últimos errores sin ampliar alcance. |
| Defensa | 1 diciembre | Mostrar solo funcionalidades y conclusiones sustentadas por la versión validada. |

Es un cronograma propuesto de ocho semanas, sujeto a disponibilidad del equipo y del laboratorio. A más tardar el 11 de octubre debe confirmarse que hay hipervisor, imágenes de laboratorio con licencia apropiada, AD, firewall, RAM y evaluador independiente. Si una prueba no es viable, registrar la limitación y acordar con el orientador una alternativa equivalente; no sustituirla por resultados ficticios ni omitirla silenciosamente.

El trabajo de laboratorio puede comenzar mientras avanza el MVP si lo realizan integrantes distintos. Reservar investigación/documentación y QA desde el primer día. Los responsables que aparecen abajo son funciones, no asignaciones inventadas a personas concretas.

## Ruta crítica

E02 → E01 congelada → E03 independiente → comparación → conclusiones. En software: IAM → CSV → motor → CTI histórica → tratamiento → E2E → rendimiento/seguridad → despliegue. En infraestructura: topología → baseline → control → conectividad/servicio → reversión → backup/restauración. E34 y E35 acompañan todo el proceso.

## Registro de las 35 evidencias

Estado inicial de las 35 solicitudes: **pendiente de ejecución o verificación**. El diagnóstico y la especificación son insumos de preparación. Solo los reportes originales de la nueva implementación pueden sustentar un cambio de estado.

### E01 Dataset definitivo de 25 hallazgos

**Etapa:** S1. **Dependencias:** E02 inicial. **Responsable sugerido:** Investigación.

**Solicitud del orientador:** ID, CVE, activo, producto/versión, zona, vector y puntuación CVSS, dimensiones DREAD, EPSS, percentil EPSS, KEV, fecha de consulta, prioridad PRIME y tratamiento sugerido.

**Formato solicitado:** Excel (.xlsx) o CSV (.csv). Preferible Excel.

**Trabajo:** Reconstruir la tabla 7; mantener valores no verificados vacíos y congelar una versión antes de evaluar.

**Aceptación:** 25 casos únicos con producto, vector y versión CVSS, cinco dimensiones DREAD justificadas, estados CTI, fechas y procedencia; ocho casos sin CVE con CTI no aplicable.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E01/`.

### E02 Algoritmo PRIME definitivo

**Etapa:** S1. **Dependencias:** Ninguna. **Responsable sugerido:** Investigación y Backend.

**Solicitud del orientador:** Fórmula, variables, pesos, escalas, normalización, participación real de CVSS, tratamiento de DREAD, EPSS y KEV, umbrales y reglas de clasificación.

**Formato solicitado:** Word/PDF con explicación + capturas del código, o archivo de código si lo pueden adjuntar.

**Trabajo:** Resolver integración de CVSS, rúbrica DREAD y saturación; distinguir hipótesis de fórmula validada.

**Aceptación:** Política versionada, pesos y escalas documentados; CVSS afecta salidas en pruebas controladas; nulos, KEV, desempates y redondeo definidos.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E02/`.

### E03 Matriz de evaluación independiente

**Etapa:** S4. **Dependencias:** E01 y E02 congeladas. **Responsable sugerido:** Evaluador externo.

**Solicitud del orientador:** Prioridad asignada por evaluador(es) a los 25 casos, criterio utilizado y justificación de cada decisión.

**Formato solicitado:** Excel (.xlsx). Idealmente una hoja por evaluador y una hoja consolidada.

**Trabajo:** Preparar instrumento y conseguir al menos un evaluador ajeno al desarrollo; preferir dos. No sustituirlo por salidas de IA.

**Aceptación:** 25 decisiones por evaluador con criterio y justificación, sin ver salida PRIME al puntuar; consolidación y discrepancias conservadas.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E03/`.

### E04 Pruebas funcionales del MVP

**Etapa:** S2–S6. **Dependencias:** E02 y MVP. **Responsable sugerido:** QA.

**Solicitud del orientador:** ID de prueba, objetivo, entrada, pasos, resultado esperado, resultado real, estado, fecha, responsable y observación.

**Formato solicitado:** Excel/Word con matriz de pruebas + capturas de pantalla como evidencia.

**Trabajo:** Reemplazar TC-01 a TC-08 aprobados por casos pendientes; extender a estados y errores reales.

**Aceptación:** Cada requisito tiene prueba positiva/negativa, resultado real, estado, fecha, versión, responsable y evidencia enlazada.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E04/`.

### E05 Pruebas de autenticación y MFA

**Etapa:** S2. **Dependencias:** IAM implementado. **Responsable sugerido:** Backend y QA.

**Solicitud del orientador:** Login correcto/incorrecto, segundo factor TOTP, expiración, recuperación, cierre de sesión y rechazo de acceso no autorizado.

**Formato solicitado:** Capturas de pantalla + breve registro en Word/Excel.

**Trabajo:** Impedir acceso a datos con sesión provisional; cifrar secreto y registrar eventos sin códigos ni secretos.

**Aceptación:** Login válido/inválido, enrolamiento, TOTP ausente/incorrecto/expirado/reutilizado, recuperación de un uso, logout y expiración comprobados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E05/`.

### E06 Pruebas RBAC y autorización

**Etapa:** S2. **Dependencias:** E05. **Responsable sugerido:** Backend y QA.

**Solicitud del orientador:** Qué puede y qué no puede hacer Analista, Administrador y Auditor; intentos permitidos y denegados.

**Formato solicitado:** Capturas + matriz Excel de operación/rol/resultado.

**Trabajo:** Alinear matriz, endpoints y pantallas; llamadas directas deben denegar operaciones aunque se altere la interfaz.

**Aceptación:** Todos los pares rol/operación probados en API; permisos por objeto y separación propuesta/aprobación verificadas.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E06/`.

### E07 Prueba de carga de CSV válido

**Etapa:** S2. **Dependencias:** Parser y E01. **Responsable sugerido:** Backend y QA.

**Solicitud del orientador:** Archivo aceptado, número de registros procesados, resultado obtenido, mensajes del sistema.

**Formato solicitado:** CSV usado + capturas del sistema.

**Trabajo:** Probar el CSV de entrada completo y mostrar ID de lote, errores y salida.

**Aceptación:** Cuenta de filas aceptadas/rechazadas coincide; consulta del lote devuelve resultados y snapshot de política; archivo original conservado.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E07/`.

### E08 Pruebas de CSV incorrecto o malicioso

**Etapa:** S2–S4. **Dependencias:** E07. **Responsable sugerido:** QA.

**Solicitud del orientador:** Rechazo de encabezados incorrectos, duplicados, datos nulos, encoding, tamaño excesivo y CSV Formula Injection.

**Formato solicitado:** Archivos CSV de prueba + capturas/logs.

**Trabajo:** Preparar fixtures inocuos y probar tanto importación como exportación; no ejecutar fórmulas.

**Aceptación:** Encabezados, duplicados, nulos, UTF-8/BOM, comillas, tamaño y fórmulas tienen comportamiento definido y verificado; salida exportada segura.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E08/`.

### E09 Pruebas de SQLi, XSS y otras entradas maliciosas

**Etapa:** S4. **Dependencias:** E05–E08. **Responsable sugerido:** Seguridad y QA.

**Solicitud del orientador:** Payload utilizado, punto donde se probó, respuesta del sistema y evidencia de que se bloqueó o procesó de forma segura.

**Formato solicitado:** Capturas, logs y, si usan herramienta, reporte HTML/PDF.

**Trabajo:** Probar únicamente el MVP de laboratorio; no considerar Eloquent o Angular evidencia suficiente.

**Aceptación:** SQLi, XSS reflejado/almacenado, autorización por ID, límites y archivos se prueban con entrada y salida guardadas; fallos se reportan.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E09/`.

### E10 Pruebas de seguridad automatizadas

**Etapa:** S6. **Dependencias:** Código y entorno desplegado. **Responsable sugerido:** Seguridad.

**Solicitud del orientador:** Resultados SAST, DAST, análisis de dependencias y vulnerabilidades identificadas.

**Formato solicitado:** Preferible reporte PDF/HTML exportado por la herramienta; capturas solo como complemento.

**Trabajo:** Exportar reportes originales, correcciones y repetición posterior; no declarar seguro por un reporte vacío.

**Aceptación:** SAST, DAST y dependencias con alcance/versiones; hallazgos triados; riesgos bloqueantes resueltos o documentados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E10/`.

### E11 Pruebas unitarias

**Etapa:** S1–S6. **Dependencias:** E02 y módulos. **Responsable sugerido:** Backend.

**Solicitud del orientador:** Casos ejecutados, total, aprobados, fallidos, tiempo de ejecución y módulo evaluado.

**Formato solicitado:** Reporte de PHPUnit u otra herramienta, preferiblemente HTML/XML/PDF; capturas adicionales.

**Trabajo:** Probar comportamiento y límites, no solo líneas de código; añadir casos de regresión H13/H14/H24 sin convertir CTI ficticia en real.

**Aceptación:** Suite reproducible de motor, umbrales, CTI, parser, MFA y estados con aprobados/fallidos/tiempo y salida original.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E11/`.

### E12 Pruebas de integración

**Etapa:** S3. **Dependencias:** API, Angular y BD. **Responsable sugerido:** Backend Frontend QA.

**Solicitud del orientador:** Comunicación entre frontend, backend, base de datos y servicios CTI.

**Formato solicitado:** Reporte Word/Excel + capturas/logs.

**Trabajo:** Separar mocks controlados de CTI en vivo y SQLite local de MySQL definitivo.

**Aceptación:** Angular/API/BD/CTI funcionan juntos; transacciones y fallos documentados; integración MySQL ejecutada explícitamente.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E12/`.

### E13 Pruebas E2E

**Etapa:** S3–S6. **Dependencias:** E05–E07 y tratamiento. **Responsable sugerido:** QA.

**Solicitud del orientador:** Flujo completo: login → carga → triaje → visualización → tratamiento → cierre.

**Formato solicitado:** Capturas secuenciales o PDF/Word con evidencias numeradas.

**Trabajo:** Guardar secuencia numerada y un intento de cierre sin evidencia que sea denegado.

**Aceptación:** Login → carga → triaje → visualización → propuesta → verificación → cierre con estados persistidos y permisos reales.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E13/`.

### E14 Resultados de rendimiento

**Etapa:** S6. **Dependencias:** E12 y E15. **Responsable sugerido:** QA.

**Solicitud del orientador:** Cantidad de registros, número de repeticiones, tiempo por ejecución, promedio, mínimo, máximo, ambiente y hardware utilizado.

**Formato solicitado:** Excel (.xlsx) con resultados + capturas de consola o herramienta.

**Trabajo:** Medir motor, lote completo y CTI por separado; informar arranque frío y caché; no rellenar 1.2 ms.

**Aceptación:** Al menos cinco repeticiones por tamaño y condición; tiempos crudos, media, mínimo, máximo y ambiente; cola termina antes de detener reloj.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E14/`.

### E15 Prueba con distintos tamaños de dataset

**Etapa:** S6. **Dependencias:** Generador y E01. **Responsable sugerido:** QA.

**Solicitud del orientador:** Comportamiento con 25, 100, 500 o la cantidad que puedan evaluar; tiempo total de procesamiento.

**Formato solicitado:** Excel + capturas/logs.

**Trabajo:** Usar 25 casos académicos; ampliar por generación controlada solo para carga, no para validar prioridad.

**Aceptación:** 25/100/500 filas procesadas con conteos correctos y datos sintéticos identificados; tiempo y memoria por ejecución.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E15/`.

### E16 Consultas EPSS

**Etapa:** S3. **Dependencias:** CVE aplicables. **Responsable sugerido:** Backend e Investigación.

**Solicitud del orientador:** CVE consultado, valor EPSS, percentil, fecha, respuesta y fuente.

**Formato solicitado:** CSV/JSON exportado, si es posible; adicionalmente capturas.

**Trabajo:** Guardar snapshot; no sobrescribir registros históricos ni consultar EPSS para una mala configuración sin CVE.

**Aceptación:** JSON original con CVE, epss, percentil, fecha del dato, consulta y fuente; ausencia/error separados de cero.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E16/`.

### E17 Consultas CISA KEV

**Etapa:** S3. **Dependencias:** CVE aplicables. **Responsable sugerido:** Backend e Investigación.

**Solicitud del orientador:** CVE consultado, presencia/no presencia en KEV, fecha y fuente.

**Formato solicitado:** JSON/CSV o captura de respuesta + registro en Excel.

**Trabajo:** Conservar catálogo completo y fragmento por CVE; ausencia no equivale a imposibilidad de explotación.

**Aceptación:** Catálogo original con versión/fecha/hash; presencia y ausencia demostradas contra ese snapshot; aplicabilidad documentada.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E17/`.

### E18 Prueba de falla de servicios CTI

**Etapa:** S3. **Dependencias:** E16–E17. **Responsable sugerido:** QA.

**Solicitud del orientador:** Qué ocurre cuando EPSS/KEV no responde, timeout, error HTTP o falta el CVE; uso de caché o reintentos.

**Formato solicitado:** Capturas/logs + breve matriz Word/Excel.

**Trabajo:** Pruebas controladas sin interrumpir proveedores; fallos nunca se convierten silenciosamente en cero o falso.

**Aceptación:** Timeout, HTTP 429/500, respuesta inválida y CVE sin dato reproducidos con mocks; caché/antigüedad/reintento visibles.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E18/`.

### E19 Prueba de trazabilidad CTI

**Etapa:** S3. **Dependencias:** Snapshots y política. **Responsable sugerido:** Backend y QA.

**Solicitud del orientador:** Que una decisión conserve el dato original, fecha de consulta y versión aunque posteriormente cambie la fuente.

**Formato solicitado:** Capturas de BD/dashboard + registro en Excel.

**Trabajo:** Prueba con dos snapshots distintos y consulta de historial; almacenar payload/hash en ubicación recuperable.

**Aceptación:** Un triaje antiguo conserva dato original y fórmula aunque cambie CTI; recálculo crea nueva versión con motivo.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E19/`.

### E20 Prueba de microsegmentación

**Etapa:** S5. **Dependencias:** Topología E33 y baseline. **Responsable sugerido:** Infraestructura y Seguridad.

**Solicitud del orientador:** Host origen, host destino, protocolo, puerto, resultado permitido/denegado y regla asociada.

**Formato solicitado:** Capturas de comandos, logs de firewall y matriz Excel.

**Trabajo:** Documentar interfaces, VLAN o redes virtuales; comprobar que los flujos pasan por el control.

**Aceptación:** Matriz origen/destino/protocolo/puerto/regla muestra allow/deny reales; acceso necesario permanece.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E20/`.

### E21 Pruebas de puertos

**Etapa:** S5. **Dependencias:** E20. **Responsable sugerido:** Infraestructura y QA.

**Solicitud del orientador:** Estado real de puertos relevantes —por ejemplo SMB/RDP— desde orígenes autorizados y no autorizados.

**Formato solicitado:** Capturas de Nmap/PowerShell/Test-NetConnection + resultados en Excel.

**Trabajo:** Guardar resultados originales de sondas y prueba funcional autorizada; ICMP no sustituye TCP.

**Aceptación:** TCP/445 y TCP/3389 probados desde orígenes permitidos y denegados; estado y servicio disponible verificados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E21/`.

### E22 Prueba de rutas y tráfico inverso

**Etapa:** S5. **Dependencias:** E20–E21. **Responsable sugerido:** Infraestructura.

**Solicitud del orientador:** Que no solo se evaluó ICMP; rutas, tráfico TCP y comunicaciones en ambos sentidos.

**Formato solicitado:** Capturas de consola + logs.

**Trabajo:** Probar ida, retorno y tráfico inverso; descartar servicio apagado o error de direccionamiento como falso bloqueo.

**Aceptación:** Rutas, máscaras y conexiones iniciadas en ambos sentidos registradas; retorno stateful se distingue de nueva conexión.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E22/`.

### E23 Logs de firewall/NGFW

**Etapa:** S5. **Dependencias:** E20–E22. **Responsable sugerido:** Infraestructura.

**Solicitud del orientador:** Fecha, origen, destino, puerto, acción allow/deny y regla aplicada.

**Formato solicitado:** Exportación CSV/TXT/PDF de logs; capturas como complemento.

**Trabajo:** Sincronizar relojes y guardar logs completos; un deny de firewall no demuestra IPS.

**Aceptación:** Logs exportados con tiempo, origen, destino, puerto, acción y regla correlacionados con casos; si se afirma IPS hay alerta correspondiente.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E23/`.

### E24 Prueba de control compensatorio en legacy

**Etapa:** S5. **Dependencias:** Baseline y playbook. **Responsable sugerido:** Infraestructura y QA.

**Solicitud del orientador:** Estado antes del control, control aplicado, estado después y evidencia de cambio.

**Formato solicitado:** Word/PDF con capturas “antes/después” + logs.

**Trabajo:** Aplicar control compatible en laboratorio aislado; no bajar riesgo residual sin reevaluación sustentada.

**Aceptación:** Antes/después del control y prueba de servicio; reinicio registrado si requerido; conclusión limitada a flujos medidos.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E24/`.

### E25 Prueba de reversión

**Etapa:** S5. **Dependencias:** E24 y respaldo. **Responsable sugerido:** Infraestructura.

**Solicitud del orientador:** Que el cambio puede revertirse sin dejar el servicio inutilizable.

**Formato solicitado:** Capturas, comandos y breve registro en Word.

**Trabajo:** Probar reversión en una copia del laboratorio; conservar evidencia de cualquier falla.

**Aceptación:** Rollback ejecutado y servicio recuperado; parámetros previos, tiempo y resultado guardados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E25/`.

### E26 Prueba de compatibilidad del servicio

**Etapa:** S5. **Dependencias:** E24. **Responsable sugerido:** Infraestructura y usuario de prueba.

**Solicitud del orientador:** Que SMB, RDP, aplicación heredada u otro servicio requerido continúa funcionando después del hardening.

**Formato solicitado:** Capturas de funcionamiento + observación técnica.

**Trabajo:** Demostrar SMB/RDP o aplicación contable de prueba; no usar ping como prueba funcional.

**Aceptación:** Servicio necesario realmente funciona después del control y del reinicio; versión y operación de negocio descritas.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E26/`.

### E27 Evidencia de LAPS

**Etapa:** S5. **Dependencias:** AD y host compatibles. **Responsable sugerido:** Infraestructura.

**Solicitud del orientador:** Modalidad realmente utilizada, rotación, permisos de lectura y compatibilidad con los sistemas del laboratorio.

**Formato solicitado:** Capturas de configuración, PowerShell/GPO y, si existe, logs.

**Trabajo:** Definir Windows LAPS o producto legado según compatibilidad; ocultar secretos; no atribuir compatibilidad a 2008 R2 sin comprobarla.

**Aceptación:** Modalidad LAPS identificada; rotación efectiva y permisos lectura allow/deny; SO, esquema y DC documentados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E27/`.

### E28 Prueba de backup

**Etapa:** S5. **Dependencias:** BD, adjuntos y laboratorio. **Responsable sugerido:** Infraestructura y Backend.

**Solicitud del orientador:** Fecha, tipo de copia, tamaño, ubicación y resultado.

**Formato solicitado:** Capturas/logs + registro en Word/Excel.

**Trabajo:** Distinguir backup del MVP de snapshot de VM; definir RPO/RTO propuestos antes de medir.

**Aceptación:** Copia con fecha, tipo, alcance, tamaño, ubicación/hash y logs; incluye adjuntos y claves necesarias bajo custodia.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E28/`.

### E29 Prueba de restauración

**Etapa:** S5–S6. **Dependencias:** E28. **Responsable sugerido:** Infraestructura y QA.

**Solicitud del orientador:** Restauración exitosa desde respaldo, tiempo empleado y validación posterior.

**Formato solicitado:** Capturas secuenciales + registro de tiempo.

**Trabajo:** No sobrescribir el entorno único; medir recuperación desde inicio hasta verificación funcional.

**Aceptación:** Restauración en entorno separado; conteos, autenticación, adjuntos y función de triaje comprobados; tiempo real registrado.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E29/`.

### E30 Prueba de despliegue reproducible

**Etapa:** S6. **Dependencias:** Aplicación y lockfiles. **Responsable sugerido:** Backend y Frontend.

**Solicitud del orientador:** Instalación del MVP desde entorno limpio, dependencias, migraciones y funcionamiento final.

**Formato solicitado:** README.md o Word/PDF + capturas.

**Trabajo:** Preparar .env.example sin secretos; scripts locales de arranque y limpieza controlada; guardar registro de instalación.

**Aceptación:** Instalación limpia siguiendo README sin pasos ocultos; migraciones, usuario inicial, worker y CTI funcionan.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E30/`.

### E31 Versiones del stack

**Etapa:** S2 y S6. **Dependencias:** Dependencias fijadas. **Responsable sugerido:** Backend y Frontend.

**Solicitud del orientador:** Versiones exactas de PHP, Laravel, Node, Angular, MySQL y paquetes principales.

**Formato solicitado:** Capturas de consola + composer.json, composer.lock, package.json y lockfile.

**Trabajo:** Usar versiones soportadas; actualizar tabla tecnológica y distinguir versión planificada de ejecutada.

**Aceptación:** Versiones exactas de PHP/Laravel/Node/Angular/MySQL y paquetes, lockfiles y fecha; respaldo de compatibilidad.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E31/`.

### E32 SBOM y dependencias

**Etapa:** S6. **Dependencias:** E31. **Responsable sugerido:** Seguridad.

**Solicitud del orientador:** Inventario de componentes y vulnerabilidades conocidas.

**Formato solicitado:** Archivo SBOM JSON/XML/SPDX/CycloneDX + reporte de escaneo.

**Trabajo:** Generar CycloneDX o SPDX; listado manual no equivale a SBOM verificado.

**Aceptación:** SBOM generado desde dependencias reales con versión/herramienta/fecha; escaneo y tratamiento de avisos.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E32/`.

### E33 Arquitectura definitiva

**Etapa:** S1 diseño S6 definitiva. **Dependencias:** Componentes implementados. **Responsable sugerido:** Arquitectura.

**Solicitud del orientador:** Componentes reales implementados, flujos, límites de confianza y zonas.

**Formato solicitado:** Imagen PNG/JPG o PDF del diagrama.

**Trabajo:** Mantener diagrama propuesto separado del implementado; eliminar SIEM/IPS/PAM si no están demostrados.

**Aceptación:** Diagrama final coincide con despliegue, red, worker, CTI y persistencia; límites de confianza y flujos etiquetados.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E33/`.

### E34 Matriz de trazabilidad

**Etapa:** Desde S1 cierre S7. **Dependencias:** E01–E33. **Responsable sugerido:** Investigación y QA.

**Solicitud del orientador:** Objetivo → requisito → componente → prueba → evidencia → resultado → conclusión.

**Formato solicitado:** Excel (.xlsx).

**Trabajo:** Mantener matriz viva; no marcar completo un objetivo por tener capturas sin pruebas.

**Aceptación:** Cada objetivo y requisito enlaza componente, caso, archivo evidencia, resultado real y conclusión proporcional.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E34/`.

### E35 Evidencia bruta de todas las pruebas

**Etapa:** Desde S1 cierre S7. **Dependencias:** Todas las ejecuciones. **Responsable sugerido:** QA.

**Solicitud del orientador:** Capturas originales, logs, reportes, comandos, fechas y archivos utilizados.

**Formato solicitado:** Carpeta comprimida .zip organizada por prueba.

**Trabajo:** Conservar originales y fallos; añadir manifiesto SHA256 y distinguir pruebas con mock de consultas reales.

**Aceptación:** Carpeta por prueba con entradas, salidas, fecha, versión, responsable y hashes; ZIP legible sin secretos.

**Estado:** pendiente de ejecución o verificación. **Ruta prevista:** `evidencias/E35/`.

## Plantillas de registros

Para cada prueba: ID, requisito, objetivo, precondiciones, versión/commit, entorno, entrada/fixture, pasos, resultado esperado, resultado real, estado, fecha y zona horaria, responsable, ruta de evidencia, hash y observaciones. Estados: pendiente, ejecutada satisfactoria, ejecutada fallida, bloqueada y no aplicable justificado. Una prueba fallida también es evidencia; no es cumplimiento del requisito.

Para E34 usar columnas Objetivo → Requisito → Componente → Prueba → Evidencia → Resultado → Conclusión. Ejemplo de una fila **planificada**, sin resultado: OE1 → RF03 cálculo versionado → Motor → UT-CVSS-01 → E11/reporte → Pendiente → Sin conclusión empírica.

Para el manifiesto E35: archivo, E-ID, prueba, fecha, versión, origen, tamaño, SHA256, responsable y clasificación real/mock/sintética. Guardar capturas originales, salidas de consola y reportes; cualquier imagen editada para presentación debe conservar su original.

## Criterios de cierre para el 22 de noviembre

1. Las 35 solicitudes tienen entrega aceptable o una limitación explícita acordada con el orientador; las no ejecutadas no se cuentan como aprobadas.
2. No hay diferencias sin explicar entre documento, fórmula, código, dataset y resultados.
3. MFA/RBAC/validación/backup tienen pruebas negativas y positivas; el flujo principal corre en un entorno limpio con MySQL.
4. Los resultados de 25 casos no se extrapolan a todos los entornos; tiempos de datos sintéticos no se usan como prueba de calidad de prioridad.
5. Existe demostración reproducible y un respaldo offline identificado. Los datos CTI congelados se muestran como snapshot, no como consulta en vivo.
6. Toda conclusión tiene evidencia enlazada; se retiran porcentajes sin medición y afirmaciones absolutas.

## Guion de defensa propuesto

Presentar el problema y la pregunta de investigación; explicar diferencia entre severidad y prioridad; mostrar una decisión reproducible y el dato CTI que la sustentó. Ejecutar login con MFA, un permiso denegado, carga válida e inválida, clasificación, detalle de un caso y propuesta/verificación de tratamiento. Mostrar histórico intacto tras un recálculo. Explicar una prueba de laboratorio antes/después que preserve el servicio. Terminar con resultados reales, limitaciones de la muestra y trabajo futuro. No prometer SOAR, PAM, SIEM o IPS si no forman parte del despliegue probado.
