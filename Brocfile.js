var _ = require('lodash');
var assetRev = require('broccoli-asset-rev');
var autoPrefixer = require('broccoli-autoprefixer');
var cleanCss = require('broccoli-clean-css');
var compileCoffeeScript = require('broccoli-coffee');
var compileSass = require('broccoli-ruby-sass');
var concat = require('broccoli-concat');
var mergeTrees = require('broccoli-merge-trees');
var pickFiles = require('broccoli-static-compiler');
var uglifyJs = require('broccoli-uglify-js');

exports.getEnv = getEnv();
/**
 * Overwrite broccoli-env's getEnv to work with Symfony's environment variable
 *
 * broccoli-env only supports production and development, so we map
 * everything to those.
 */
function getEnv () {
    var env = process.env.SYMFONY_ENV || 'dev';
    switch (env) {
        case 'test': // revisit this once we require more JS for test.
        case 'prod':
        case 'staging':
            env = 'production';
        break;
        case 'dev':
            env = 'development';
        break;
        default:
            throw new Error('Environment ' + env + ' not supported');
    }
    return env
}

// paths
var bowerRoot = 'vendor/bower';
var appCssFile = 'style/app.css';
var scoreBoardCssFile = 'style/scoreboard.css';
var scoreCardCssFile = 'style/scorecard.css';

var sassSources = [
    bowerRoot + '/bootstrap-sass-official/assets/stylesheets',
    bowerRoot + '/fontawesome/scss',
    'app/Resources/styles',
];

var sassOptions = {
    unixNewlines: true,
    cacheLocation: 'var/cache/dev/sass-cache',
    precision: 10
};

var env = getEnv();
var appCss;
var scoreBoardCss;
var scoreCardCss;
var appJs;
var appFonts;
var appAssets;

appCss = compileSass(sassSources, 'app.scss', appCssFile, sassOptions);
appCss = autoPrefixer(appCss);
scoreBoardCss = compileSass(sassSources, 'scoreboard.scss', scoreBoardCssFile, sassOptions);
scoreBoardCss = autoPrefixer(scoreBoardCss);
scoreCardCss = compileSass(sassSources, 'scorecard.scss', scoreCardCssFile, sassOptions);
scoreCardCss = autoPrefixer(scoreCardCss);

var filesMap = {};
filesMap[bowerRoot + '/jquery/dist'] = ['jquery.js'];
filesMap[bowerRoot + '/bootstrap-sass-official/assets/javascripts'] = ['bootstrap.js'];
filesMap[bowerRoot + '/jquery-color'] = ['jquery.color.js'];
filesMap[bowerRoot + '/jquery.countdown/dist'] = ['jquery.countdown.js'];
filesMap[__dirname + '/web/bundles/common/js'] = ['jquery-ujs.js'];
filesMap[__dirname + '/src/GameBundle/Resources/coffee'] = ['game.coffee', 'common.coffee'];

var jsTrees = [];
for(var dir in filesMap) {
    var tree = pickFiles(dir, {
        srcDir: '/',
        files: filesMap[dir],
        destDir: 'js'
    });
    tree = compileCoffeeScript(tree, {bare: true});
    jsTrees.push(tree);
}

appJs = mergeTrees(jsTrees);

// @todo get inputFiles from filesMap
appJs = concat(appJs, {
    inputFiles: [
        'js/jquery.js',
        'js/bootstrap.js',
        'js/jquery.color.js',
        'js/jquery.countdown.js',
        'js/jquery-ujs.js',
        'js/game.js',
        'js/common.js'
    ],
    outputFile: '/js/app.js'
});

//
if ('production' == env) {
    appJs = uglifyJs(appJs, {
        //mangle: true,
        compress: true
    });
    /* cleancss options to consider
        root - path to resolve absolute @import rules and rebase relative URLs
        relativeTo - path with which to resolve relative @import rules and URLs
        processImport - whether to process @import rules
        noRebase - whether to skip URLs rebasing
    */
    appCss = cleanCss(appCss);
    scoreBoardCss = cleanCss(scoreBoardCss);
    scoreCardCss = cleanCss(scoreCardCss);
}

appFonts = pickFiles(bowerRoot + '/fontawesome/fonts', {
    srcDir: '/',
    destDir: '/fonts'
});

appAssets = mergeTrees([appCss, scoreBoardCss, scoreCardCss, appJs, appFonts]);
/*if ('production' == env) {
    appAssets = assetRev(appAssets, {
        extensions: ['js', 'css', 'png', 'jpg', 'gif'],
        //exclude: ['fonts/169929'],
        // prepend: 'https://example.com/',
        replaceExtensions: ['html', 'js', 'css']
    });
}*/

module.exports = appAssets;
