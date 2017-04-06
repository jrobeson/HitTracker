const autoPrefixer = require('broccoli-autoprefixer');
const cleanCss = require('broccoli-clean-css');
const compileSass = require('broccoli-sass');
const concat = require('broccoli-concat');
const mergeTrees = require('broccoli-merge-trees');
const path = require('path');
const sieveFiles = require('broccoli-funnel');
const uglifyJs = require('broccoli-uglify-js');
const esTranspiler = require('broccoli-babel-transpiler');


/**
 * Get environment name, first by looking at SYMFONY_ENV
 * and then falling back to BROCCOLI_ENV
 *
 * @returns string
 */
function getEnv() {
    const symfonyEnv = process.env.SYMFONY_ENV || 'development';
    return = process.env.BROCCOLI_ENV || symfonyEnv;
}
exports.getEnv = getEnv();

// paths
const bowerRoot = 'bower_components';
const appCssFile = 'style/app.css';
const scoreBoardCssFile = 'style/scoreboard.css';
const scoreCardCssFile = 'style/scorecard.css';

const env = getEnv();

const sassSources = mergeTrees([
    `${bowerRoot}/bootstrap-sass-official/assets/stylesheets`,
    `${bowerRoot}/fontawesome/scss`,
    'app/Resources/styles',
]);

const sassOptions = {
    precision: 10,
};


let appCss = autoPrefixer(
    compileSass([sassSources], 'app.scss', appCssFile, sassOptions),
);
let scoreBoardCss = autoPrefixer(
    compileSass([sassSources], 'scoreboard.scss', scoreBoardCssFile, sassOptions),
);
let scoreCardCss = autoPrefixer(
    compileSass([sassSources], 'scorecard.scss', scoreCardCssFile, sassOptions),
);

const bowerJsTree = sieveFiles(bowerRoot, {
    files: [
        'jquery/dist/jquery.js',
        'bootstrap-sass-official/assets/javascripts/bootstrap.js',
        'jquery-color/jquery.color.js',
        'jquery.countdown/dist/jquery.countdown.js',
        // 'modernizr/modernizr.js'
    ],
    destDir: 'js',
    getDestinationPath: relativePath => path.basename(relativePath),
});

let jsTree = sieveFiles(`${__dirname}/app/Resources/js`, {
    include: ['*.js'],
    destDir: 'js',
});

jsTree = esTranspiler(jsTree, {
    filterExtensions: ['js', 'es6'],
    compact: false,
});

let appJs = mergeTrees([bowerJsTree, jsTree]);

appJs = concat(appJs, {
    inputFiles: [
        'js/jquery.js',
        'js/bootstrap.js',
        'js/jquery.color.js',
        'js/jquery.countdown.js',
        'js/jquery-ujs.js',
        'js/game.js',
        'js/common.js',
    ],
    outputFile: '/js/app.js',
});

if (env === 'production') {
    appJs = uglifyJs(appJs, {
        compress: true,
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

const fontAwesomeFonts = sieveFiles(`${bowerRoot}/fontawesome/fonts`, {
    destDir: '/fonts',
});
const glyphiconsFonts = sieveFiles(`${bowerRoot}/bootstrap-sass-official/assets/fonts/`, {
    destDir: '/fonts',
});
const appAssets = mergeTrees([
    appCss,
    scoreBoardCss,
    scoreCardCss,
    appJs,
    fontAwesomeFonts,
    glyphiconsFonts,
]);
/* if (env === 'production') {
    appAssets = assetRev(appAssets, {
        extensions: ['js', 'css', 'png', 'jpg', 'gif'],
        //exclude: ['fonts/169929'],
        // prepend: 'https://example.com/',
        replaceExtensions: ['html', 'js', 'css'],
    });
}*/

module.exports = appAssets;
