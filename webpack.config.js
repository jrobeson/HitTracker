const Encore = require('@symfony/webpack-encore');

Encore
    // directory where all compiled assets will be stored
    .setOutputPath('web/build/')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()

    // will output as web/build/app.js
    .addEntry('js/app', './app/Resources/assets/js/main.js')
    .addEntry('js/app', './app/Resources/assets/js/app.js')

    // will output as web/build/global.css
    //.addStyleEntry('global', './assets/styles/global.scss')
    .addStyleEntry('style/app', ['./app/Resources/assets/style/app.scss'])
    .addStyleEntry('style/scoreboard', ['./app/Resources/assets/style/scoreboard.scss'])
    .addStyleEntry('style/scorecard', ['./app/Resources/assets/style/scorecard.scss'])

    // allow sass/scss files to be processed
    .enableSassLoader({
         resolve_url_loader: false
     })
    .enablePostCssLoader()

    // allow legacy applications to use $/jQuery as a global variable
    .autoProvideVariables({
        '$': 'jquery',
        'jQuery': 'jquery',
        'window.jQuery': 'jquery'
    })

    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
