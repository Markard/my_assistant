//-----------------------------------------------------//
//                      Plugins                        //
//-----------------------------------------------------//
var gulp = require('gulp');
var del = require('del');
var concat = require('gulp-concat');
var minifyСss = require('gulp-minify-css');
var rename = require('gulp-rename');
var uglify = require('gulp-uglify');
var runSequence = require('run-sequence');
var less = require('gulp-less');
var path = require('path');
//-----------------------------------------------------//
//                        Paths                        //
//-----------------------------------------------------//
var pathBower = 'bower_components/';
var pathSource = 'assets/';
var pathDestination = 'web/static/';
var appJsSrc = [
    pathSource + 'app.constants.js',
    pathSource + 'shared/service.js',
    pathSource + 'shared/filters.js',
    // Directives
    pathSource + 'app.directives.js',
    pathSource + 'shared/bootstrapValidation/directive.js',
    pathSource + 'shared/datePicker/directive.js',
    pathSource + 'shared/menu/directive.js',
    pathSource + 'shared/budgetSummary/directive.js',
    pathSource + 'shared/updateTitle/directive.js',
    pathSource + 'shared/messageBox/directive.js',
    //Controllers
    pathSource + 'components/budget/controller.js',
    pathSource + 'components/budget/service.js',
    pathSource + 'components/session/controller.js',
    pathSource + 'components/session/service.js',
    pathSource + 'components/user/controller.js',
    pathSource + 'components/user/service.js',
    pathSource + 'app.translate.js',
    // Rotes
    pathSource + 'app.routes.js',
    pathSource + 'components/general/routes.js',
    pathSource + 'components/session/routes.js',
    pathSource + 'components/user/routes.js',
    pathSource + 'components/budget/routes.js',
    // General
    pathSource + 'app.config.js',
    pathSource + 'app.module.js'

];
//-----------------------------------------------------//
//                        Tasks                        //
//-----------------------------------------------------//
gulp.task('default', function () {
    runSequence('clean', [
        'prepare:css',
        'prepare:less',
        'prepare:js',
        'copy:fonts',
        'copy:images',
        'copy:html'
    ]);
});

gulp.task('watch', [
    'prepare:less',
    'prepare:js',
    'copy:html'
], function () {
    gulp.watch([pathSource + '/less/**/*.less'], ['prepare:less']);
    gulp.watch(appJsSrc, ['prepare:app:js']);
    gulp.watch([
        pathSource + 'components/budget/templates/**/*.html',
        pathSource + 'components/session/templates/**/*.html',
        pathSource + 'components/general/templates/**/*.html',
        pathSource + 'components/user/templates/**/*.html',
        pathSource + 'shared/menu/**/*.html',
        pathSource + 'shared/datePicker/**/*.html',
        pathSource + 'shared/budgetSummary/**/*.html',
        pathSource + 'shared/messageBox/**/*.html'
    ], ['copy:html'])
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                 Clean static                              //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('clean', function (callback) {
    return del([
        pathDestination + '**/*'
    ], callback);
});
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                 Prepare css                               //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('prepare:css', ['prepare:css:bootstrap', 'prepare:css:custom'], function () {
    return gulp.src([
        pathBower + 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css',
        pathBower + 'metisMenu/dist/metisMenu.css',
        pathBower + 'select2/select2.css',
        pathBower + 'angular-growl-2/build/angular-growl.min.css',
        pathBower + 'angular-bootstrap/ui-bootstrap-csp.css',
        pathBower + 'angular-ui-select/dist/select.min.css',
        pathBower + 'components-font-awesome/css/font-awesome.min.css',
        pathBower + 'angular-loading-bar/src/loading-bar.css',
    ])
        .pipe(concat('vendor.css'))
        .pipe(minifyСss({keepSpecialComments: 0}))
        .pipe(gulp.dest(pathDestination + 'css'));
});

gulp.task('prepare:css:bootstrap', function () {
    return gulp.src(pathBower + 'bootstrap/less/bootstrap.less')
        .pipe(less({
            paths: [path.join(__dirname, 'less', 'mixins')]
        }))
        .pipe(minifyСss({keepSpecialComments: 0}))
        .pipe(gulp.dest(pathDestination + 'css'));
});

