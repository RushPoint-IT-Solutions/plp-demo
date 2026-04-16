const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .js('resources/js/applicant-form.js', 'public/js')
   .js('resources/js/applicant-select.js', 'public/js')
   .js('resources/js/student-profile.js', 'public/js')
   .js('resources/js/student-sidebar-dropdown.js', 'public/js')
   .js('resources/js/section-offering.js', 'public/js')
   .js('resources/js/registrar-cor.js', 'public/js')
   .js('resources/js/registrar-loa-enrolled.js', 'public/js')
   .js('resources/js/registrar-citizens-charter.js', 'public/js')
   .js('resources/js/registrar-request-form-f137a.js', 'public/js')
   .js('resources/js/registrar-faculty-loads.js', 'public/js')
   .js('resources/js/slot-monitoring-report.js', 'public/js')
   .js('resources/js/registrar-listbox-select.js', 'public/js')
   .js('resources/js/registrar-class-list.js', 'public/js')
   .js('resources/js/registrar-system-config-configuration.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .sass('resources/sass/style.scss', 'public/css')
   .sass('resources/sass/faculty-notifications.scss', 'public/css')
   .sass('resources/sass/certificate-gwa.scss', 'public/css')
   .sass('resources/sass/certificate-graduation-8c2.scss', 'public/css')
   .sass('resources/sass/certificate-honor-8d2.scss', 'public/css')
   .sass('resources/sass/cog-copy-of-grades.scss', 'public/css')
   .sass('resources/sass/cor-certificate-of-registration.scss', 'public/css')
   .sass('resources/sass/loa-enrolled.scss', 'public/css')
   .sass('resources/sass/citizens-charter.scss', 'public/css')
   .sass('resources/sass/request-form-f-137a.scss', 'public/css')
   .sass('resources/sass/registrar-faculty-loads.scss', 'public/css')
   .version();

   mix.browserSync({
      proxy: '127.0.0.1:8000',
      host: '127.0.0.1',
      open: false,
      notify: false,
      files: [
         'app/**/*.php',
         'resources/views/**/*.php',
         'routes/**/*.php',
         'resources/js/**/*.js',
         'resources/sass/**/*.scss',
         'public/js/**/*.js',
         'public/css/**/*.css'
      ],
   });
