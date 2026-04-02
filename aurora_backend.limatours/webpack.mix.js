// const path = require('path')
const mix = require('laravel-mix')
// const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')

// Extend Mix with the "i18n" method, that loads the vue-i18n-loader
mix.extend('i18n',
  new class {
    webpackRules () {
      return [
        {
          resourceQuery: /blockType=i18n/,
          type: 'javascript/auto',
          loader: '@kazupon/vue-i18n-loader'
        }]
    }
  }()
)

mix.i18n()
  .js('resources/js/app.js', 'public/js')
  .stylus('resources/stylus/app.styl', 'public/css')
  .sass('resources/sass/vendor.scss', 'public/css')

  .sourceMaps()
  .disableNotifications()

mix.copyDirectory('resources/images', 'public/images')

if (mix.inProduction()) {
  mix.version()

  mix.extract([
    'vue',
    'vform',
    'axios',
    'vuex',
    'jquery',
    'popper.js',
    'vue-i18n',
    'vue-meta',
    'js-cookie',
    'bootstrap',
    'bootstrap-vue',
    'moment',
    'vue-router',
    'sweetalert2',
    'vuex-router-sync',
    '@fortawesome/fontawesome',
    '@fortawesome/vue-fontawesome'
  ])
} else {
  mix.browserSync({
    proxy: 'localhost:8000',
    files: ['public/js/**/*.js', 'public/css/**/*.css']
  })

}
