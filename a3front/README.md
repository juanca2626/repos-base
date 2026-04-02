<p align="center"><img src="http://www.limatours.com.pe/assets/online/img/logo.png" alt="Lima Tours Logo"></p>

# Aurora 3 Repository

Este proyecto es una aplicación Vue 3 utilizando Vite como herramienta de construcción.

## Configuración del IDE Recomendada

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (desactivar Vetur) + [TypeScript Vue Plugin (Volar)](https://marketplace.visualstudio.com/items?itemName=Vue.vscode-typescript-vue-plugin).

## Configuración Personalizada

Consulta la [Referencia de Configuración de Vite](https://vitejs.dev/config/) para más detalles.

## Configuración del Proyecto

```sh
npm install
```

### Compilación y Recarga en Caliente para Desarrollo

```sh
npm run dev
```

Para desarrollo local con un host específico:

```sh
npm run local
```

### Compilación y Minificación para Producción

```sh
npm run build
```

Para compilar en modo desarrollo:

```sh
npm run build-dev
```

### Vista Previa de Producción

```sh
npm run preview
```

### Linting con [ESLint](https://eslint.org/)

```sh
npm run lint
```

### Formateo de Código con Prettier

Para asegurar un formateo de código consistente, este proyecto utiliza Prettier. La configuración se especifica en el archivo `prettier.config.js`.

```sh
npm run format
```

### Husky y Lint-Staged

Este proyecto utiliza Husky para manejar git hooks y Lint-Staged para ejecutar linters en archivos staged. La configuración se encuentra en el `package.json`.

## Dependencias Principales

- Vue 3
- Vue Router
- Pinia (para manejo de estado)
- Ant Design Vue
- Axios
- Vue I18n (para internacionalización)
- VeeValidate (para validación de formularios)
- Luxon (para manejo de fechas y horas)
- XLSX (para manejo de archivos Excel)

## Herramientas de Desarrollo

- TypeScript
- Vite
- ESLint
- Prettier
- Sass
- Tailwind CSS

## Scripts Disponibles

- `npm run dev`: Inicia el servidor de desarrollo.
- `npm run local`: Inicia el servidor de desarrollo con un host específico.
- `npm run build`: Construye la aplicación para producción.
- `npm run build-dev`: Construye la aplicación en modo desarrollo.
- `npm run preview`: Vista previa de la versión de producción.
- `npm run lint`: Ejecuta el linter.
- `npm run format`: Formatea el código con Prettier.

## Recursos Adicionales

- [Documentación de Vue 3](https://v3.vuejs.org/)
- [Fundamentos de Vue 3](https://v3.vuejs.org/guide/essentials/getting-started.html)
- [Documentación de Vite](https://vitejs.dev/guide/)
- [API de Plugins CLI de Vite](https://vitejs.dev/api/plugin-cli/)
- [Guía de Usuario de TypeScript](https://www.typescriptlang.org/docs/handbook/intro.html)
- [Documentación de Prettier](https://prettier.io/docs/en/install)
- [Documentación de Husky](https://typicode.github.io/husky/get-started.html)

