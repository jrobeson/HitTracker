HitTracker
========================

[![Build Status](https://travis-ci.org/jrobeson/HitTracker.svg?branch=master)](https://travis-ci.org/jrobeson/HitTracker)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/c98aecd9-b933-486b-b586-26387eca5474/mini.png)](https://insight.sensiolabs.com/projects/c98aecd9-b933-486b-b586-26387eca5474)

[![Dependency Status](https://www.versioneye.com/user/projects/53ba3e02609ff0a377000002/badge.svg?style=flat)](https://www.versioneye.com/user/projects/53ba3e02609ff0a377000002)


This document contains information on how to download, install, and start
this program.

Requirements
========================

To install this program you will need:

 * [PHP](http://php.net) >= 5.5
 * Various PHP extensions (checked below)
 * [Composer](http://getcomposer.org)
 * [PostgreSQL](http://postgresql.org) >= 9.3
 * [Nginx](http://nginx.org)
 * [Nginx Push Stream Module](https://github.com/wandenberg/nginx-push-stream-module)

To (re)build the assets (js,css,images) for this program you will need:

 * [Node.js](http://nodejs.org) (for bower and npm)
 * A [Sass](http://sass-lang.com) compiler

To run this program you will need:

 * [Firefox](http://www.getfirefox.org)
 * [JSPrintSetup](http://jsprintsetup.mozdev.org)


1) Installation
----------------------------------

 * Configuring the web server

   TODO: add nginx configuration

 * Web application

    1) `php composer.phar create-project jrobeson/hit-tracker path/to/install`
    2) `bin/symfony_requirements`
    3) <answer questions>
    4) `cd /path/to/install`
    5) `bin/console doctrine:database:create`
    6) `bin/console doctrine:schema:create`
    7) `bin/console cache:clear`

 * Vest data receiver
   * TODO: link to htdataredirector once it has instructions

 * Optional - (Re)build assets

    1) `npm install`
    2) `bower install`
    3) `ember build --output-path web/assets`

2) Run
--------------------------------
  Go to http://example.org/
