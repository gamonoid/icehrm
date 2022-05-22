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
const babel = require('gulp-babel');
const less = require('gulp-less');
const path = require('path');
const cleanCSS = require('gulp-clean-css');
const javascriptObfuscator = require('gulp-javascript-obfuscator');
const ifElse = require('gulp-if-else');
const clean = require('gulp-clean');

const env = process.argv.filter((item) => item.substr(0, 3) === '--e');
let isProduction = false;
if (env.length === 1) {
  isProduction = env[0].substr(3) === 'prod';
}

if (isProduction) {
  process.env.NODE_ENV = 'production';
}

gulp.task('clean', () => gulp.src('web/dist', { read: false, allowEmpty: true }).pipe(clean()));

gulp.task('pack-js', (done) => gulp.src([
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
  .pipe(gulp.dest('web/dist')));

gulp.task('compile-ant-less', () => gulp.src([
  'web/node_modules/antd/dist/antd.less',
]).pipe(less({
  paths: [path.join(__dirname, 'less', 'includes')],
  javascriptEnabled: true,
}))
  .pipe(concat('antd.css'))
  .pipe(gulp.dest('web/dist')));

gulp.task('pack-css', (done) => gulp.src([
  'web/themecss/bootstrap.css',
  'web/themecss/fa-all-5.8.2.min.css',
  // 'web/themecss/font-awesome.css',
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
  'web/css/fa-animations.css',
  'web/css/style.css',
  'web/bower_components/simplemde/dist/simplemde.min.css',
  'web/node_modules/codemirror/lib/codemirror.css',
  'web/dist/antd.css',
])
  .pipe(cleanCSS())
  .pipe(concat('third-party.css'))
  .pipe(gulp.dest('web/dist')));

gulp.task('login-css', (done) => gulp.src([
  'web/css/login/bootstrap.css',
  'web/css/login/main.css',
  'web/themecss/fa-all-5.8.2.min.css',
  'web/themecss/ionicons.min.css',
  'web/bower_components/material-design-icons/iconfont/material-icons.css',
])
  .pipe(cleanCSS())
  .pipe(concat('login.css'))
  .pipe(gulp.dest('web/dist')));

gulp.task('login-js', () => browserify({
  entries: [
    'web/js/jquery-1.8.1.js',
    'web/bootstrap/js/bootstrap.js',
    'web/api-common/login.js',
  ],
  basedir: '.',
  debug: true,
  cache: {},
  packageCache: {},
})
  .transform('babelify', {
    plugins: [
      ['@babel/plugin-proposal-class-properties', { loose: true }],
    ],
    presets: ['@babel/preset-env'],
    extensions: ['.js'],
  })
  .bundle()
  .pipe(source('login.js'))
  .pipe(buffer())
  .pipe(ifElse(isProduction, () => uglifyes(
    {
      compress: true,
      mangle: {
        reserved: [],
      },
    },
  )))
  .pipe(gulp.dest('./web/dist')));

gulp.task('copy-assets', (done) => {
  const tasks = [];
  tasks.push(gulp
    .src([
      'web/js/select2/*.png',
      'web/js/select2/*.gif',
      'web/js/select2/*.gif',
    ]).pipe(gulp.dest('web/dist/img/select2')));

  tasks.push(gulp
    .src([
      'web/js/bootstrap-colorpicker-2.1.1/img/bootstrap-colorpicker/*.png',
    ]).pipe(gulp.dest('web/dist/img/bootstrap-colorpicker')));

  es.merge.apply(null, tasks).on('end', done);
});

gulp.task('api-common', (done) => browserify({
  basedir: '.',
  debug: true,
  entries: ['web/api-common/entry.js'],
  cache: {},
  packageCache: {},
})
  .transform('babelify', {
    presets: ['@babel/preset-env', '@babel/preset-react'], extensions: ['.js', '.jsx'],
  })
  .transform(require('browserify-css'))
  .bundle()
  .pipe(source('common.js'))
  .pipe(buffer())
  .pipe(ifElse(!isProduction, () => sourcemaps.init({ loadMaps: true })))
  .pipe(ifElse(isProduction, () => uglifyes(
    {
      compress: true,
      mangle: {
        reserved: [
          'Aes',
          'RequestCache',
          'SocialShare',
          'setupTimeUtils',
          'setupNotifications',
        ],
      },
    },
  )))
  .pipe(ifElse(isProduction, () => javascriptObfuscator({
    compact: true,
  })))
  .pipe(ifElse(!isProduction, () => sourcemaps.write('./')))
  .pipe(gulp.dest('web/dist')));

const vendorReact = ['react', 'react-dom'];
const vendorAntd = ['antd'];
const vendorAntdIcons = ['@ant-design/icons'];
const vendorAntv = ['@antv/g2plot'];
const vendorOther = ['moment', 'codemirror'];

const vendorList = [
  ['vendorReact.js', vendorReact, []],
  ['vendorAntd.js', vendorAntd, vendorReact],
  ['vendorAntdIcons.js', vendorAntdIcons, vendorReact],
  ['vendorAntv.js', vendorAntv, vendorReact],
  ['vendorOther.js', vendorOther, []],
];

const vendorsFlat = vendorList.reduce((acc, item) => {
  acc.push(...item[1]);
  return acc;
}, []);

const compileVendor = (name, vendors, external) => {
  const b = browserify({
    basedir: './web',
    debug: true,
  });

  // require all libs specified in vendors array
  vendors.forEach((lib) => {
    b.require(lib);
  });

  return b.external(external)
    .bundle()
    .pipe(source(name))
    .pipe(buffer())
    .pipe(ifElse(isProduction, () => uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    )))
    .pipe(gulp.dest('./web/dist'));
};

gulp.task('vendor', (done) => {
  const tasks = vendorList
    .map(([name, vendors, external]) => compileVendor(name, vendors, external));

  es.merge.apply(null, tasks).on('end', done);
});

gulp.task('admin-js', (done) => {
  // we define our input files, which we want to have
  // bundled:
  const files = [
    'attendance',
    'company_structure',
    'connection',
    'custom_fields',
    'clients',
    'charts',
    'dashboard',
    'documents',
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
  return browserify({
    entries: files.map((file) => `web/admin/src/${file}/index.js`),
    basedir: '.',
    debug: true,
    cache: {},
    packageCache: {},
  })
    .external(vendorsFlat)
    .transform('babelify', {
      plugins: [
        ['@babel/plugin-proposal-class-properties', { loose: true }],
      ],
      presets: ['@babel/preset-env', '@babel/preset-react'],
      extensions: ['.js', '.jsx'],
    })
    .transform(require('browserify-css'))
    .bundle()
    .pipe(source('admin-bundle.js'))
    .pipe(buffer())
    .pipe(ifElse(!isProduction, () => sourcemaps.init({ loadMaps: true })))
    .pipe(ifElse(isProduction, () => uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    )))
    .pipe(ifElse(isProduction, () => javascriptObfuscator({
      compact: true,
    })))
    .pipe(ifElse(!isProduction, () => sourcemaps.write('./')))
    .pipe(gulp.dest('./web/dist'));
});

gulp.task('modules-js', (done) => {
  // we define our input files, which we want to have
  // bundled:
  const files = [
    'attendance',
    'dashboard',
    'dependents',
    'documents',
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
  return browserify({
    entries: files.map((file) => `web/modules/src/${file}/index.js`),
    basedir: '.',
    debug: true,
    cache: {},
    packageCache: {},
  })
    .external(vendorsFlat)
    .transform('babelify', {
      plugins: [
        ['@babel/plugin-proposal-class-properties', { loose: true }],
      ],
      presets: ['@babel/preset-env', '@babel/preset-react'],
      extensions: ['.js', '.jsx'],
    })
    .transform(require('browserify-css'))
    .bundle()
    .pipe(source('modules-bundle.js'))
    .pipe(buffer())
    .pipe(ifElse(!isProduction, () => sourcemaps.init({ loadMaps: true })))
    .pipe(ifElse(isProduction, () => uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    )))
    .pipe(ifElse(isProduction, () => javascriptObfuscator({
      compact: true,
    })))
    .pipe(ifElse(!isProduction, () => sourcemaps.write('./')))
    .pipe(gulp.dest('./web/dist'));
});

gulp.task('extension-js', (done) => {
  let extension = process.argv.filter((item) => item.substr(0, 3) === '--x');
  if (extension.length === 1) {
    extension = extension[0].substr(3);
  }

  // map them to our stream function
  return browserify({
    entries: [`extensions/${extension}/web/js/index.js`],
    basedir: '.',
    debug: true,
    cache: {},
    packageCache: {},
  })
    .external(vendorsFlat)
    .transform('babelify', {
      plugins: [
        ['@babel/plugin-proposal-class-properties', { loose: true }],
      ],
      presets: ['@babel/preset-env', '@babel/preset-react'],
      extensions: ['.js', '.jsx'],
    })
    .transform(require('browserify-css'))
    .bundle()
    .pipe(source(`${extension}.js`))
    .pipe(buffer())
    .pipe(ifElse(!isProduction, () => sourcemaps.init({ loadMaps: true })))
    .pipe(ifElse(isProduction, () => uglifyes(
      {
        compress: true,
        mangle: {
          reserved: [],
        },
      },
    )))
    .pipe(ifElse(isProduction, () => javascriptObfuscator({
      compact: true,
    })))
    .pipe(ifElse(!isProduction, () => sourcemaps.write('./')))
    .pipe(gulp.dest(`./extensions/${extension}/dist`));
});

gulp.task('watch', () => {
  gulp.watch('web/admin/src/*/*.js', gulp.series('admin-js'));
  gulp.watch('web/modules/src/*/*.js', gulp.series('modules-js'));
});

gulp.task('default', gulp.series(
  'compile-ant-less',
  'pack-js',
  'pack-css',
  'login-css',
  'login-js',
  'copy-assets',
  'api-common',
  'vendor',
  'admin-js',
  'modules-js',
));
