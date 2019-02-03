const gulp = require('gulp');
const browserify = require('browserify');
const source = require('vinyl-source-stream');
const uglify = require('gulp-uglify');
const uglifyes = require('gulp-uglify-es').default;
const composer = require('gulp-uglify/composer');
const sourcemaps = require('gulp-sourcemaps');
const buffer = require('vinyl-buffer');
const obfuscate = require('gulp-obfuscate');
const minify = require('gulp-minify');
const es = require('event-stream');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const copy = require('gulp-copy');

const paths = {
  pages: ['src/*.html'],
};

gulp.task('pack-js', (done) => {
  gulp.src([
    'web/js/jquery2.0.2.min.js',
    'web/js/jquery-ui.js',
    'web/themejs/bootstrap.js',
    'web/js/jquery.placeholder.js',
    'web/js/base64.js',
    'web/js/bootstrap-datepicker.js',
    'web/js/select2/select2.min.js',
    'web/js/bootstrap-colorpicker-2.1.1/js/bootstrap-colorpicker.min.js',
    'web/js/fullcaledar/lib/moment.min.js',
    'web/js/fullcaledar/fullcalendar.min.js',
    'web/js/clipboard.js',
    'web/api-common/datatables/jquery.dataTables.js',
    'web/api-common/datatables/dataTables.bootstrap.js',
    'web/themejs/AdminLTE/app.js',
    'web/bower_components/tinymce/tinymce.min.js',
    'web/bower_components/simplemde/dist/simplemde.min.js',
    'web/bower_components/inputmask/dist/min/jquery.inputmask.bundle.min.js',
    'web/js/signature_pad.js',
    'web/js/date.js',
    'web/js/json2.js',
    'web/api-common/app-global.js',
  ])
    .pipe(concat('third-party.js'))
    .pipe(gulp.dest('web/dist'));
  done();
});

gulp.task('pack-css', (done) => {
  gulp.src([
    'web/themecss/bootstrap.min.css',
    'web/themecss/font-awesome.min.css',
    'web/themecss/ionicons.min.css',
    'web/bower_components/material-design-icons/iconfont/material-icons.css',
    'web/js/fullcaledar/fullcalendar.css',
    'web/themecss/datatables/dataTables.bootstrap.css',
    'web/css/jquery.timepicker.css',
    'web/css/datepicker.css',
    'web/css/bootstrap-datetimepicker.min.css',
    'web/js/select2/select2.css',
    'web/js/bootstrap-colorpicker-2.1.1/css/bootstrap-colorpicker.css',
    'web/themecss/AdminLTE.css',
    'web/css/style.css',
    'web/bower_components/simplemde/dist/simplemde.min.css',
  ])
    .pipe(concat('third-party.css'))
    .pipe(gulp.dest('web/dist'));
  done();
});

gulp.task('copy-assets', (done) => {
  gulp
    .src([
      'web/js/select2/*.png',
      'web/js/select2/*.gif',
      'web/js/select2/*.gif',
    ]).pipe(gulp.dest('web/dist/img/select2'));

  gulp
    .src([
      'web/js/bootstrap-colorpicker-2.1.1/img/bootstrap-colorpicker/*.png',
    ]).pipe(gulp.dest('web/dist/img/bootstrap-colorpicker'));

  done();
});

gulp.task('api-common', (done) => {
  browserify({
    basedir: '.',
    debug: true,
    entries: ['web/api-common/entry.js'],
    cache: {},
    packageCache: {},
  })
    .transform('babelify', {
      presets: ['es2015'], extensions: ['.js'],
    })
    .bundle()
    .pipe(source('common.js'))
    .pipe(buffer())
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [
            'Aes',
            'RequestCache',
            'setupTimeUtils',
            'setupNotifications',
          ],
        },
      },
    ))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('web/dist'));
  done();
});


gulp.task('admin-js', (done) => {
  // we define our input files, which we want to have
  // bundled:
  const files = [
    'attendance',
    'company_structure',
    'dashboard',
    'data',
    'employees',
    'fieldnames',
    'jobs',
    'loans',
    'metadata',
    'modules',
    'overtime',
    'payroll',
    'permissions',
    'projects',
    'qualifications',
    'reports',
    'salary',
    'settings',
    'travel',
    'users',
  ];
    // map them to our stream function
  const tasks = files.map(entry => browserify({
    entries: [`web/admin/src/${entry}/index.js`],
    basedir: '.',
    debug: true,
    cache: {},
    packageCache: {},
  })
    .transform('babelify', {
      presets: ['es2015'], extensions: ['.js'],
    })
    .bundle()
    .pipe(source(`${entry}/lib.js`))
  // rename them to have "bundle as postfix"
    .pipe(rename(`${entry}.js`))
    .pipe(buffer())
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    ))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./web/admin/dist/')));
    // create a merged stream
  es.merge.apply(null, tasks).on('end', done);
});

gulp.task('modules-js', (done) => {
  // we define our input files, which we want to have
  // bundled:
  const files = [
    'attendance',
    'dashboard',
    'dependents',
    'emergency_contact',
    'employees',
    'loans',
    'overtime',
    'projects',
    'qualifications',
    'reports',
    'salary',
    'staffdirectory',
    'time_sheets',
    'travel',
  ];
    // map them to our stream function
  const tasks = files.map(entry => browserify({
    entries: [`web/modules/src/${entry}/index.js`],
    basedir: '.',
    debug: true,
    cache: {},
    packageCache: {},
  })
    .transform('babelify', {
      presets: ['es2015'], extensions: ['.js'],
    })
    .bundle()
    .pipe(source(`${entry}/lib.js`))
  // rename them to have "bundle as postfix"
    .pipe(rename(`${entry}.js`))
    .pipe(buffer())
    .pipe(sourcemaps.init({ loadMaps: true }))
    .pipe(uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    ))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./web/modules/dist/')));
    // create a merged stream
  es.merge.apply(null, tasks).on('end', done);
});

gulp.task('default', gulp.series(
  'pack-js',
  'pack-css',
  'copy-assets',
  'api-common',
  'admin-js',
  'modules-js',
));
