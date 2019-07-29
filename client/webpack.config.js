const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const VuetifyLoaderPlugin = require('vuetify-loader/lib/plugin')
const fs = require('fs');
const _ = require('lodash');
const read = require('fs-readdir-recursive');


let files = read('./assets/js/page');
let filesBundle = read('./src/MobicoopBundle/Resources/assets/js/page');

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  .addEntry('app', './src/MobicoopBundle/Resources/assets/js/app.js')
  .splitEntryChunks()
  // .cleanupOutputBeforeBuild()
  .enableVersioning(Encore.isProduction())
  // enables Sass/SCSS support
  .enableVueLoader()
  // .enableSassLoader(options => {
  //   options.implementation = require('sass')
  //   options.fiber = require('fibers')
  // }) // ☣️This is the way encore works but vuetify show warning error about order mini-css-....
  .addPlugin(new VuetifyLoaderPlugin())
  .addLoader({
    test: /\.s(c|a)ss$/,
    use: [
      'vue-style-loader',
      'css-loader',
      {
        loader: 'sass-loader',
        options: {
          implementation: require('sass'),
          fiber: require('fibers')
        }
      }
    ]
  })
  .setManifestKeyPrefix('/build')
  .enablePostCssLoader()

// for production we do not add some plugin & loader
if (!Encore.isProduction()) {
  Encore.addLoader({
    test: /\.(js|vue)$/,
    enforce: 'pre',
    loader: 'eslint-loader',
    exclude: ['/node_modules', '/vendor', '/public'],
    options: {
      fix: true
    }
  })
    .addPlugin(new StyleLintPlugin({
      failOnWarning: false,
      failOnError: false,
      testing: false,
      fix: true,
      emitErrors: false,
      syntax: 'scss'
    }))
    .enableSourceMaps(!Encore.isProduction())
    .enableBuildNotifications()
    .configureBabel(function (babelConfig) {
      // add additional presets
      babelConfig.plugins.push('transform-class-properties');
      // babelConfig.presets.push('stage-3');
      // This will add compatibility for old nav
    })
}

// Add base assets
for (let file of files) {
  Encore.addEntry(file.split('.js')[0], `./assets/js/page/${file}`)
}

// Add bundle assets
for (let file of filesBundle) {
  Encore.addEntry(`bundle_${file.split('.js')[0]}`, `./src/MobicoopBundle/Resources/assets/js/page/${file}`)
}

let encoreConfig = Encore.getWebpackConfig();
encoreConfig.watchOptions = {
  aggregateTimeout: 500,
  poll: 1000
}


module.exports = [encoreConfig];