const mix = require('laravel-mix');
// const webpack = require('webpack');
// const tailwindcss = require('tailwindcss');
const src = 'resources';
const dest = 'resources/dist';

mix.setPublicPath('./resources/dist');

mix.css(`${src}/css/cp.css`, `${dest}/css`).options({
    processCssUrls: false,
    postCss: [
        // tailwindcss('./tailwind.config.js'),
        // require('autoprefixer')
    ],
});

mix.js(`${src}/js/cp.js`, `${dest}/js`);

mix.sourceMaps();

mix.options({ extractVueStyles: true });
