var elixir = require('laravel-elixir');
var gutils = require('gulp-util');

if (elixir.config.production == true) {
    process.env.NODE_ENV = 'production';
}

// Vueify transform
require('laravel-elixir-vueify');

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

// Setup paths
var paths = {
    // Vendor - base path to vendor packages
    vendor: {
        js: 'node_modules',
        css: 'node_modules',
        fonts: 'node_modules',
    },
    // Proxy - used for virtual machine + browsersync
    proxy: 'xpp.dev'
};

// Detect development environment and if we are running 'watch' task
var isDevEnv = gutils.env._.indexOf('watch') > -1 && elixir.config.production != true;

// Setup hot module reloading
if (isDevEnv) {
    elixir.config.js.browserify.plugins.push({
        name: "browserify-hmr",
        options : {}
    });
}

// Elixir mixes
elixir(function(mix) {
    // Vue components entry point
    mix.browserify('main.js');

    // Vendor JS
    mix.scripts([
        'jquery/dist/jquery.js',
        'admin-lte/bootstrap/js/bootstrap.js',
        'admin-lte/dist/js/app.js'
    ], 'public/js/vendor.js', paths.vendor.js);

    // Vendor CSS
    mix.styles([
        'admin-lte/bootstrap/css/bootstrap.css',
        'font-awesome/css/font-awesome.css'
    ], 'public/css/vendor.css', paths.vendor.css);

    // LESS entry point
    mix.less('main.less');

    // mix.sass('main.scss');

    // Copy the fonts
    mix.copy(paths.vendor.fonts + '/font-awesome/fonts', 'public/fonts');

    // BrowserSync setup
    if (isDevEnv) {
        mix.browserSync({
            // Files to watch
            files: [
               elixir.config.appPath + '/**/*.php',
               elixir.config.get('public.css.outputFolder') + '/**/*.css',
               elixir.config.get('public.versioning.buildFolder') + '/rev-manifest.json',
               'resources/views/**/*.php'
            ],
            // Proxy through VM
            proxy: paths.proxy,
            // Don't open new window when launching gulp and don't send popups in browser
            open: false,
            notify: false
        });
    }
});
