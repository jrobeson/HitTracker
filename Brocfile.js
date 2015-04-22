var _ = require('lodash');
var assetRev = require('broccoli-asset-rev');
var autoPrefixer = require('broccoli-autoprefixer');
var cleanCss = require('broccoli-clean-css');
var compileCoffeeScript = require('broccoli-coffee');
var compileSass = require('broccoli-ruby-sass');
var concat = require('broccoli-concat');
var mergeTrees = require('broccoli-merge-trees');
var sieveFiles = require('broccoli-funnel');
var uglifyJs = require('broccoli-uglify-js');
var esTranspiler = require('broccoli-babel-transpiler');

exports.getEnv = getEnv();

/**
 * Get environment name, first by looking at SYMFONY_ENV
 * and then falling back to BROCCOLI_ENV
 *
 * @returns string
 */
function getEnv () {
    var symfonyEnv = process.env.SYMFONY_ENV || 'dev';
    var env = process.env.BROCCOLI_ENV || symfonyEnv;
    switch (env) {
        case 'prod':
        case 'production':
        case 'staging':
            env = 'production';
            break;
        case 'dev':
        case 'development':
        case 'test':
            env = 'development';
            break;
        default:
            throw new Error('Environment "' + env + '" is not supported');
    }
    return env
}

// paths
var bowerRoot = 'vendor/bower';
var appCssFile = 'style/app.css';
var scoreBoardCssFile = 'style/scoreboard.css';
var scoreCardCssFile = 'style/scorecard.css';

var env = getEnv();
var buildType = process.env.SYMFONY__BUILD_TYPE || 'hosted';

var sassSources = mergeTrees([
    bowerRoot + '/bootstrap-sass-official/assets/stylesheets',
    bowerRoot + '/fontawesome/scss',
    'app/Resources/styles'
]);

var sassOptions = {
    unixNewlines: true,
    cacheLocation: 'var/cache/' + buildType + '/' + env + '/sass-cache',
    precision: 10
};


var appCss = autoPrefixer(
    compileSass(sassSources, 'app.scss', appCssFile, sassOptions)
);
var scoreBoardCss = autoPrefixer(
    compileSass(sassSources, 'scoreboard.scss', scoreBoardCssFile, sassOptions)
);
var scoreCardCss = autoPrefixer(
    compileSass(sassSources, 'scorecard.scss', scoreCardCssFile, sassOptions)
);

var filesMap = {};
filesMap[bowerRoot + '/jquery/dist'] = ['jquery.js'];
filesMap[bowerRoot + '/bootstrap-sass-official/assets/javascripts'] = ['bootstrap.js'];
filesMap[bowerRoot + '/jquery-color'] = ['jquery.color.js'];
filesMap[bowerRoot + '/jquery.countdown/dist'] = ['jquery.countdown.js'];
filesMap[__dirname + '/web/bundles/common/js'] = ['jquery-ujs.js'];
filesMap[__dirname + '/src/GameBundle/Resources/coffee'] = ['game.coffee', 'common.coffee'];

var jsTrees = [];
for(var dir in filesMap) {
    var tree = sieveFiles(dir, {
        files: filesMap[dir],
        destDir: 'js'
    });
    tree = compileCoffeeScript(tree, {bare: true});
    jsTrees.push(tree);
}

var appJs = mergeTrees(jsTrees);

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

//var scriptTree = esTranspiler(inputTree, options);
//
if ('production' == env) {
    appJs = uglifyJs(appJs, {
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

var appFonts = sieveFiles(bowerRoot + '/fontawesome/fonts', {
    destDir: '/fonts'
});

var appAssets = mergeTrees([appCss, scoreBoardCss, scoreCardCss, appJs, appFonts]);
/*if ('production' == env) {
    appAssets = assetRev(appAssets, {
        extensions: ['js', 'css', 'png', 'jpg', 'gif'],
        //exclude: ['fonts/169929'],
        // prepend: 'https://example.com/',
        replaceExtensions: ['html', 'js', 'css']
    });
}*/

module.exports = appAssets;
