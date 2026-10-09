# PRIME: entrega inicial y estado verificable

Defensa: **1 de diciembre de 2026**. Punto de partida confirmado por el estudiante: documentación teórica, sin implementación previa. Esta entrega introduce una primera implementación local; no acredita la ejecución previa descrita en el PDF.

## Documentos entregados

1. **01_Diagnostico_PRIME.md**: 26 observaciones con referencias al PDF y al documento de requisitos. Incluye inconsistencias del algoritmo, resultados sin respaldo, CTI inaplicable, laboratorio y presupuesto.
2. **02_Plan_35_evidencias.md**: las 35 evidencias del Word, con acciones, dependencias, responsables propuestos, criterios de aceptación y fechas hasta el 30 de noviembre. Es planificación; no una declaración de cumplimiento.
3. **03_Textos_academicos_corregidos.md**: textos de sustitución y ampliación para objetivos, problema, metodología, diseño, validación y conclusiones de diseño. Deben integrarse y ajustar referencias/índices en el documento original. No se ha entregado un PDF o DOCX final maquetado.
4. **04_Especificacion_y_validacion_MVP.md**: requisitos, arquitectura, datos, permisos y protocolo de pruebas.

## Prototipo disponible

Abrir **http://127.0.0.1:8000** en este equipo mientras el servicio esté activo. Consultar **prime-mvp/ACCESO_LOCAL.txt** para cuentas locales nuevas de Administrador, Analista y Auditor. Enrolar cada cuenta en una aplicación autenticadora al primer acceso. Las contraseñas locales se excluyen del ZIP.

El código es Laravel 12 / Angular 21. Se ejecutó con **SQLite**; MySQL tiene configuración y migraciones, pero falta su ejecución y validación. El entorno HTTP es únicamente local. La versión incluye MFA, roles en backend, importación CSV, cálculo experimental con CVSS/DREAD/EPSS/KEV, estados de CTI, historial, adjuntos privados, propuesta/revisión por usuarios diferentes, auditoría y exportación.

Los 25 registros visibles y su evidencia adjunta son **sintéticos**. Los CVE son identificadores de ensayo y requieren comprobar correspondencia con productos antes de usarlos en el estudio. Las cifras CTI del PDF no se cargaron como hechos. El cierre administrativo realizado en QA no demuestra mitigación de infraestructura.

## Verificación realizada

- **14 pruebas PHPUnit, 56 aserciones, 0 errores y 0 fallos**: motor, límites, TOTP, MFA, RBAC, CSV, historial y flujo de evidencias; CTI simulada para pruebas deterministas. Reporte original: `prime-mvp/evidencias-iniciales/phpunit.xml`.
- **Angular**: comprobación de tipos realizada y compilación completada. El build advierte bundle superior a 500 kB; falta optimización/AOT para producción.
- **Navegador**: ocho pasos completados en Chrome headless: login/enrolamiento, rechazo RBAC, importación de 25 casos, propuesta/adjunto, logout y rechazo 401, revisión por otro administrador y auditoría.
- El intento inicial falló al localizar Hallazgos en móvil. Se corrigieron las etiquetas accesibles y el botón de cierre móvil. La continuación en el navegador de Codex verificó el acceso QA con TOTP, Actividad/Hallazgos a 390 × 844, captura y cierre de sesión. Se conservan tanto el fallo inicial como la continuación en los registros JSON. No se volvió a ejecutar el recorrido completo tras la última corrección. La tabla requiere desplazamiento horizontal en móvil; no se revisaron todas las acciones móviles.
- Un timeout breve al esperar el logout en la continuación terminó con la pantalla de acceso comprobada posteriormente. Es un límite de esta comprobación, no un resultado de rendimiento medido.

Las capturas prueban un recorrido de software con datos de demostración. No reemplazan las 35 evidencias, pruebas de red, mediciones comparativas ni evaluación independiente.

## Pendientes prioritarios

1. Congelar la política experimental y construir rúbrica DREAD con evaluadores independientes; justificar pesos y hacer sensibilidad. La fórmula implementada no es estándar oficial ni ha sido validada científicamente.
2. Recalcular CVSS desde vectores con un calculador verificado y construir los 25 casos académicos trazables. El importador actual valida sintaxis/rango, pero no calcula el score del vector.
3. Ejecutar MySQL, verificar consultas CTI reales, ampliar seguridad/sesiones/cookies y documentar fallos/actualización de feeds.
4. Construir el laboratorio Zero Trust con rutas y reglas comprobables; probar AD/MFA/LAPS según compatibilidad, servicios permitidos/bloqueados y controles en Legacy.
5. Ejecutar evaluación comparativa, restauración de backups, rendimiento y revisión externa; recién entonces redactar resultados y conclusiones empíricas.

El ZIP contiene fuentes, dependencias fijadas en lockfiles, frontend compilado, pruebas y evidencias iniciales. Excluye dependencias instaladas, claves, cuentas, BD local y archivos privados de ejecución. Para reproducir en otro equipo, seguir `prime-mvp/README.md`; la carpeta portátil de trabajo de este equipo no forma parte del ZIP.
