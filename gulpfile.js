var gulp    = require('gulp'),
	//sass    = require('gulp-sass'),
	compass = require('gulp-compass'),
	plumber = require('gulp-plumber'),
	notify  = require('gulp-notify'),
	minifyCSS = require('gulp-minify-css');
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

// gulp.task('sass', function () {
// 	gulp.src('./root/assets/stylesheets/style.scss')
// 	.pipe(plumber(plumberErrorHandler))
// 	.pipe(sass({
// 		// includePaths: require('node-neat').includePaths
// 		// includePaths: require('node-bourbon').with('other/path', 'another/path')
//     }))
//     .pipe(gulp.dest('./root/assets/css'));
// });

gulp.task('default', ['compass', 'watch']);
