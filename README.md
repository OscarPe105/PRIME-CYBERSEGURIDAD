# PRIME prototipo funcional 0.1

Primera implementación realizada a partir de la revisión del proyecto teórico. Defensa prevista para el 1 de diciembre de 2026. La política de triaje es **experimental**, y los 25 casos de demostración son sintéticos. No se presentan como hallazgos de pentesting ni como validación de riesgo.

## Abrir en este equipo

El prototipo se sirve en **http://127.0.0.1:8000** mientras el proceso local está activo. La carpeta `frontend` contiene el código Angular; Laravel sirve una copia compilada bajo el mismo origen.

1. En el equipo original, abre `ACCESO_LOCAL.txt` para consultar las cuentas locales de demostración. Ese archivo contiene contraseñas de este entorno y se excluye del repositorio y del ZIP. Si clonaste el repositorio, crea tus propias cuentas según las instrucciones de instalación.
2. Ingresa con correo y contraseña. En el primer acceso, añade la clave mostrada en una aplicación autenticadora TOTP de seis dígitos y 30 segundos; confirma el código.
3. Guarda los códigos de recuperación de un solo uso que entrega el enrolamiento.
4. Explora los 25 casos sintéticos, filtros, detalle, historial, consulta CTI y exportación. Si el espacio está vacío, descarga el CSV de demostración e impórtalo como Analista o Administrador. No importes el mismo archivo dos veces: se rechazan IDs repetidos.
5. Para el tratamiento, propón como Analista, adjunta evidencia y revisa con otro usuario Administrador. Un cierre administrativo no verifica automáticamente el control ni reduce riesgo residual.

Si el servicio dejó de funcionar, ejecuta `Iniciar-PRIME.ps1` desde PowerShell. Usa el PHP portátil preparado en la carpeta de trabajo de este equipo si sigue disponible, o PHP del sistema. No requiere abrir una ventana de terminal visible para mantener el servicio. Si el puerto está ocupado, comprueba que corresponde a PRIME antes de usarlo.

## Funciones implementadas

Login con contraseña y desafío MFA; enrolamiento TOTP; códigos de recuperación con hashes; rechazo de reutilización TOTP; sesión provisional de cinco minutos sin acceso a datos; cierre de sesión. Tres roles con permisos en backend. CSV UTF-8 de máximo 2 MB y 500 filas, validación de estructura, rangos, duplicados y prefijos de fórmula; importación transaccional y original privado con hash.

Motor con CVSS explícito, cinco dimensiones DREAD y política fija versionada. EPSS y KEV consultables por CVE, conservación de respuesta/hash/fechas, catálogo KEV cacheado 24 horas y estados pending/error/missing/no aplica. Historial de revisiones preservado. Tabla, búsqueda, filtros, detalle, propuesta, adjunto privado, revisión por otra persona, auditoría y exportación CSV.

## Límites actuales

- Persistencia ejecutada con **SQLite local**. Hay configuración y migraciones para MySQL, pero MySQL todavía no se ha ejecutado ni validado.
- Fórmula `Base = 0.60 CVSS + 0.40 DREAD_w`; con EPSS válido, `IRC = min(10, Base × (1 + EPSS))`. KEV crea banda urgente sin reemplazar puntaje. No es una fórmula oficial ni validada científicamente.
- El parser admite vectores CVSS v4.0 Base en orden canónico; verifica estructura y rango, pero no recalcula el puntaje desde el vector. Debe añadirse un calculador verificado antes de validar E01/E02.
- El dataset de ejemplo usa productos y activos simulados, CVE públicos como identificadores de ensayo y un vector/puntaje declarado de ejemplo. Requiere sustitución y revisión de aplicabilidad para investigación. Los valores CTI no se inventan ni se precargan con cifras del PDF.
- No hay pantalla de usuarios/pesos, proceso asíncrono, conector a escáneres, generación de informe PDF, riesgo residual calculado, SIEM, IPS, LAPS, backup probado ni laboratorio Windows implementado.
- Es un solo espacio de trabajo. No hay separación multiempresa ni políticas de propiedad por proyecto.
- Angular se compila inicialmente con JIT; el bundle es grande y el optimizador avisa que supera 500 kB. La migración a compilación AOT y partición del código es pendiente. El build compila, pero no se presenta como optimización final de producción.
- El entorno actual usa HTTP localhost. HTTPS, cookies Secure y configuración de publicación deben verificarse en el despliegue definitivo.

