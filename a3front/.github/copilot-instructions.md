### Aurora3 (a3front) — quick AI contributor guide

This repository is a Vue 3 application scaffolded with Vite. These notes focus on patterns and conventions that help an AI agent make high-quality, low-risk changes.

- Entry points & runtime
  - App bootstrap: `src/main.js` (Pinia, Vue Router, Antd, Vue Query and global env set on window).
  - Main layout: `src/App.vue` (global i18n loading via axios + import.meta.glob for local JSONs).
  - Vite aliases are defined in `vite.config.ts` (use `@`, `@views`, `@routes`, `@service`, etc.).

- Build / dev commands (from `package.json`)
  - dev: `npm run dev` (vite HMR)
  - local: `npm run local` (vite with custom host)
  - build: `npm run build` / `npm run build-dev`
  - preview: `npm run preview`
  - lint/format: `npm run lint`, `npm run format`

- API / services conventions
  - Services commonly import a module named `negotiationsApi` (e.g. `import negotiationsApi from '@/modules/negotiations/negotiationsApi.ts'`). If the file is missing, follow the pattern used in `src/services/*` — requests use `negotiationsApi.get/post(...)` and `window.API` + path.
  - Many service utilities live under `src/services/` and `src/modules/negotiations/`.

- State, data fetching & permissions
  - State: Pinia stores in `src/stores` and `src/modules/**/store`.
  - Server-state querying uses TanStack Vue Query (`@tanstack/vue-query`) — see `src/modules/negotiations/.../optimized-queries/*.query.ts` for examples.
  - Authorization: CASL is used (`src/services/casl/ability` + `useCaslAbility`) and route middleware checks (`src/middleware/CheckPermission.js`).

- Router & middleware
  - Router defined in `src/router/index.js`. Routes carry `meta.middleware` (e.g. `auth`, `isLogin`) — use `nextFactory` pattern to chain middleware.
  - Typical middleware: `src/middleware/auth.js`, `src/middleware/isLogin.js`.

- Environment & globals
  - Runtime config uses Vite env vars (prefix VITE_). Many are copied to `window.*` in `src/main.js` (e.g. `window.API_GATEWAY_BACKEND`, `window.API_NEGOTIATIONS`, `window.TOKEN_KEY`). Use import.meta.env for build-time access.

- Patterns & gotchas to follow when editing
  - Use existing alias imports (e.g. `@/...`) rather than relative deep paths.
  - Use Pinia helpers for stores (defineStore) and prefer existing store methods over duplicating logic.
  - For API changes, prefer updating service wrapper files under `src/services` or `src/modules/*/service/*.js` rather than inlining axios calls widely.
  - i18n is partially dynamic: `App.vue` merges translations from `src/lang/**` and also fetches remote translations. Preserve this flow when touching localization.
  - Many routes use dynamic imports for pages — keep lazy-loading where present to avoid increasing bundle size.

- Tests / CI
  - There are no visible test runner configs; focus on running lint and manual local runs. Use `npm run lint` and `npm run dev` to validate changes locally.

- Files to reference when making changes
  - Bootstrapping & globals: `src/main.js`, `src/App.vue`
  - Router & middleware: `src/router/index.js`, `src/middleware/*.js`
  - Services: `src/services/**`, `src/modules/negotiations/**/service/**`
  - Stores: `src/stores/*`, `src/modules/**/store/*`
  - Queries: `src/modules/**/optimized-queries/*.query.ts` (Vue Query examples)

- When in doubt
  - Run the dev server and reproduce the UI flow before changing large pieces.
  - For API endpoints, inspect nearby service files (example: `src/services/negotiations/serviceSheets/product.js`) to understand URL composition (they often use `window.API + '<path>'`).

If any of these notes are unclear or you want more examples (e.g., a typical Pinia store + unit test scaffold), tell me which area and I'll expand with concrete code snippets and files to edit.
