# Revisión académica y técnica del proyecto PRIME

Fecha de revisión: 3 de octubre de 2026. Destinatarios: estudiantes de preespecialidad en Ciberseguridad Aplicada y orientador académico.

PRIME tiene un problema pertinente y una propuesta de arquitectura aprovechable. El principal trabajo pendiente consiste en hacer coherentes objetivos, algoritmo, implementación y validación. La versión revisada presenta como demostrados varios resultados para los que los archivos entregados no contienen registros suficientes. Esto no demuestra que las pruebas nunca se hayan realizado; significa que todavía no pueden verificarse a partir de esta entrega.

Se revisó el texto completo del PDF de 53 páginas, su presentación visual general y las 35 entradas del Word. Se verificó por separado la figura 7. El Word se inspeccionó por su estructura y contenido; su diseño de páginas no pudo verificarse porque el entorno no dispone de LibreOffice en el runtime proporcionado. El equipo confirmó que el trabajo realizado hasta esta revisión era teórico y que no existía implementación base. Por tanto, las pruebas descritas como aprobadas deben pasar a pendientes de ejecución. La defensa está programada para el 1 de diciembre de 2026. Las observaciones que siguen son una revisión del documento, no una auditoría de una aplicación en ejecución.

Las páginas se identifican como **página del archivo PDF / página impresa**. Desde la página 6 del archivo, la página impresa equivale a la página del archivo menos cinco.

## Qué conservar

El escenario ficticio permite formular un estudio aplicado sin atribuir incidentes a una empresa real. El alcance CSV → enriquecimiento CTI → triaje → tratamiento → verificación es defendible como MVP. Los tres roles, la arquitectura Angular/Laravel/MySQL y el uso de laboratorio aislado ofrecen una base útil. La lista del orientador exige una buena combinación de pruebas funcionales, seguridad, infraestructura, reproducibilidad y trazabilidad.

Las 35 entradas son requisitos de evidencia. Su inclusión en el Word no constituye cumplimiento ni demuestra que todas sean observaciones originales del jurado. No se recibió el acta completa del jurado: las referencias a calificaciones y versiones previas del PDF deben cotejarse con esa fuente antes de conservarlas.

## Observaciones que deben resolverse primero

### O01 Participación ausente de CVSS en el algoritmo

**Prioridad P0. Ubicación:** objetivo general, PDF 13/8; alcance, 14/9; fórmula, 19–20/14–15; componentes, 29–30/24–25. Evidencia relacionada: E02.

El objetivo promete integrar CVSS v4.0, DREAD, EPSS y KEV. Sin embargo, `IR_base = 0.30D + 0.25E + 0.20R + 0.15A + 0.10Ds` y `IRC = KEV ? 10 : min(10, IR_base × (1 + EPSS))` no incorporan CVSS. Su presencia en una columna del dataset no equivale a participación en el cálculo.

**Corrección:** definir una política explícita y versionada: incorporar CVSS a una función de prioridad y validarla, o describir CVSS como comparador y modificar el objetivo. La segunda opción debe conciliarse con E02, que pide participación real de CVSS. La propuesta de reemplazo adjunta plantea una combinación experimental, sin presentarla como fórmula oficial o validada.

### O02 Resultados y estados de prueba no verificables

**Prioridad P0. Ubicación:** PDF 12/7, 14/9, 20/15, 24/19, 40–42/35–37 y 47/42. Evidencias: E03–E15 y E35.

Se afirma desperdicio y ahorro del 60%, resolución del 80% de cuellos de botella, eliminación del 100% de falsos positivos, ejecución menor a 1.2 ms por registro y reducción de 14 horas a menos de tres segundos. La tabla TC-01 a TC-08 marca todos los casos como aprobados, pero no presenta resultado real, fecha, responsable, versión ni registro de ejecución. Los porcentajes requieren denominador, muestra, procedimiento y comparación.

**Corrección:** retirar afirmaciones concluyentes hasta adjuntar registros. Usar «objetivo de evaluación», «estimación» o «pendiente de verificación», según corresponda. Preservar cualquier resultado real que el equipo aporte, con su alcance exacto. No convertir resultados esperados en resultados observados.

