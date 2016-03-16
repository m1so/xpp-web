var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

var bowerDir = 'resources/assets/bower/';

// Fix for LESS path
elixir.config.assetsDir = 'resources/assets/bower/';

// Vueify transform
require('laravel-elixir-vueify');

// Elixir mixes
elixir(function(mix) {
    // Vue components entry
    mix.browserify('main.js');

    // Vendor stuff
    mix.scripts([
        'jquery/dist/jquery.js',
        'admin-lte/bootstrap/js/bootstrap.js',
        'admin-lte/dist/js/app.js'
    ], 'public/js/vendor.js', bowerDir)

    // AdminLTE bootstrap CSS
    .styles([
        'admin-lte/bootstrap/css/bootstrap.css'
    ], 'public/css/bootstrap.css', bowerDir)

    // Icons CSS
    .styles([
        'ionicons/css/ionicons.css'
    ], 'public/css/icons.css', bowerDir)

    // LESS
    // Use '../' to get rid of default 'less/' prefix
    .less([
        '../bower/admin-lte/build/less/AdminLTE.less',
        '../bower/admin-lte/build/less/skins/skin-green.less'
    ], 'public/css/app.css')

    .sass('main.scss')

    // Copy the fonts
    .copy(bowerDir + 'ionicons/fonts', 'public/build/fonts')

    // Version files
    .version([
        'css/bootstrap.css',
        'css/app.css',
        'css/main.css',
        'css/icons.css',
        'js/vendor.js',
        'js/main.js'
    ]);
});
