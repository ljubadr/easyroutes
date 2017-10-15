var gulp = require('gulp'),
    gp_concat = require('gulp-concat'),
    gp_rename = require('gulp-rename'),
    gp_uglify = require('gulp-uglify');

gulp.task('js-lib-min', function() {
    return gulp.src([
      'bower_components/jquery/dist/jquery.min.js',
      'bower_components/datatables/media/js/jquery.dataTables.min.js',
      'bower_components/nprogress/nprogress.js',
      'bower_components/select2/dist/js/select2.min.js',
      'bower_components/mark.js/dist/jquery.mark.js',
      'bower_components/datatables.mark.js/dist/datatables.mark.min.js',
    ])
    .pipe(gp_concat('concat.js'))
    .pipe(gulp.dest('src/tmp'))
    .pipe(gp_rename('easyroutes-libs.min.js'))
    .pipe(gp_uglify())
    .pipe(gulp.dest('src/dist/js'));
});

gulp.task('js-min', function() {
    return gulp.src([
      'src/assets/js/scripts.js',
    ])
    .pipe(gp_concat('concat.js'))
    .pipe(gulp.dest('src/tmp'))
    .pipe(gp_rename('easyroutes.min.js'))
    .pipe(gp_uglify())
    .pipe(gulp.dest('src/dist/js'));
});

gulp.task('css-lib-min', function() {
    return gulp.src([
      'bower_components/flexbox-grid/dist/flexbox-grid.min.css',
      'src/assets/css/easyroutes-datatable-theme.css',
      'bower_components/nprogress/nprogress.css',
      'bower_components/select2/dist/css/select2.min.css',
      // 'bower_components/datatables.mark.js/dist/datatables.mark.min.css',
      'src/assets/css/easyroutes.css',
    ])
    .pipe(gp_concat('concat.css'))
    .pipe(gulp.dest('src/tmp'))
    .pipe(gp_rename('easyroutes-libs.min.css'))
    // .pipe(gp_uglify())
    .pipe(gulp.dest('src/dist/css'));
});

gulp.task('copy-images', function() {
    return gulp.src('bower_components/datatables/media/images/**')
               .pipe( gulp.dest('src/dist/images') );
});

gulp.task('default', [
  'js-min',
  'js-lib-min',
  'css-lib-min',
  'copy-images'
], function() {});
