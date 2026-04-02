// postcss.config.js
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
  overrides: [
    {
      files: ['*.js', '*.cjs'],
      parserOptions: {
        sourceType: 'script',
      },
    },
    {
      files: ['*.vue'],
      rules: {
        'no-undef': 'off',
      },
    },
  ],
};
