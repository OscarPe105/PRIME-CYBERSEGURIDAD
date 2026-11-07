# Evidencias generadas el 2026-11-07

| ID | Evidencia | Resultado | Archivo |
|---|---|---|---|
| E05, E06, E08, E09, E11 | PHPUnit completo | 29 pruebas, 0 fallos (28 existentes + rendimiento) | `E11_phpunit_2026-11-07.xml` |
| E10 (parcial) | `composer audit` | Antes: 8 avisos en 5 paquetes Symfony. Tras `composer update "symfony/*"`: **0 avisos**. Falta SAST/DAST y auditoría del frontend. | `E10_composer_audit_ANTES.txt`, `E10_composer_audit.txt` |
| E32 (parcial) | SBOM backend CycloneDX 1.5 | 111 componentes. Frontend pendiente (no hay pnpm ni node_modules). | `E32_sbom_backend.cdx.json` |
| E14, E15 | Rendimiento importación y listado | 25 filas: 67 ms / 3 ms; 100: 52 ms / 6 ms; 500: 234 ms / 30 ms (SQLite en memoria, local) | `E14_E15_rendimiento.json` |
| E28, E29 | Backup y restauración SQLite | Procedimiento OK, integridad OK, conteos iguales. Base sin hallazgos importados. | `E28_E29_backup_restauracion.txt` |

Limitaciones: rendimiento medido en memoria y en un solo equipo, no es de producción; SAST/DAST, MySQL, CTI real, E2E y red/legacy siguen pendientes.
