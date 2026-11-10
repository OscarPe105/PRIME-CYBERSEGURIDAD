# Reglas de interfaz de PRIME

Toda interfaz nueva o modificada usa **estrictamente** la librería de componentes del proyecto: **Spartan UI** (`@spartan-ng/brain`, comportamiento headless) con el patrón de estilos **shadcn/helm** sobre **Tailwind CSS 4**. No se inventan estilos propios.

## Reglas

1. **Componentes interactivos con Spartan brain.** Diálogos, menús, popovers, tooltips y similares salen de `@spartan-ng/brain/<componente>`. No se escriben modales, dropdowns ni focus-traps a mano.
2. **Estilo solo con utilidades de Tailwind** y los tokens de `src/styles/tokens.css`: `bg-background`, `text-foreground`, `text-muted-foreground`, `border-border`, `bg-primary`, `text-primary-foreground`, `bg-destructive`, `bg-muted`, `rounded-ui`.
3. **Sin colores fijos.** Prohibido `#hex`, `rgb()` o `style="color: …"` en componentes nuevos. Los colores salen de los tokens, que a su vez salen de la paleta activa (`src/styles/themes.css`). Así todo respeta el selector de paletas de Configuración.
4. **Botones con `hlmBtn`** (`src/app/ui/button.ts`): variantes `default`, `outline`, `ghost`, `destructive`; tamaños `default` y `sm`. Las acciones destructivas usan `variant="destructive"`.
5. **Clases condicionales con `cn()`** (`src/app/ui/cn.ts`) y variantes con `class-variance-authority`. No se concatenan strings de clases a mano.
6. **Confirmaciones con `prime-confirm-dialog`** (`src/app/ui/confirm-dialog.component.ts`, basado en `brn-alert-dialog`). Toda acción que modifica o archiva datos pide confirmación y explica qué pasará.
7. **Toda tabla lleva paginación, con un máximo de 10 registros por página.** Sin excepciones: se usa `paginate()` y `PAGE_SIZE` (`src/app/utils/paginate.ts`) y el componente `<prime-pagination>` bajo la tabla. Los filtros o búsquedas devuelven a la página 1. Nunca se renderizan todas las filas de golpe.
8. **Toda tabla lleva una primera columna `#` con el número de cada registro (1, 2, 3…).** La numeración continúa entre páginas (la página 2 empieza en 11); se calcula con `pageStart()` de `src/app/utils/paginate.ts` más el índice de la fila (`start + $index`).
9. **Tablas** (aspecto): contenedor `rounded-ui border border-border bg-background`, cabecera `bg-muted text-xs uppercase tracking-wide text-muted-foreground`, filas con `divide-y divide-border`. Ver `history.component.html`.
10. **Accesibilidad**: roles y `aria-*` correctos, foco visible (`focus-visible:ring-2`), texto alternativo, contraste suficiente, `sr-only` para etiquetas ocultas.
11. **No se añade CSS a `src/styles/*.css`** (legado, en la capa `legacy`). Solo se toca al migrar una pantalla antigua a Tailwind. Evita etiquetas `dl`, `dt`, `dd`, `h2`, `h3`, `ul`, `li` globales del legado cuando importe el aspecto: usa clases de Tailwind explícitas.
12. **Un componente nuevo de UI reutilizable va en `src/app/ui/`**, con el mismo patrón (directiva o componente standalone + `cn`).

## Componentes disponibles

| Necesidad | Usar |
| --- | --- |
| Botón | `button[hlmBtn]` / `a[hlmBtn]` |
| Confirmación (modal) | `<prime-confirm-dialog>` |
| Mezclar clases | `cn()` |
| Paginación de tablas | `<prime-pagination>` + `paginate()` (máx. 10 por página) |
| Numeración de filas | columna `#` con `pageStart()` + `$index` |

## Lista de verificación antes de entregar

- [ ] ¿Los colores son tokens y no hex?
- [ ] ¿Se ve bien en las 5 paletas de Configuración?
- [ ] ¿Los botones usan `hlmBtn`?
- [ ] ¿Las acciones que cambian datos piden confirmación?
- [ ] ¿Toda tabla nueva está paginada (máx. 10 por página)?
- [ ] ¿Toda tabla nueva tiene la columna `#` numerada?
- [ ] `pnpm check`, `pnpm test` y `pnpm build` pasan.