### O03 La figura 7 contiene comandos sin salidas

**Prioridad P0. Ubicación:** PDF 39/34, sección 4.4 y figura 7. Evidencias: E20–E26 y E35.

La imagen muestra `ping -c 4 192.168.56.101` y `nmap -Pn -p 445 192.168.56.101`. No muestra salidas de ping o Nmap, fecha, interfaz, regla, registro del firewall ni resultado antes/después. Por tanto, no sustenta el rótulo «100% Packet Loss». Debe identificarse como ejemplo de comandos hasta reemplazarla por una ejecución original documentada.

**Corrección:** registrar TCP/445 y TCP/3389 desde orígenes permitidos y denegados; incluir configuración de rutas, interfaces, regla aplicada y logs. Validar que el servidor y el servicio estaban disponibles para un origen autorizado. ICMP fallido por sí solo no prueba contención SMB ni eficacia de IPS.

### O04 Deshabilitación de SMBv1 y requisito de reinicio

**Prioridad P0. Ubicación:** PDF 39–40/34–35 y 51/46, PB-01. Evidencias: E24–E26.

El proyecto atribuye contención «sin necesidad de reinicio» a un escenario que incluye deshabilitar SMBv1 mediante registro en Windows Server 2008 R2. Microsoft indica que ese cambio requiere reiniciar. La regla de firewall puede restringir conectividad antes del reinicio; eso no prueba que el protocolo haya quedado deshabilitado en el servicio. [Microsoft, administración de SMB](https://learn.microsoft.com/en-us/windows-server/storage/file-server/troubleshoot/detect-enable-and-disable-smbv1-v2-v3).

**Corrección:** separar control de red inmediato, cambio de configuración pendiente de reinicio y validación posterior. Documentar dependencia de la aplicación contable, compatibilidad SMB, ventana autorizada y reversión.

### O05 Dataset sin trazabilidad suficiente

**Prioridad P0. Ubicación:** PDF 33–36/28–31, tabla 7. Evidencias: E01, E16, E17 y E19.

Faltan producto/versión, vector CVSS, fuente y versión de CVSS, cinco dimensiones DREAD y sus justificaciones, percentil EPSS y fechas de los datos CTI. Los valores de severidad de CVE conocidos no permiten inferir que sean CVSS v4.0. Algunos podrían proceder de otras versiones; debe comprobarse cada uno. No es válido convertir un CVSS v3.1 en v4.0 cambiando la etiqueta.

**Corrección:** recuperar vector y fuente del fabricante o proveedor; si se calcula CVSS v4.0 internamente, conservar vector, responsable, fecha y justificación. Verificar producto afectado y aplicabilidad a cada activo. Identificar los datos de laboratorio, los supuestos del escenario y los datos públicos por separado.

### O06 EPSS aplicado a hallazgos sin CVE

**Prioridad P0. Ubicación:** H13, H14, H17 y H21–H25, PDF 35–36/30–31. Evidencias: E01, E02 y E16–E19.

Hay ocho casos sin identificador CVE: H13, H14, H17, H21, H22, H23, H24 y H25; los 17 restantes tienen CVE. Estos ocho incluyen configuraciones y hallazgos de aplicación. EPSS corresponde a CVE publicados y estima explotación observada en los próximos 30 días; no es una probabilidad particular del activo ni una predicción de explotación «masiva». El percentil es una posición relativa, no otra probabilidad. [FIRST, EPSS](https://www.first.org/epss/).

**Corrección:** los ocho casos necesitan EPSS `no_aplica` y KEV `no_aplica`, salvo que se documente un CVE aplicable. Un CVE ausente en una respuesta EPSS debe registrarse como `sin_dato`; un timeout como `error`; ninguna de esas situaciones equivale a EPSS cero. No asignar una probabilidad simulada bajo la etiqueta FIRST EPSS.

### O07 Diferencias aritméticas y redondeo ambiguo

**Prioridad P0. Ubicación:** tabla 7, PDF 35–36/30–31. Evidencias: E01, E02 y E11.

Se recalcularon los casos no KEV con los agregados DREAD y EPSS del propio PDF. Esto verifica únicamente la aritmética declarada, no la validez de las entradas:

| Caso | Operación de la fórmula publicada | Valor sin redondear | Redondeo convencional a un decimal | Publicado |
|---|---|---:|---:|---:|
| H13 | 8.4 × 1.10 | 9.24 | 9.2 | 8.8 |
| H14 | 8.6 × 1.15 | 9.89 | 9.9 | 9.8 |
| H17 | 7.9 × 1.08 | 8.532 | 8.5 | 8.5 |
| H21 | 4.2 × 1.05 | 4.41 | 4.4 | 4.4 |
| H22 | 4.0 × 1.02 | 4.08 | 4.1 | 4.1 |
| H23 | 2.1 × 1.01 | 2.121 | 2.1 | 2.1 |
| H24 | 3.8 × 1.04 | 3.952 | 4.0 | 3.9 |
| H25 | 3.0 × 1.01 | 3.03 | 3.0 | 3.0 |

H13 cambia de Alto a Crítico con la regla publicada. H24 muestra una diferencia entre clasificación sobre precisión completa y representación redondeada. No aplicar estas salidas como resultados corregidos del MVP: primero hay que resolver la invalidez de EPSS en los ocho casos y definir la fórmula definitiva.

**Corrección:** usar precisión suficiente, intervalos continuos y una regla explícita de presentación. Registrar puntaje calculado y puntaje mostrado. No ajustar salidas manualmente para conservar conclusiones previas.

### O08 Pesos DREAD sin instrumento reproducible

**Prioridad P0. Ubicación:** PDF 17–18/12–13. Evidencias: E01–E03.

Los pesos suman uno, pero la justificación narrativa no constituye una validación de esos pesos. No hay rúbrica que explique cómo dos analistas asignan el mismo valor a D, E, R, A y Ds. A mezcla usuarios afectados con movimiento lateral; R mezcla reproducibilidad con persistencia. Además, CVSS, DREAD E/R/Ds y EPSS pueden contabilizar señales relacionadas varias veces.

**Corrección:** declarar pesos iniciales propuestos, definir anclas observables, registrar justificación y evaluar sensibilidad. No atribuir a DREAD una probabilidad calibrada. Congelar los parámetros antes de obtener la evaluación independiente.

### O09 Saturación y override de KEV

**Prioridad P0. Ubicación:** PDF 19–20/14–15 y tabla 7. Evidencias: E02, E03 y E11.

Los 17 casos marcados KEV reciben 10; la regla elimina diferencias contextuales entre ellos. Además, `min(10, base × (1 + EPSS))` puede saturar casos no KEV. KEV confirma explotación conocida del CVE, no compromiso del activo. La aplicabilidad del producto debe verificarse antes de emitir tratamiento.

**Corrección:** evaluar mantener KEV como banda de atención urgente con orden contextual dentro de esa banda, o justificar el override y un desempate estable. El valor 10 es una decisión local de política, no una instrucción matemática de CISA. [CISA, catálogo KEV](https://www.cisa.gov/known-exploited-vulnerabilities-catalog?f%5B0%5D=vendor_project%3A800&page=1).

## Correcciones metodológicas y de alcance

### O10 Severidad y prioridad se confunden con falsos positivos

**Prioridad P0. Ubicación:** PDF 37/32 y 47/42. Evidencia: E03.

Una SQLi verdadera de un sistema aislado sigue siendo un hallazgo válido aunque su tratamiento tenga menor urgencia. Un falso positivo técnico requiere demostrar que la vulnerabilidad no existe o no es aplicable. CVSS-B caracteriza severidad, no riesgo empresarial; CVSS v4 también incluye grupos Threat y Environmental. [FIRST, guía CVSS](https://www.first.org/cvss/user-guide).

**Corrección:** hablar de «discrepancia de prioridad respecto de una referencia independiente». Comparar PRIME contra CVSS-B y, si se dispone de los vectores, incluir CVSS-BTE como comparación adicional. No diseñar el estudio para probar de antemano la superioridad de PRIME.

### O11 Validación independiente ausente y muestra limitada

**Prioridad P0. Ubicación:** PDF 15/10, 33/28, 37/32 y conclusiones. Evidencias: E01–E03, E14 y E15.

Faltan pregunta de investigación, criterios de selección, unidad de análisis, procedimiento de evaluación, métricas de concordancia y límites de generalización. Un conjunto intencional de 25 casos no demuestra eficacia universal ni reducción de incidentes. Los casos «reales simulados» deben distinguir CVE públicos de activos ficticios y condiciones efectivamente reproducidas.

**Corrección:** describir un estudio aplicado de laboratorio, comparación pareada sobre los mismos casos y evaluadores sin acceso a la salida PRIME al puntuar. Informar acuerdo ordinal, acuerdo entre evaluadores y diferencias justificadas. Los casos usados para ajustar pesos no deben contarse como una validación independiente sin declarar ese sesgo.

### O12 Riesgo residual sin modelo ni medición

**Prioridad P0. Ubicación:** PDF 23/18, 38/33, 47/42, 51/46. Evidencias: E24–E29 y E34.

La tabla 8 asigna 1.2, 1.4, 0.8, 1.0 y 0.5 sin fórmula, valores posteriores o prueba de control. Una captura de carga o el estado «Mitigado» no prueban reducción. Sumar puntajes ordinales no produce una medida de pérdida financiera.

**Corrección:** distinguir parcheado, mitigado, pendiente de verificación y riesgo aceptado. Preservar puntaje inherente y reevaluación posterior. Si el índice no incorpora exposición efectiva, añadir evidencia contextual o admitir que el control no cambia el puntaje. No cambiar KEV ni bajar CVSS-B para reflejar un firewall.

### O13 Alcance declarado menor que el comprometido

**Prioridad P1. Ubicación:** PDF 14/9 frente a 23/18, 31–32/26–27 y 51–53/46–48. Evidencias: E04, E13 y E33–E34.

El alcance resume CSV, cálculo y dashboard, mientras otras secciones comprometen MFA, RBAC, auditoría, cierre, recalculo residual y reportes PDF. Postura EDR, certificados de dispositivo, PAM/JIT, SIEM e IPS aparecen como funcionalidades o controles sin especificación implementable.

**Corrección:** una lista única de requisitos obligatorios y un listado explícito de capacidades propuestas para una fase futura. Zero Trust debe describirse mediante controles efectivamente demostrados. VLAN, MFA y RBAC por sí solos no prueban una arquitectura Zero Trust completa. [NIST SP 800-207](https://nvlpubs.nist.gov/nistpubs/SpecialPublications/NIST.SP.800-207.pdf).

### O14 Cronograma y presupuesto no comparten horizonte

**Prioridad P1. Ubicación:** PDF 16–17/11–12 y 42–44/37–39.

La ingeniería se planifica en 10 semanas, pero el presupuesto cubre cinco meses. Puede ser coherente si se explican preparación y cierre, pero el documento no lo hace. La selección del stack también usa puntuaciones de dominio del equipo que necesitan respaldo. No descartar ASP.NET por una supuesta incapacidad general para SPA: la comparación propuesta mezcla framework y patrón de interfaz.

**Corrección:** vincular entregables de cada sprint, responsables y capacidad real; explicar el horizonte de cinco meses o ajustar cantidades. Declarar el baremo como evaluación del equipo para este proyecto y adjuntar sus criterios.

### O15 Estimaciones económicas presentadas como hechos

**Prioridad P1. Ubicación:** PDF 12–13/7–8, 42–44/37–39 y 47/42.

Los supuestos financieros de una empresa ficticia no son observaciones empresariales. El costo laboral de 250 × 18.50 × 40 = USD 185,000 coincide con la cifra presentada como remediación forense/restauración; deben desglosarse conceptos y evitar doble conteo. El total del presupuesto sí suma correctamente: USD 2,687.20 × 5 + USD 300 = USD 13,736.00.

La amortización condicional de 13,736 / 1,480 es aproximadamente 9.28 meses, pero depende de un ahorro no medido. El 14.4% de un costo de incidente no es ROI. Los USD 37.20/mes incluyen solamente los tres rubros de infraestructura del presupuesto; al agregar USD 25 de tooling son USD 62.20/mes. Eso tampoco valida precios AWS ni la configuración multi-AZ.

**Corrección:** presentar supuestos y escenarios; obtener cotización con fecha, región, horas, almacenamiento, respaldos, tráfico, IPv4, alta disponibilidad e impuestos. Distinguir costo de oportunidad académico de desembolso. ROI requiere beneficios y costos dentro del mismo horizonte. No afirmar que cifras específicas de Apex proceden de IBM sin una página o tabla que lo respalde.

## Correcciones técnicas del MVP y del laboratorio

### O16 Sanctum no equivale a JWT

**Prioridad P0. Ubicación:** PDF 45/40 y 53/48, contrato API. Evidencias: E05, E12 y E30.

La expresión «Sanctum JWT final» mezcla mecanismos. Sanctum ofrece sesiones para SPA y tokens API; para una SPA propia, la documentación describe autenticación con cookies y protección CSRF. [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum).

**Corrección:** elegir y documentar un mecanismo único. Para Angular y API bajo un mismo dominio, se propone sesión con cookie HttpOnly, Secure, configuración SameSite y CSRF, con estado provisional sin acceso a datos hasta validar TOTP. Si se eligen tokens, definir expiración, revocación y MFA sin llamarlos JWT por defecto.

### O17 Versiones y soporte a la fecha de revisión

**Prioridad P1. Ubicación:** PDF 7/2, 14/9, 26/21, 28–29/23–24. Evidencias: E31–E32.

Laravel 11 terminó su soporte de seguridad el 12 de marzo de 2026; Angular 18 está fuera de soporte según las tablas oficiales consultadas. [Laravel, soporte](https://laravel.com/docs/11.x/releases); [Angular, versiones](https://angular.dev/reference/releases).

**Corrección:** conservar Laravel/Angular/MySQL como tecnologías, pero fijar versiones soportadas y compatibles para una construcción nueva. Si hay implementación existente, evaluar el costo de migración antes de elegir. Registrar versiones exactas y lockfiles. La referencia a EPSS v3 también debe cotejarse con la versión del modelo correspondiente al snapshot que se use; no suponer que el modelo de 2024 sigue vigente.

### O18 Defensas CSV SQLi y XSS sobregeneralizadas

**Prioridad P0. Ubicación:** PDF 31/26, 40–41/35–36 y 45/40. Evidencias: E08–E11.

Eliminar `= + - @ cmd` indiscriminadamente destruye datos y no demuestra protección. CSV Formula Injection se materializa especialmente cuando valores no confiables se abren en una hoja de cálculo. Deben revisarse también exportaciones, separadores, comillas, controles y variantes. [OWASP, CSV Injection](https://owasp.org/www-community/attacks/CSV_Injection).

Eloquent puede coexistir con SQL crudo inseguro. Angular tampoco protege automáticamente todo HTML, URL o bypass de sanitización. MIME no prueba validez del archivo.

**Corrección:** validar por esquema, rangos y límites; conservar el original con acceso restringido; definir representación segura para exportación; probar importación y posterior exportación. Mantener parametrización, validación de identificadores de ordenación, codificación de salida y pruebas de XSS almacenado. Sustituir «inmunidad» y «cero vulnerabilidades» por resultados de pruebas delimitadas.

### O19 MFA y RBAC carecen de ciclo completo

**Prioridad P0. Ubicación:** PDF 31–32/26–27 y 53/48. Evidencias: E05–E06.

Faltan enrolamiento y confirmación TOTP, cifrado del secreto, control de intentos, rechazo de reutilización, recuperación, revocación de sesión y autorización por objeto. Cambiar la interfaz no impide llamadas directas a la API. La matriz admite exportación para todos, mientras el endpoint la restringe al Auditor. Las pruebas usan `/login` y `/weights`; el catálogo muestra `/auth/login` y no incluye pesos.

**Corrección:** alinear rutas, matriz y pruebas. Separar propuesta de cierre del Analista, verificación del Administrador y auditoría de lectura. Registrar cambios de pesos, su versión y autor; un recálculo no debe sobrescribir el resultado original.

### O20 Windows LAPS y Microsoft LAPS legado se mezclan

**Prioridad P0. Ubicación:** PDF 27/22, 38/33 y 51/46, PB-02. Evidencia: E27.

El playbook usa `AdmPwd.dll` y `ms-Mcs-AdmPwd`, identificadores del producto legado, mientras la bibliografía describe Windows LAPS. Microsoft lista Windows Server 2019 y posteriores entre las plataformas de Windows LAPS; 2008 R2/2012 no pueden darse por compatibles con el producto moderno. [Microsoft, Windows LAPS](https://learn.microsoft.com/en-us/windows-server/identity/laps/laps-overview).

**Corrección:** identificar modalidad, sistema administrado, DC y esquema AD por separado. Demostrar rotación y acceso de lectura permitido/denegado; no publicar contraseñas. Si se usa LAPS en un cliente moderno, limitar a ese cliente la conclusión. No extrapolar su funcionamiento a servidores heredados.

### O21 Playbooks sin compatibilidad ni validación de fabricante

**Prioridad P1. Ubicación:** controles de la tabla 7, PDF 33–36/28–31, y anexos.

`formatMsgNoLookups` aparece como tratamiento de Log4Shell sin verificar versión y suficiencia; mitigaciones históricas, rutas de WAF y KB genéricas no deben presentarse como soluciones universales. MFA no corrige por sí sola un bypass de autenticación en un producto vulnerable. Exigir TLS 1.3 puede ser incompatible con aplicaciones heredadas. La ausencia de NLA no demuestra por sí sola BlueKeep, ni un certificado autofirmado constituye siempre una vulnerabilidad si la confianza se administra correctamente.

**Corrección:** cada playbook requiere producto, versión, aviso del fabricante, aplicabilidad, precondiciones, limitaciones, prueba funcional y rollback. Revisar todas las recomendaciones antes de ejecutarlas. Distinguir corrección de vulnerabilidad, mitigación temporal y hardening. No ejecutar cambios en infraestructura ajena a partir de esta revisión.

### O22 Topología y prueba no demuestran las VLAN descritas

**Prioridad P1. Ubicación:** PDF 27–28/22–23 y 39/34. Evidencias: E20–E23 y E33.

Kali y el servidor usan direcciones 192.168.56.x; sin máscaras, interfaces y rutas no puede deducirse que estén en segmentos distintos o que el NGFW observe el tráfico. Un aislamiento total que vuelve inaccesible la aplicación tampoco cumple el objetivo de continuidad. «Unidireccional» para SIEM necesita especificar transporte y flujos de retorno, no asumirlo por un dibujo.

**Corrección:** documentar redes virtuales o VLAN reales, router/firewall efectivo, jump host y flujos necesarios. Distinguir topología propuesta de implementada. Probar tráfico permitido y denegado en ambos sentidos, teniendo en cuenta el retorno de conexiones stateful.

### O23 Complejidad y rendimiento miden tareas distintas

**Prioridad P1. Ubicación:** PDF 20/15, 24/19, 41/36 y 51/46. Evidencias: E14–E15.

Una combinación de un número fijo de variables puede ser O(1) por caso; el lote requiere O(n) cálculos y su ordenación general O(n log n). HTTP, parsing y persistencia agregan costos. Se usan metas de <2.5 s, <3 s y <1.2 ms sin definir entorno ni alcance. Una aceptación HTTP asíncrona no es finalización del lote.

**Corrección:** separar tiempo del motor, procesamiento completo del lote y flujo humano. Medir con CTI cacheada y en vivo por separado; repetir 25/100/500 casos, registrar ambiente y dispersión. Elegir antes una meta de aceptación y reportar fallos.

### O24 Persistencia y API incompletas para trazabilidad

**Prioridad P1. Ubicación:** PDF 51–53/46–48. Evidencias: E12, E19, E30 y E34.

El diccionario enumera tablas y su PK, no los campos que permiten reproducir triajes. Faltan hallazgo por activo, snapshots CTI, cinco dimensiones DREAD, versiones de política, adjuntos, auditoría, lotes y reevaluaciones. El catálogo tampoco define esquemas JSON, errores, paginación, restricciones por rol y estados de lotes/cierre.

**Corrección:** completar modelo lógico y contrato API a partir de requisitos; la especificación adjunta propone entidades y reglas. Mantener fecha del dato CTI y fecha de consulta como campos distintos.

## Observaciones editoriales y de referencias

### O25 Índices y anexos necesitan actualizarse

**Prioridad P1. Ubicación:** PDF 2–5 y 51–53.

El índice general sitúa el capítulo IV en página impresa 26, pero comienza en 28; ubica conclusiones en 39, pero están en 42. El índice de figuras anuncia una figura 8 de mockup Angular que no se observa en los anexos revisados, y una figura 9 de ERD que aparece rotulada como «Anexo C». No basta con actualizar números: deben coincidir título, identificador y objeto real.

Hay tablas con encabezado aislado en la página anterior y sin repetición de encabezado, especialmente tabla 7. Las columnas estrechas fragmentan identificadores H01/CVE y prioridades. Los diagramas y ERD tienen texto muy pequeño. La página impresa 3 contiene pocas líneas y mucho espacio vacío.

**Corrección:** editar en el documento fuente, repetir encabezados, impedir filas partidas cuando corresponda y usar página horizontal para matrices extensas. Mejorar resolución y tamaño de diagramas. Regenerar índices al terminar. Solicitar la fuente editable del proyecto para conservar estilos universitarios; no reconstruirla automáticamente desde el PDF como si fuera equivalente.

### O26 Sustento bibliográfico y redacción

**Prioridad P1. Ubicación:** introducción, metodología y PDF 49–50/44–45.

Las afirmaciones sobre tiempos de detección, costos mínimos, porcentajes y formalización de DREAD requieren referencias específicas, no tres fuentes globales agrupadas. Una página general de SANS o el catálogo KEV no respaldan cualquier estadística. Verificar que autores y título de cada obra correspondan; no asumir que Shostack formaliza exactamente los pesos usados aquí.

**Corrección:** una ficha por afirmación con fuente, página/sección, fecha y alcance. Para fuentes que cambian, conservar snapshot y fecha de recuperación. Citar RFC 6238 para TOTP y OWASP ASVS como referencia para requisitos verificables, si se adopta. Explicar por qué se mantiene una versión histórica de OWASP Top 10 si el estudio la usa.

Eliminar referencias a «para superar contundentemente», notas sobre calificación previa y frases de inmunidad, bloqueo absoluto o superioridad garantizada. Corregir «Modelo Temático» del índice a «Modelo Matemático», duplicación de viñetas y «ataques aplicativas».

## Orden recomendado de corrección

1. Reconstruir el estado real de software y laboratorio; preservar evidencia existente.
2. Resolver O01, O05–O11 y congelar dataset, rúbrica y política antes de medir.
3. Alinear alcance, permisos, API y persistencia; preparar pruebas y manifestación de evidencia desde el inicio.
4. Implementar y probar MVP, CTI y controles del laboratorio, conservando fallos.
5. Reemplazar resultados esperados por observados, corregir conclusiones y recalcular factibilidad con supuestos claros.
6. Integrar anexos y regenerar referencias, índices y presentación para defensa.

## Documentos de apoyo

El plan de evidencias conserva exactamente las 35 solicitudes del orientador. Los textos de reemplazo corrigen objetivos, metodología, algoritmo y validación sin llenar resultados. La especificación técnica permite empezar una implementación posterior; no constituye una aplicación ya desarrollada ni una prueba de aceptación.
