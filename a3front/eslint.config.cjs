// eslint.config.cjs
const globals = require('globals');

module.exports = [
  {
    files: ['**/*.{js,ts,vue}'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      parser: require('vue-eslint-parser'), // Usa el parser de Vue para analizar correctamente los archivos .vue
      parserOptions: {
        parser: require('@typescript-eslint/parser'),
        ecmaFeatures: {
          jsx: true,
        },
      },
      globals: {
        ...globals.node,
        ...globals.browser,
      },
    },
    plugins: {
      '@typescript-eslint': require('@typescript-eslint/eslint-plugin'),
      vue: require('eslint-plugin-vue'),
      prettier: require('eslint-plugin-prettier'),
    },
    rules: {
      // Vue rules
      'vue/no-multiple-template-root': 'off',
      'vue/script-setup-uses-vars': 'error',

      // TypeScript rules
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],

      // Prettier rules
      // 'prettier/prettier': 'error',
      'prettier/prettier': ['error', { endOfLine: 'auto' }],

      // General rules
      'no-console': process.env.VITE_APP_ENV === 'production' ? 'warn' : 'off',
      'no-debugger': process.env.VITE_APP_ENV === 'production' ? 'warn' : 'off',
      'no-undef': 'off', // Consolidated from the second block
    },
    settings: {
      'import/resolver': {
        typescript: {},
      },
    },
    ignores: [
      'node_modules/',
      'dist/',
      'public/',
      'src/quotes/',
      'src/views/negotiations/',
      'src/views/operations/',
      'postcss.config.js',
    ],
  },
  {
    files: ['*.vue'],
    languageOptions: {
      parser: require('vue-eslint-parser'), // Asegúrate de utilizar el parser Vue para archivos .vue
      parserOptions: {
        parser: require('@typescript-eslint/parser'),
        ecmaVersion: 2020,
        sourceType: 'module',
      },
    },
    rules: {
      'no-undef': 'off',
    },
  },
];
