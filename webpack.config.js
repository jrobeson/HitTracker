const path = require('path');
const Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('public/build/')
  .setPublicPath('/build')
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .addEntry('js/app', './assets/js/app.ts')

  //.addStyleEntry('global', './assets/styles/global.scss')
  .addStyleEntry('style/app', ['./assets/style/app.scss'])
  .addStyleEntry('style/scoreboard', ['./assets/style/scoreboard.scss'])
  .addStyleEntry('style/scorecard', ['./assets/style/scorecard.scss'])

  .configureBabel(babelConfig => {
    babelConfig.presets.push('es2017');
  })
  .enableSassLoader(
    sassOptions => {
      sassOptions.precision = 10;
    },
    {
      resolveUrlLoader: false,
    }
  )
  .enableTypeScriptLoader(tsConfig => {})
  .enableForkedTypeScriptTypesChecking(tsForkedConfig => {
    tsForkedConfig.vue = true;
    tsForkedConfig.tslint = true;
  })
  .enablePostCssLoader()
  .enableVueLoader()

  .autoProvideVariables({
    $: 'jquery',
    jQuery: 'jquery',
    'window.jQuery': 'jquery',
  })

  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction());

const config = Encore.getWebpackConfig();
config.resolve.alias = {
  ...config.resolve.alias,
  '@': path.resolve(__dirname, './assets/js'),
  style: path.resolve(__dirname, './assets/style'),
};

module.exports = config;
