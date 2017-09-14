const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()

    .addEntry('js/app', './app/Resources/assets/js/app.ts')

    //.addStyleEntry('global', './assets/styles/global.scss')
    .addStyleEntry('style/app', ['./app/Resources/assets/style/app.scss'])
    .addStyleEntry('style/scoreboard', ['./app/Resources/assets/style/scoreboard.scss'])
    .addStyleEntry('style/scorecard', ['./app/Resources/assets/style/scorecard.scss'])

    .enableSassLoader((sassOptions) => {
        sassOptions.precision = 10;
    }, {
         resolve_url_loader: false
    })
    .enableTypeScriptLoader((tsConfig) => {
    })
    .enablePostCssLoader()

    .autoProvideVariables({
        '$': 'jquery',
        'jQuery': 'jquery',
        'window.jQuery': 'jquery'
    })

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
