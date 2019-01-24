const path = require('path');
const glob = require('glob-all');
const { mix } = require('laravel-mix');
const PurgecssPlugin = require('purgecss-webpack-plugin');

mix
    .js('resources/js/app.js', 'public/js/')
    .postCss('resources/css/app.css', 'public/css/')

    .version()

    .options({
        // CSSNext already processes our css with Autoprefixer, so we don't
        // need mix to do it twice.
        autoprefixer: false,

        // Our PostCSS plugins are defined in a standard `postcss.config.js`
        // file, which we'll read for plugins.
        postCss: require('./postcss.config').plugins,

        // Since we don't do any image preprocessing and write url's that are
        // relative to the site root, we don't want the css loader to try to
        // follow paths in `url()` functions.
        processCssUrls: false,
    })

    .webpackConfig(() => {
        const config = {};

        config.output = {
            // The public path needs to be set to the root of the site so
            // Webpack can locate chunks at runtime.
            publicPath: '/',

            // We'll place all chunks in the `js` folder by default so we don't
            // need to worry about ignoring them in our version control system.
            chunkFilename: 'js/[name].js',
        };

        /*
         * Purge CSS - commented out because it keeps removing xterms styles/colors 😠
         */

        // if (mix.inProduction()) {
        //     config.plugins = [
        //         new PurgecssPlugin({
        //             paths: glob.sync([
        //                 path.join(__dirname, 'app/**/*.php'),
        //                 path.join(__dirname, 'resources/views/**/*.blade.php'),
        //                 path.join(__dirname, 'resources/js/**/*.vue'),
        //                 path.join(__dirname, 'resources/js/**/*.js'),

        //                 // Blender css paths. In the future it would be preferable
        //                 // to simply ignore all of blender-css by extracting all
        //                 // selectors from the dist .css file provided by the
        //                 // package, but it's currently not possible to add
        //                 // css paths to Purgecss via the webpack plugin.
        //                 path.join(__dirname, 'node_modules/xterm/**/*.css'),
        //                 path.join(__dirname, 'node_modules/xterm/**/*.js'),
        //             ]),
        //             whitelistPatterns: [
        //                 /fa-/, // FontAwesome icon font selectors
        //                 /re-/, // Redactor icon font selectors
        //             ],
        //             extractors: [
        //                 {
        //                     extractor: class {
        //                         static extract(content) {
        //                             return content.match(/[A-z0-9-:\/]+/g) || [];
        //                         }
        //                     },
        //                     extensions: ['html', 'js', 'php', 'scss', 'vue'],
        //                 },
        //             ],
        //         }),
        //     ];
        // }

        return config;
    });