gulp.task('prepare:css:custom', function () {
    return gulp.src(pathSource + 'css/bootstrap-lumen.min.css')
        .pipe(minifyСss({keepSpecialComments: 0}))
        .pipe(gulp.dest(pathDestination + 'css'));
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                 Prepare less                              //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('prepare:less', function () {
    return gulp.src(pathSource + 'less/app.less')
        .pipe(less({
            paths: [path.join(__dirname, 'less', 'modules')]
        }))
        .pipe(minifyСss({keepSpecialComments: 0}))
        .pipe(gulp.dest(pathDestination + 'css'));
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                 Prepare js                                //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('prepare:js', ['prepare:bower:js', 'prepare:app:js']);

gulp.task('prepare:bower:js', function () {
    return gulp.src([
        pathBower + 'jquery/dist/jquery.js',
        pathBower + 'metisMenu/dist/metisMenu.js',
        pathBower + 'bootstrap/dist/js/bootstrap.js',
        pathBower + 'moment/min/moment.min.js',
        pathBower + 'select2/select2.js',
        pathBower + 'angular/angular.js',
        pathBower + 'angular-bo otstrap/ui-bootstrap.min.js',
        pathBower + 'angular-bootstrap/ui-bootstrap-tpls.min.js',
        pathBower + 'angular-bootstrap-datetimepicker/src/js/datetimepicker.js',
        pathBower + 'angular-route/angular-route.js',
        pathBower + 'angular-resource/angular-resource.js',
        pathBower + 'angular-cookies/angular-cookies.js',
        pathBower + 'angular-translate/angular-translate.js',
        pathBower + 'angular-growl-2/build/angular-growl.min.js',
        pathBower + 'moment/moment.js',
        pathBower + 'moment/locale/ru.js',
        pathBower + 'moment/locale/en.js',
        pathBower + 'angular-ui-select/dist/select.min.js',
        pathBower + 'angular-sanitize/angular-sanitize.js',
        pathBower + 'a0-angular-storage/dist/angular-storage.js',
        pathBower + 'angular-jwt/dist/angular-jwt.js',
        pathBower + 'ui-router/release/angular-ui-router.js',
        pathBower + 'angular-loading-bar/src/loading-bar.js',
        pathBower + 'angular-animate/angular-animate.js',
    ])
        .pipe(concat('vendor.js'))
        .pipe(uglify())
        .pipe(gulp.dest(pathDestination + 'js'));
});

gulp.task('prepare:app:js', function () {
    return gulp.src(appJsSrc)
        .pipe(uglify())
        .pipe(concat('app.js'))
        .pipe(gulp.dest(pathDestination + 'js'));
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                     Fonts                                 //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('copy:fonts', [
    'copy:fonts:bootstrap_glyphicons'
]);

gulp.task('copy:fonts:bootstrap_glyphicons', function () {
    return gulp.src([
        pathBower + 'bootstrap/dist/fonts/glyphicons-halflings-regular.eot',
        pathBower + 'bootstrap/dist/fonts/glyphicons-halflings-regular.svg',
        pathBower + 'bootstrap/dist/fonts/glyphicons-halflings-regular.ttf',
        pathBower + 'bootstrap/dist/fonts/glyphicons-halflings-regular.woff',
        pathBower + 'bootstrap/dist/fonts/glyphicons-halflings-regular.woff2',
        pathBower + 'components-font-awesome/fonts/*'
    ])
        .pipe(gulp.dest(pathDestination + 'fonts'));
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                     Images                                //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('copy:images', function (callback) {
//var result = gulp.src([
//    pathSource + '**/*.{png,gif,jpg}',
//    pathBower + '**/*.{png,gif,jpg}'
//])
//    .pipe(rename(function (path) {
//        path.dirname = path.dirname.substring(path.dirname.lastIndexOf("/") + 1, path.dirname.length);
//    }))
//    .pipe(gulp.dest(pathDestinationCommon));
//return result
});

//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
//                     HTML                                  //
//+++++++++++++++++++++++++++++++++++++++++++++++++++++//
gulp.task('copy:html', [
    'copy:html:directives:menu',
    'copy:html:directives:datePicker',
    'copy:html:directives:budgetSummary',
    'copy:html:directives:messageBox',
    'copy:html:components:budget',
    'copy:html:components:general',
    'copy:html:components:session',
    'copy:html:components:user',
]);

gulp.task('copy:html:directives:menu', function () {
    return gulp.src([pathSource + 'shared/menu/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'shared/menu/'));
});

gulp.task('copy:html:directives:datePicker', function () {
    return gulp.src([pathSource + 'shared/datePicker/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'shared/datePicker/'));
});

gulp.task('copy:html:directives:budgetSummary', function () {
    return gulp.src([pathSource + 'shared/budgetSummary/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'shared/budgetSummary/'));
});

gulp.task('copy:html:directives:messageBox', function () {
    return gulp.src([pathSource + 'shared/messageBox/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'shared/messageBox/'));
});

gulp.task('copy:html:components:budget', function () {
    return gulp.src([pathSource + 'components/budget/templates/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'app/budget/templates/'));
});

gulp.task('copy:html:components:general', function () {
    return gulp.src([pathSource + 'components/general/templates/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'app/general/templates/'));
});

gulp.task('copy:html:components:session', function () {
    return gulp.src([pathSource + 'components/session/templates/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'app/session/templates/'));
});

gulp.task('copy:html:components:user', function () {
    return gulp.src([pathSource + 'components/user/templates/**/*.html'])
        .pipe(gulp.dest(pathDestination + 'app/user/templates/'));
});