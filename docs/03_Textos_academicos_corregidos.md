# Propuesta de corrección y ampliación académica de PRIME

Los siguientes textos están preparados para incorporarse al documento académico, manteniendo la estructura universitaria y adaptando su numeración en el archivo fuente. Describen diseño y evaluación prevista. El equipo confirmó que el contenido original era teórico. La implementación inicial posterior a esta revisión debe presentarse con su versión, sus pruebas ejecutadas y sus limitaciones; no respalda retroactivamente los resultados del PDF.

## Título propuesto

Diseño y validación en laboratorio de una plataforma web de triaje contextual de vulnerabilidades basada en CVSS DREAD e inteligencia de amenazas con controles alineados con Zero Trust para el escenario Consultores Apex SA

El título puede conservar el original si existe aprobación institucional. EPSS y KEV deben aparecer en objetivo, alcance y marco técnico aunque el título aprobado no se cambie. Consultores Apex S.A. se identifica expresamente como escenario ficticio.

## Introducción

La gestión de vulnerabilidades exige transformar hallazgos técnicos en decisiones de tratamiento compatibles con los recursos, los activos y las restricciones de cada organización. El puntaje CVSS Base describe la severidad de una vulnerabilidad, pero su interpretación operativa necesita información sobre el entorno y las amenazas. Una prioridad de tratamiento no equivale a la existencia o ausencia de la vulnerabilidad ni a la certeza de que un activo vaya a ser comprometido. FIRST diferencia las métricas Base, Threat y Environmental dentro de CVSS v4.0, por lo que el uso de una puntuación base aislada debe distinguirse del uso contextual del estándar. [FIRST, CVSS v4.0](https://www.first.org/cvss/v4.0/specification-document).

Esta investigación propone PRIME como un MVP de apoyo al triaje contextual para el escenario ficticio Consultores Apex S.A. La plataforma incorporará severidad CVSS, una valoración DREAD con criterios explícitos y datos públicos de EPSS y CISA KEV cuando exista un CVE aplicable. Cada decisión deberá conservar los datos utilizados, la fecha de consulta, la versión de la política y la justificación del analista.

La validación se realizará en un laboratorio controlado y mediante un conjunto de 25 hallazgos definidos para el estudio. Se comparará la prioridad producida por PRIME con una evaluación independiente y con una línea base de severidad CVSS. También se comprobarán funciones del software y controles seleccionados de acceso, segmentación y tratamiento de sistemas heredados. Los resultados se limitarán a los casos, versiones, flujos y condiciones efectivamente evaluados.

## Marco de autorización y alcance del laboratorio

Consultores Apex S.A. es una organización ficticia utilizada para modelar necesidades operativas. Sus características de tamaño, personal, aplicaciones y costos constituyen supuestos del escenario; no son datos obtenidos de una empresa real. Las actividades técnicas deberán ejecutarse únicamente sobre infraestructura propia o expresamente autorizada, con un inventario de activos y responsables del laboratorio.

Antes de las pruebas se documentarán redes, máquinas virtuales, versiones de sistemas, servicios autorizados, fechas y condiciones de interrupción. Se conservarán copias o snapshots necesarios para restaurar el ambiente. Las pruebas de conectividad, autenticación y hardening no se extenderán a redes públicas ni a activos ajenos al estudio. La autorización del propietario del laboratorio y el plan de pruebas son registros distintos de la autorización corporativa hipotética del escenario Apex.

Las actividades ejecutadas se describirán en pasado únicamente cuando exista su registro. Los casos públicos de vulnerabilidad utilizados como insumo se diferenciarán de vulnerabilidades reproducidas y verificadas en laboratorio.

## Planteamiento del problema

En el escenario de estudio, la priorización basada exclusivamente en una lista de severidades puede omitir restricciones de mantenimiento, importancia del activo y señales de explotación conocidas. El problema investigado consiste en la falta de un proceso reproducible que relacione estos elementos con decisiones de tratamiento y permita explicar posteriormente por qué se asignó una prioridad.

No se presupone que CVSS produzca falsos positivos ni que todos los hallazgos de severidad alta deban tratarse de inmediato. Se examinarán diferencias entre severidad técnica y prioridad contextual. Tampoco se considerará demostrado un ahorro de horas de análisis hasta contar con mediciones comparables del procedimiento manual y del flujo asistido.

Pregunta de investigación: ¿en qué medida una política explícita que combina CVSS, DREAD y datos CTI obtiene prioridades concordantes con una evaluación independiente y permite justificar decisiones reproducibles en los 25 casos del escenario de laboratorio?

## Objetivo general

Diseñar, implementar y validar en laboratorio un MVP web de triaje contextual de vulnerabilidades para el escenario ficticio Consultores Apex S.A., que combine CVSS, DREAD y datos EPSS/KEV aplicables mediante una política explícita y versionada, conserve la trazabilidad de las decisiones y permita documentar tratamientos y controles de acceso y segmentación alineados con principios Zero Trust.

## Objetivos específicos

1. Definir un conjunto de 25 hallazgos con procedencia, aplicabilidad, contexto de activo, vector y versión CVSS, rúbrica DREAD y datos CTI fechados; especificar una política de prioridad que permita reproducir cada resultado.
2. Implementar el MVP con Laravel, Angular y MySQL, incorporando importación CSV, autenticación TOTP, autorización por rol, historial de triaje, propuestas de tratamiento, evidencias y consulta de auditoría.
3. Evaluar las prioridades del MVP frente a CVSS Base y evaluadores independientes; ejecutar pruebas unitarias, funcionales, de integración, E2E, seguridad y rendimiento con resultados reales y registros originales.
4. Diseñar y probar controles seleccionados de segmentación y tratamiento en el laboratorio, documentando estado previo, resultado posterior, compatibilidad del servicio y reversión, sin atribuir a estos ensayos protección universal.

Los objetivos de esta propuesta sustituyen la exigencia de «demostrar eliminación de falsos positivos» por una evaluación que admite resultados favorables, desfavorables o inconclusos.

## Alcance funcional y límites

El MVP académico deberá soportar el flujo login y MFA → importación CSV → triaje → consulta de detalle e histórico → propuesta de tratamiento → adjunto de evidencia → revisión y cierre administrativo. La autorización se verificará en backend para los roles Analista, Administrador y Auditor. Los datos CTI se obtendrán de fuentes públicas y se conservarán con sus fechas y respuestas originales. Se generarán reportes de triaje con identificación de la política y condiciones de cálculo.

La integración con firewalls para ejecutar reglas automáticamente, los conectores comerciales con escáneres y la orquestación SOAR se consideran trabajo futuro. Postura del dispositivo, PAM/JIT, SIEM e IPS solo podrán describirse como implementados si existen componentes y pruebas que lo demuestren. El laboratorio evaluará un subconjunto explícito de controles Zero Trust; el uso de VLAN o MFA no implica certificación ni implementación completa de una arquitectura ZTA. [NIST SP 800-207](https://nvlpubs.nist.gov/nistpubs/SpecialPublications/NIST.SP.800-207.pdf).

Se distinguirá cierre administrativo, mitigación verificada, corrección por parche y aceptación de riesgo. Un clic de cierre no constituye evidencia suficiente de reducción de riesgo.

## Metodología de investigación

Se propone un estudio aplicado con desarrollo de artefacto y evaluación de laboratorio. La unidad de análisis será el hallazgo asociado a un activo y a un contexto determinado; un mismo CVE en activos distintos puede generar casos distintos si la selección se declara. Los 25 casos se seleccionarán de forma intencional para representar vulnerabilidades con CVE y configuraciones sin CVE, zonas de red y restricciones de tratamiento. Este muestreo permite estudiar decisiones concretas, pero no estima la frecuencia de vulnerabilidades ni garantiza representatividad de otras organizaciones.

El dataset distinguirá tres procedencias: datos públicos de CVE/CTI, características hipotéticas del escenario Apex y observaciones verificadas de laboratorio. Una condición sintética utilizada en una prueba de software se identificará como tal. Los 100 y 500 registros de carga se generarán para medir rendimiento y no incrementarán artificialmente la muestra de validación de prioridades.

Antes de medir se congelarán el dataset, la rúbrica, los pesos y las reglas de clasificación. Se conservará identificador de versión y hash. Al menos un evaluador ajeno al desarrollo, preferiblemente dos, asignará prioridad a los mismos 25 casos usando contexto y criterios definidos, sin ver las salidas de PRIME. Sus valoraciones iniciales se conservarán antes de cualquier consolidación. Si los mismos casos se usan para ajustar pesos, se declarará la limitación y la evaluación no se presentará como validación externa independiente.

La comparación utilizará una línea base ordenada por CVSS-B y la salida PRIME. Se registrarán acuerdo ordinal y discrepancias por caso. Si hay dos evaluadores, se informará primero su acuerdo; la discrepancia entre ellos no debe ocultarse mediante un promedio automático. El consenso, cuando se utilice, incluirá procedimiento y decisiones originales.

## Operacionalización y evaluación

| Aspecto | Variable o registro | Evaluación prevista |
|---|---|---|
| Entrada técnica | Vector, versión, puntaje CVSS y fuente | Aplicabilidad y correspondencia entre vector y puntaje |
| Contexto | D, E, R, A, Ds y justificaciones | Rúbrica, valores y diferencias entre analistas |
| Amenazas | EPSS, percentil, fecha; KEV y snapshot | Proveniencia y estados disponible/sin dato/error/no aplica |
| Decisión | IRC, banda, política y motivo | Reproducción y comparación independiente |
| Software | Entrada, pasos, salida, estado de prueba | Unitarias, integración, E2E y seguridad |
| Rendimiento | Duración, memoria, tamaño, entorno | Repeticiones y estadísticas descriptivas |
| Control | Flujo y servicio antes/después | Permiso/denegación, compatibilidad y rollback |

Para categorías ordinales, se podrá calcular kappa ponderado con los pesos definidos de antemano; para ordenamientos, una medida de concordancia como Spearman o Kendall, especificando cómo se manejan empates. Se presentará también la matriz de discrepancias y la concordancia exacta `casos coincidentes / casos evaluables`. No se llamará «exactitud» respecto de explotación real a una concordancia de juicio experto. Con 25 casos se interpretarán prudentemente estimaciones e intervalos; si hay un único evaluador, se declarará que no pudo medirse acuerdo entre evaluadores.

Si se compara tiempo manual contra asistido, las tareas deben ser equivalentes: lectura, contextualización, DREAD, CTI, decisión y verificación. El tiempo de ejecutar una función aritmética no es equivalente al tiempo total humano. Los cambios porcentuales solo se calcularán sobre mediciones reales con condiciones registradas.

## Política experimental del algoritmo PRIME

**Esta es una propuesta de ingeniería para probar; los pesos no han sido validados y no pertenecen a una fórmula oficial de FIRST o CISA.** El prototipo inicial utiliza esta versión experimental para hacer concreta la evaluación. No debe denominarse «algoritmo definitivo» hasta revisar E02 y sus resultados.

Sea `C` el puntaje CVSS-B v4.0 declarado y sustentado por un vector; sean D, E, R, A y Ds valoraciones contextuales de 0 a 10. Adoptar 0–10 es un cambio explícito respecto del 1–10 del documento original y permite representar ausencia del atributo; no se mezclarán ambas escalas en un dataset.

```
DREAD_w = 0.30D + 0.25E + 0.20R + 0.15A + 0.10Ds
Base = 0.60C + 0.40DREAD_w
IRC = min(10, Base × (1 + p))  si existe EPSS válido
IRC = Base                   si EPSS no aplica o está incompleto
```

Si el CVE está en un snapshot KEV válido y es aplicable al producto, la banda será «Urgente KEV». Se preservará el puntaje contextual para ordenar dentro de esa banda; no se reemplazará automáticamente por 10. Después se ordenará por IRC descendente e identificador estable. Esta regla evita que la presencia de 17 casos KEV vuelva idénticas todas sus prioridades numéricas. La amplificación EPSS y el tope siguen siendo heurísticos y pueden producir saturación, que deberá medirse.

`p` representa la probabilidad EPSS de explotación del CVE en los próximos 30 días, no de compromiso de un activo concreto. El percentil se guarda como dato descriptivo y no entra a la fórmula. Un dato faltante o un error no se registra como cero: se conserva un estado y se muestra decisión provisional. En un hallazgo sin CVE, EPSS y KEV serán no aplicables y se usará Base. Un KEV desconocido no se trata como ausencia comprobada. [FIRST, EPSS](https://www.first.org/epss/).

Los coeficientes 0.60/0.40 se proponen para impedir que CVSS sea una columna sin efecto. Su aceptación científica requiere sensibilidad y comparación, no solo pruebas de código. Como alternativa a evaluar se puede usar una matriz de decisión por bandas que conserve CVSS, contexto y KEV por separado. No se elegirá la alternativa según cuál confirme mejor las conclusiones ya escritas.

Las categorías numéricas se definirán con intervalos completos: Bajo `[0,4)`, Medio `[4,7)`, Alto `[7,9)` y Crítico `[9,10]`. Se clasifica con precisión completa y se muestra el número redondeado a dos decimales. Un valor cercano al límite puede visualizarse como 4.00 y seguir siendo Bajo: el detalle debe mostrar precisión suficiente y la regla; no es válido introducir huecos entre 3.9 y 4.0.

Los SLAs serán objetivos de tratamiento propuestos para el escenario, sujetos a factibilidad y aprobación académica. Separar urgencia de contención de plazo para corrección definitiva; una contención inmediata no implica que el parche se haya aplicado en 24 horas. No atribuir estos SLAs a FIRST o CISA como obligación del escenario privado.

## Rúbrica inicial para DREAD

Las anclas siguientes son una propuesta para calibrar. Para cada dimensión se documentará un valor entero inicial, su evidencia y el motivo. Los puntos 1–2, 4–6 y 8–9 representan situaciones intermedias justificadas; usar decimales solo cuando se explique la interpolación.

| Dimensión | 0 | 3 | 7 | 10 |
|---|---|---|---|---|
| D Daño | Sin impacto plausible en el alcance | Impacto local recuperable | Interrupción o exposición relevante de proceso crítico | Impacto grave de confidencialidad o continuidad con alcance demostrado |
| E Explotabilidad | No aplicable al producto/condiciones | Requiere privilegios y condiciones poco frecuentes | Herramienta disponible con precondiciones razonables | Ejecución sencilla en condiciones verificadas |
| R Reproducibilidad | No reproducible bajo condiciones registradas | Dependiente de estado raro | Consistente bajo precondiciones definidas | Reproducible de forma estable en ensayos documentados |
| A Usuarios afectados | Ninguno dentro del alcance | Un usuario o función local | Múltiples usuarios de un proceso relevante | Mayoría del alcance o servicio central compartido |
| Ds Descubribilidad | Información inexistente en el alcance evaluado | Requiere conocimiento interno específico | Identificable con enumeración autorizada | Visible fácilmente en el alcance registrado |

«No observado» no siempre equivale a cero. Si no hay evidencia suficiente para E/R/Ds, se debe registrar incertidumbre y revisar, en vez de inferir imposibilidad de ataque. La definición de E/R/Ds conserva sus significados y evita sustituir reproducibilidad por persistencia. Los datos de movimiento lateral pueden contribuir a daño y alcance, pero se explicará su participación para no contarlos repetidamente.

La sensibilidad probará, como mínimo, combinaciones CVSS/DREAD 0.50/0.50, 0.60/0.40 y 0.70/0.30, manteniendo entradas y evaluaciones independientes. Se reportarán cambios de bandas, orden y número de empates/saturaciones. Un análisis de sensibilidad posterior no permite borrar resultados desfavorables.

## Arquitectura y seguridad

La capa Angular presentará hallazgos, prioridades, datos CTI y evidencias. Laravel validará entradas, aplicará autorización, calculará prioridades y conservará historial. MySQL mantendrá datos relacionados de usuarios, hallazgos por activo, lotes, triajes, snapshots y auditoría. La arquitectura final identificará servicios de ejecución, almacenamiento de adjuntos y flujos externos reales, en lugar de limitarse a tres cajas conceptuales.

El acceso utilizará autenticación de sesión para una SPA propia y protección CSRF, con un desafío MFA que no permita consultar datos hasta verificar TOTP. Se documentarán enrolamiento, recuperación, expiración, cierre de sesión, cifrado del secreto y control de intentos. Sanctum puede utilizar sesiones y tokens de API; no debe describirse automáticamente como JWT. [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum).

El CSV se validará por estructura, rangos, codificación y límites. La exportación neutralizará fórmulas en campos no confiables sin atribuir al uso de ORM o Angular una protección universal. Las pruebas de SQLi y XSS conservarán punto de entrada, payload inocuo de ensayo, respuesta y resultado. Las pruebas automatizadas se interpretarán dentro de su alcance.

Se registrarán versiones exactas y archivos de bloqueo. A la fecha de revisión, Laravel 11 y Angular 18 del documento original están fuera de soporte. La implementación inicial se orienta a Laravel 12 y Angular 21; esta decisión deberá figurar en el baremo y las evidencias de versiones. [Laravel](https://laravel.com/docs/11.x/releases); [Angular](https://angular.dev/reference/releases).

## Diseño de validación de laboratorio

Cada control se evaluará con un baseline que demuestre que el servicio existe y funciona. Se aplicará una regla o cambio documentado; se probará acceso desde origen autorizado y no autorizado, tráfico TCP pertinente, rutas y conexiones iniciadas en ambos sentidos. Se conservarán logs del control y prueba funcional de la aplicación. Una prueba fallida de ICMP no confirma denegación SMB, RDP ni detección IPS.

Para Windows Server 2008 R2, la deshabilitación SMBv1 mediante registro requiere reinicio; se diferenciará de una restricción de firewall efectiva antes del reinicio. Si el servicio depende de SMBv1, se evaluarán controles compensatorios compatibles y su limitación sin afirmar que eliminan la vulnerabilidad. [Microsoft, SMB](https://learn.microsoft.com/en-us/windows-server/storage/file-server/troubleshoot/detect-enable-and-disable-smbv1-v2-v3).

Windows LAPS y Microsoft LAPS legado son productos diferentes. Se registrará qué modalidad funciona en qué sistema; una demostración en cliente moderno no prueba compatibilidad en 2008 R2. Rotación y permisos se verificarán sin publicar secretos. [Microsoft, LAPS](https://learn.microsoft.com/en-us/windows-server/identity/laps/laps-overview).

Cada playbook incluirá producto y versión afectados, fuente del fabricante, requisitos, respaldo, cambio, prueba de conectividad, prueba del servicio, criterio de éxito, resultado real, riesgos remanentes y reversión. La reducción de riesgo residual no se asignará por tabla predeterminada: se sustentará en las condiciones reevaluadas. Si la fórmula no refleja cambios de exposición, se declarará esa limitación y se mantendrá el registro de control por separado.

## Estructura del capítulo de resultados

Conservar el capítulo IV como **Plan de validación y resultados obtenidos**, con subsecciones separadas. Para cada objetivo presentar método, entrada, resultado observado, evidencia enlazada y limitación. La matriz TC original debe incluir columnas de resultado real, fecha, versión y referencia de archivo; su estado inicial será Pendiente.

La comparación de prioridades se completará solo después de E03. Las pruebas del prototipo local con SQLite deberán identificarse como tales; no cuentan como validación de MySQL. Las consultas CTI con mocks verifican manejo de respuestas, pero no constituyen consultas reales a FIRST/CISA. Cada reporte distinguirá ambos tipos.

Los resultados deberán incluir fallos y correcciones, no únicamente capturas favorables. El informe de rendimiento presentará registros crudos y estadísticas de repeticiones para 25, 100 y 500 filas. Se separarán duración del cálculo, persistencia, CTI y flujo completo; la complejidad constante de una función por caso no se atribuirá al lote completo.

## Factibilidad económica

El presupuesto es una estimación del proyecto y sus supuestos deben exponerse. El total original de USD 13,736 es aritméticamente consistente con cinco meses de rubros recurrentes y USD 300 de capacitación. Sin embargo, su relación con el cronograma de ocho semanas hasta la defensa requiere ajustar el horizonte o explicar fases adicionales. No se adoptarán precios de infraestructura sin cotización fechada y configuración verificable.

El período de recuperación simple podrá expresarse como `inversión / ahorro neto mensual` solo si dicho ahorro está sustentado o marcado como supuesto. El ROI se calculará como `(beneficios netos del horizonte − inversión) / inversión × 100`, definiendo qué costos se incluyen para evitar doble conteo. Presentar escenarios conservador, central y favorable no sustituye la medición; hace explícita la incertidumbre.

## Conclusiones para la etapa de diseño

El diseño de PRIME establece un proceso de triaje que pretende relacionar severidad técnica, contexto y señales de amenazas con decisiones trazables. La revisión identificó la necesidad de incorporar CVSS explícitamente al cálculo, formalizar la rúbrica DREAD y distinguir datos CTI desconocidos de valores nulos o cero.

La validación prevista permitirá evaluar concordancia con una referencia independiente, corrección funcional del MVP y comportamiento de controles seleccionados del laboratorio. En esta etapa no es posible concluir eliminación de falsos positivos, ahorro de horas de análisis, rendimiento alcanzado ni reducción cuantitativa del riesgo residual.

El alcance de las conclusiones finales dependerá de las pruebas efectivamente ejecutadas y de los resultados conservados. El uso de un escenario ficticio y una muestra intencional de 25 casos deberá reconocerse como límite de generalización.

## Reglas para integrar el texto en el documento fuente

Reemplazar las afirmaciones incompatibles con estos textos; no añadirlos después de conservar resultados contradictorios. Mantener la nomenclatura de objetivos en toda la matriz de trazabilidad. Incorporar tablas extensas como anexos legibles, actualizar títulos de figuras y regenerar índices. Las conclusiones de diseño anteriores deberán sustituirse por conclusiones de resultados únicamente cuando exista evidencia real; si un objetivo no se alcanza, indicarlo con su causa y consecuencias.
