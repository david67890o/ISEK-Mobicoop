const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const path = require('path');
const read = require('fs-readdir-recursive');
const _ = require('lodash');
// ⚙️ UNCOMMENT below if you are using a client platform  ⚙️ //
// const fs = require('fs');

let files = read('./assets/js/page');
let filesBundle = read('./src/MobicoopBundle/Resources/assets/js/page');
// ⚙️ UNCOMMENT below if you are using a client platform  ⚙️ //
// let bundleRealPath = fs.realpathSync(__dirname + '/src/MobicoopBundle');
// let bundleNodeModules = path.resolve(bundleRealPath + '../../../node_modules');
// let bundleVendor = path.resolve(bundleRealPath + '../../../vendor');
// let bundlePublic = path.resolve(bundleRealPath + '../../../public');

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('bundle_app', './src/MobicoopBundle/Resources/assets/js/app.js')
  // ⚙️ UNCOMMENT below if you are client platform ⚙️ //
  // .addEntry('app', './assets/js/app.js')  
  .splitEntryChunks()
  .enableVersioning(Encore.isProduction())
  .enableVueLoader()
  .addLoader({
    test: /\.s(c|a)ss$/,
    use: [
      'vue-style-loader',
      // ⚙️ COMMENT below if you are using a client platform  ⚙️ //
      'css-loader',
      // ⚙️ UNCOMMENT below if you are using a client platform  ⚙️ //
      // {
      //   loader: 'css-loader',
      //   options: {
      //     url: false
      //   }
      // },
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

// for Dev we do not add some plugin & loader
if (!Encore.isProduction()) {
  Encore.addLoader({
    test: /\.(js|vue)$/,
    enforce: 'pre',
    loader: 'eslint-loader',
    // ⚙️ COMMENT below if you are using a client platform  ⚙️ //
    exclude: ['/node_modules', '/vendor', '/public'],
    // ⚙️ UNCOMMENT below if you are using a client platform  ⚙️ //
    // exclude: ['/node_modules', '/vendor', '/public', bundleNodeModules, bundleVendor, bundlePublic],
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

// Add aliases for files !
encoreConfig.resolve.alias = _.merge(encoreConfig.resolve.alias, { // merge is very important because if not present vue is not found because cnore add aliasl !! https://github.com/vuejs-templates/webpack/issues/215#issuecomment-514220431
  '@root': path.resolve(__dirname, '..'),
  '@js': path.resolve(__dirname, 'src/MobicoopBundle/Resources/assets/js'),
  '@css': path.resolve(__dirname, 'src/MobicoopBundle/Resources/assets/css'),
  '@translations': path.resolve(__dirname, 'src/MobicoopBundle/Resources/translations'),
  '@assets': path.resolve(__dirname, 'src/MobicoopBundle/Resources/assets'),
  '@themes': path.resolve(__dirname, 'src/MobicoopBundle/Resources/themes'),
  '@clientTranslations': path.resolve(__dirname, './translations'),
  '@components': path.resolve(__dirname, 'src/MobicoopBundle/Resources/assets/js/components'),
  '@images': path.resolve(__dirname, './public/images'),
  // ⚙️ UNCOMMENT below if you are using a client platform  ⚙️ //
  // '@clientJs': path.resolve(__dirname, './assets/js'),
  // '@clientCss': path.resolve(__dirname, './assets/css'),
  // '@clientAssets': path.resolve(__dirname, './assets'),
  // '@themes': path.resolve(__dirname, './themes'),
  // '@root': path.resolve(__dirname, ''),
  // '@clientComponents': path.resolve(__dirname, './assets/js/components'),
});

module.exports = [encoreConfig];