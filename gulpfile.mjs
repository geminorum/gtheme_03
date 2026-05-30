import gulp from 'gulp'; // eslint-disable-line import/no-duplicates
import * as dartSass from 'sass';
import gulpSass from 'gulp-sass';
import cssnano from 'cssnano';
import autoprefixer from 'autoprefixer';
import inlineSVG from 'postcss-inline-svg'; // https://github.com/TrySound/postcss-inline-svg
import rtlcss from 'rtlcss';
import log from 'fancy-log';
import plumber from 'gulp-plumber';
import postcss from 'gulp-postcss';
import rename from 'gulp-rename';
import livereload from 'gulp-livereload';
import { deleteSync } from 'del';

// @REF: https://www.stefanjudis.com/snippets/how-to-import-json-files-in-es-modules-node-js/
import { createRequire } from 'module';
const require = createRequire(import.meta.url);

const { src, dest, watch, series, parallel, task } = gulp;
const sass = gulpSass(dartSass);

const conf = require('./gulp.config.json');
// const pkg = require('./package.json');

function clean (done) {
  deleteSync(conf.input.clean);
  done();
}

function devSass () {
  return src(conf.input.sass)
    .pipe(plumber())
    .pipe(sass(conf.sass).on('error', sass.logError))
    .pipe(postcss([
      inlineSVG(),
      cssnano(conf.cssnano.dev),
      autoprefixer(conf.autoprefixer.dev)
    ]))
    .pipe(dest(conf.output.css))
    .pipe(postcss([
      rtlcss()
    ]))
    .pipe(rename({ suffix: '-rtl' }))
    .pipe(dest(conf.output.css)).on('error', log.error);
}

function devReload (done) {
  livereload.reload();
  done();
}

function devWatch () {
  livereload.listen();
  return watch(conf.input.watch, { ignoreInitial: false }, series(devSass, devReload));
}

function buildSass () {
  return src(conf.input.sass)
    .pipe(sass(conf.sass).on('error', sass.logError))
    .pipe(postcss([
      inlineSVG(),
      cssnano(conf.cssnano.build),
      autoprefixer(conf.autoprefixer.build)
    ]))
    .pipe(dest(conf.output.css)).on('error', log.error);
}

function buildSassRTL () {
  return src(conf.input.sass)
    .pipe(sass(conf.sass).on('error', sass.logError))
    .pipe(postcss([
      rtlcss(),
      inlineSVG(),
      cssnano(conf.cssnano.build),
      autoprefixer(conf.autoprefixer.build)
    ]))
    .pipe(rename({ suffix: '-rtl' }))
    .pipe(dest(conf.output.css)).on('error', log.error);
}

task('default', function (done) {
  log.info('Hi, I\'m Gulp!');
  log.info('Sass is:\n' + dartSass.info);
  done();
});

task('sass', devSass);
task('watch', devWatch);
task('build', series(clean, parallel(buildSass, buildSassRTL)));
task('clean', clean);