## Instalar desde el repositorio o ZIP en otro entorno

Requisitos: PHP 8.4 con `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `zip`, XML y DOM; Composer 2; Node compatible con Angular 21 y Vite 7; pnpm; MySQL preparado si se va a validar el stack definitivo. Los lockfiles fijan las versiones exactas instaladas. Las credenciales y bases de datos del entorno local se excluyen del ZIP.

Para una ejecución inicial SQLite, desde `backend`:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File database/database.sqlite
php artisan migrate
php artisan prime:user administrador@prime.test administrador
php artisan prime:user analista@prime.test analista
php artisan prime:user auditor@prime.test auditor
php artisan serve --host=127.0.0.1 --port=8000
```

Cada comando `prime:user` muestra una contraseña aleatoria una sola vez. Guardarla privadamente. La aplicación exige configurar TOTP antes de acceder a los datos. Si el archivo SQLite ya existe, no lo recrees ni sobrescribas. `.env.example` es la base de Laravel: ajusta `APP_NAME=PRIME`, `APP_DEBUG=false` y `SESSION_LIFETIME=30` para coincidir con el entorno revisado.

Para MySQL, usar `.env.mysql.example` como base de `.env`, crear una base `prime` y una cuenta con permisos limitados a esa base, fijar contraseña y ejecutar `key:generate` y `migrate`. No reutilizar la BD SQLite como prueba de MySQL. No se incluye contraseña fija ni se crean usuarios de MySQL automáticamente. En un despliegue HTTPS, definir `SESSION_SECURE_COOKIE=true`.

La interfaz compilada ya está en `backend/public/prime.html` y `backend/public/assets`. Para modificar Angular:

```powershell
cd frontend
pnpm install --frozen-lockfile
pnpm check
pnpm build
Copy-Item dist/index.html ../backend/public/prime.html -Force
Copy-Item dist/assets ../backend/public -Recurse -Force
Copy-Item public/ejemplo25.csv ../backend/public/ejemplo25.csv -Force
```

Para desarrollo, `pnpm dev` inicia el frontend local en puerto 5173 y proxy hacia Laravel en 8000. La ejecución compartida por Laravel en 8000 evita la necesidad de mantener ambos servidores para la demostración.

## Repetir las pruebas

Desde `backend`, `php artisan test --log-junit ../evidencias-iniciales/phpunit.xml`. Las pruebas usan una BD SQLite de ensayo separada según `phpunit.xml`, CTI simulada y datos sintéticos. `RefreshDatabase` solo debe usarse en el entorno de pruebas. No configurar pruebas contra una base con información importante.

El paquete `evidencias-iniciales` contiene el reporte real de PHPUnit y registros/capturas del ensayo de navegador. El ensayo navegó con Chrome headless usando Playwright y cuentas QA locales separadas. Las pruebas de navegador aquí registradas no equivalen a pruebas en Windows Server, una evaluación externa ni rendimiento de producción.

## Próximo avance hasta la defensa

Completar las 35 evidencias según el plan adjunto. Primero validar vector/puntaje, rúbrica, política y dataset; luego ampliar pruebas MFA/CSV/RBAC, ejecutar MySQL, CTI real, seguridad y backups. En paralelo preparar AD/firewall/legacy y evaluadores. Congelar la versión probada antes del ensayo final del 23 al 29 de noviembre.
