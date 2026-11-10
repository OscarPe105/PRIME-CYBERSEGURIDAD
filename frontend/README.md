# PRIME: frontend

Interfaz web de PRIME (MVP de triaje de vulnerabilidades). Es una SPA en **Angular 21** que consume la API de Laravel (`../backend`).

## Stack y librerías

| Área | Tecnología | Versión |
| --- | --- | --- |
| Framework | Angular (`core`, `common`, `forms`, `platform-browser`, `compiler`) | ^21.0.0 |
| Reactividad | RxJS | ^7.8.2 |
| Zona | zone.js (el arranque usa `provideZonelessChangeDetection`) | ~0.15.1 |
| Lenguaje | TypeScript | ~5.9.3 |
| Bundler / dev server | Vite | ^7.2.2 |
| Tests | Vitest + jsdom | 3.2.4 / 26.1.0 |
| Formato | Prettier | 3.6.2 |
| Gestor de paquetes | pnpm | lockfile `pnpm-lock.yaml` |

Añadido en la migración de UI:

| Área | Tecnología |
| --- | --- |
| Estilos | Tailwind CSS 4 (`@tailwindcss/vite`), sin preflight mientras se migra el CSS antiguo |
| Componentes headless | `@spartan-ng/brain` + `@angular/cdk` |
| Utilidades de clases | `clsx`, `tailwind-merge`, `class-variance-authority` (`src/app/ui/cn.ts`) |
| Compilación | `@analogjs/vite-plugin-angular` + `@angular/compiler-cli`: **AOT** con Vite y Vitest |

Notas importantes:

- **No usa Angular CLI** (no hay `angular.json`). Vite compila con el plugin de Analog en modo AOT (`tsconfig.app.json` para la app, `tsconfig.spec.json` para los tests). El bundle bajó de ~853 kB a ~524 kB.
- Las plantillas usan `templateUrl` (AOT no admite `?raw`).
- Los **tokens de diseño** viven en `src/styles/tokens.css` (variables `--ui-*` y `@theme`). Úsalos en componentes nuevos con clases como `bg-primary` o `text-muted-foreground`.
- Los componentes antiguos siguen con su CSS en `src/styles/`. Migrar pantalla por pantalla.
- **Componentes estilo shadcn (helm):** el CLI de Spartan espera un workspace Angular/Nx, así que se escriben a mano sobre `@spartan-ng/brain` en `src/app/ui/`.
- **Componentes standalone**, **sin router** (navegación en `app.component.ts`), **sin gestor de estado**; HTTP con `fetch` en `PrimeApiService`.

> Reglas de estilo obligatorias para la interfaz: [UI-GUIDELINES.md](UI-GUIDELINES.md).

## Estructura

```
frontend/
├── index.html
├── vite.config.ts          # puerto 5173, proxy /api -> http://127.0.0.1:8000
├── vitest.config.ts        # jsdom, setup en tests/setup.ts
├── public/                 # ejemplo25.csv, prime-logo.svg
├── scripts/publish-build.mjs   # copia el build a backend/public
├── tests/                  # app.test.ts, prime-api.test.ts, setup.ts
└── src/
    ├── main.ts             # bootstrap de PrimeApp
    ├── style.css           # importa los CSS de src/styles
    ├── styles/             # base, shell, login, recovery, findings, detail, responsive
    └── app/
        ├── app.component.{ts,html}     # shell, sesión y vistas
        ├── components/                 # login, recovery, findings, finding-detail, activity
        ├── services/prime-api.service.ts   # cliente de la API (fetch, CSRF, ApiError)
        ├── models/prime.ts             # tipos e interfaces, canWrite()
        └── utils/                      # clipboard.ts, format.ts
```

## Comandos

Instalar dependencias (aún no hay `node_modules`):

```bash
pnpm install
```

Si pnpm no existe: `corepack enable pnpm`.

Desarrollo con recarga en caliente, en http://127.0.0.1:5173. Requiere el backend en el puerto 8000:

```bash
pnpm dev
```

Otros comandos:

```bash
pnpm check          # tsc --noEmit
pnpm test           # vitest run
pnpm format         # prettier --write
pnpm build          # vite build -> dist/
pnpm build:backend  # check + build + copia a backend/public
```

## Flujo de trabajo para cambios

1. Levantar el backend: `cd ../backend && php artisan serve --host=127.0.0.1 --port=8000`.
2. En `frontend`: `pnpm install` y `pnpm dev`.
3. Editar en `src/app/components` (lógica y plantilla) y `src/styles` (CSS).
4. Validar con `pnpm check && pnpm test`.
5. Para que Laravel sirva los cambios en `:8000/prime.html`, ejecutar `pnpm build:backend`. Esto sobrescribe `backend/public/prime.html` y `backend/public/assets`.

## Pendientes conocidos

- Partición del bundle (aún ~524 kB).
- No hay pantalla de usuarios ni de pesos de la política.
- HTTPS y cookies `Secure` se verifican en el despliegue definitivo.
