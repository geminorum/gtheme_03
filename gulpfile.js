
var gulp    = require('gulp'),
	// sass         = require('gulp-sass'),
	compass      = require('gulp-compass'),
	plumber      = require('gulp-plumber'),
	notify       = require('gulp-notify'),
	htmlmin      = require('gulp-htmlmin'), // https://github.com/jonschlinkert/gulp-htmlmin
	rename       = require('gulp-rename'), // https://github.com/hparra/gulp-rename
	minifyCSS    = require('gulp-minify-css'),
	postcss      = require('gulp-postcss'), // https://github.com/postcss/postcss
	sourcemaps   = require('gulp-sourcemaps'),
	autoprefixer = require('autoprefixer'); // https://github.com/postcss/autoprefixer
	// path    = require('path');

gulp.task('default', function(){
	console.log( 'Hi, I\'m Gulp!' );
});

var plumberErrorHandler = { errorHandler: notify.onError({
	title: 'Gulp',
	message: 'Error: <%= error.message %>'
  })
};

gulp.task('watch', function() {

	gulp.watch('./stylesheets/**', ['compass']);
	// gulp.watch('./root/assets/stylesheets/**/*.scss', ['sass']);
	// gulp.watch('js/src/*.js', ['js']);
	// gulp.watch('img/src/*.{png,jpg,gif}', ['img']);
});

gulp.task('compass', function() {
	gulp.src('./stylesheets/*.scss')

		.pipe(plumber(plumberErrorHandler))

		.pipe(compass({
			project: __dirname, //path.join(__dirname, 'assets'),
			css: 'css',
			sass: 'sass',
			image: 'images',
			font: 'fonts',
			import_path: [
				"components/bootstrap-sass/assets/stylesheets"
			],
			require: [
				'sass-css-importer'
			]
		}))

		.pipe(minifyCSS())

		.pipe(gulp.dest('./css'));
});

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////


// gulp.task('sass', function () {
// 	gulp.src('./root/assets/stylesheets/style.scss')
// 	.pipe(plumber(plumberErrorHandler))
// 	.pipe(sass({
// 		// includePaths: require('node-neat').includePaths
// 		// includePaths: require('node-bourbon').with('other/path', 'another/path')
//     }))
//     .pipe(gulp.dest('./root/assets/css'));
// });

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////


// https://github.com/sass/sass/issues/556#issuecomment-39321867
gulp.task('components', function() {
  return gulp.src(['bower_components/normalize.css/normalize.css'])
  .pipe(plugins.rename('_normalize.scss'))
  .pipe(gulp.dest('assets/src/scss'));
});

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////


// https://github.com/sass/sass/issues/556#issuecomment-50825607
gulp.task('cssToSass', function() {
  return gulp.src(paths.cssToSass.src)
	.pipe(cached('cssToSass'))
	.pipe(rename(function(path) {
	  path.basename = '_' + path.basename;
	  path.extname = '.scss';
	}))
	.pipe(gulp.dest(paths.cssToSass.dest));
});

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////


// https://github.com/sass/sass/issues/556#issuecomment-50837783
// https://github.com/yuguo/gulp-import-css
gulp.task('styles', function () {
	return gulp.src('app/scss/app.scss')
		.pipe(sass())
		.pipe(importCss())
		.pipe(gulp.dest('dist/styles'));
});

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////


// ALSO SEE: https://gist.github.com/geminorum/35b4e1c5f0e7d6a4a914
// https://github.com/yuguo/gulp-import-css

////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////
// RENAME :

// SEE: https://github.com/hparra/gulp-rename/blob/master/test/rename.spec.js

// http://stackoverflow.com/a/22147264/4864081

// gulp.src("./partials/**/*.hmtl")
// .pipe(rename(function (path) {
  // path.suffix += ".min";
// }))
// .pipe(gulp.dest("./dist"));

gulp.task('compress', function() {
  gulp.src('./partials/**/*.html')
	.pipe(htmlmin())
	.pipe(gulp.dest('dist'))
});

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// https://github.com/postcss/autoprefixer

// gulp.task('autoprefixer', function () {
// 	return gulp.src('./src/*.css')
// 		.pipe(sourcemaps.init())
// 		.pipe(postcss([ autoprefixer({ browsers: ['last 2 versions'] }) ]))
// 		.pipe(sourcemaps.write('.'))
// 		.pipe(gulp.dest('./dest'));
// });

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

gulp.task('default', ['compass', 'watch']);
