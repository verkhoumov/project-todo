'use strict';

const gulp       = require('gulp');
const del        = require('del');
const uglify     = require('gulp-uglify');
const prefixer   = require('gulp-autoprefixer');
const cleaner    = require('gulp-clean-css');
const browserify = require('browserify');
const source     = require('vinyl-source-stream');
const buffer     = require('vinyl-buffer');
const gutil      = require('gulp-util');
const sourcemaps = require('gulp-sourcemaps');
const tap        = require('gulp-tap');
const cssimport  = require('gulp-cssimport');

// Параметры.
const CONFIG = {
	prefixer: {
		browsers: ['last 30 versions']
	},
	cleaner: {
		debug: true,
		inline: 'all',
		level: {
			1: {
				specialComments: 0
			}
		}
	},
	browserify: {
		debug: true
	},
	sourcemaps: {
		options: {
			loadMaps: true
		},
		write: './maps'
	},
	cssimport: {
		//...
	}
};

// Пути файлов.
const PATH = {
	// Пути для очистки директорий.
	clean: {
		js: './build/js/**',
		css: './build/css/**',
		upload: ['../www/resources/(js|css)/**', '!../www/resources/(js|css)']
	},

	// Местоположение исходных файлов для последующей обработки.
	src: {
		js: './src/js/common.js',
		css: './src/css/common.css'
	},

	// Пути сохранения обработанных файлов.
	build: {
		js: './build/js',
		css: './build/css'
	},

	// Пути выгрузки обработанных файлов для production.
	upload: {
		from: {
			js: ['./build/+(js)/common.js', './build/+(js)/+(maps)/common.js.map'],
			css: './build/+(css)/common.css'
		},
		to: '../www/resources'
	},

	// Отслеживание файлов.
	watch: {
		js: './src/js/**/*.js',
		css: './src/css/**/*.css'
	}
};


/**
 *  Очистка директорий.
 */
gulp.task('clean:js', () => {
	return del(PATH.clean.js);
});

gulp.task('clean:css', () => {
	return del(PATH.clean.css);
});

gulp.task('clean:upload', () => {
	return del(PATH.clean.upload, {
		force: true
	});
});

// Очистка всех директорий.
gulp.task('clean', 
	gulp.parallel('clean:js', 'clean:css', 'clean:upload', (done) => {
		done();
	})
);


/**
 *  Обработка JavaScript.
 */
gulp.task('js:build', (done) => {
	return gulp.src(PATH.src.js, {read: false})
		.pipe(tap((file) => {
			file.contents = browserify(file.path, {
				debug: true,
				standalone: file.basename
			}).bundle();
		}))
		.pipe(buffer())
		.pipe(sourcemaps.init(CONFIG.sourcemaps.options))
		.pipe(uglify())
			.on('error', gutil.log)
		.pipe(sourcemaps.write(CONFIG.sourcemaps.write))
		.pipe(gulp.dest(PATH.build.js));
});

gulp.task('js', 
	gulp.series('clean:js', 'js:build', (done) => {
		done();
	})
);


/**
 *  Обработка CSS-стилей.
 */
// Обработчик.
let build_css = (from, to) => {
	let build = gulp.src(from)
		.pipe(cssimport(CONFIG.cssimport))
		.pipe(prefixer(CONFIG.prefixer))
		.pipe(cleaner(CONFIG.cleaner));

	if (to) {
		build = build.pipe(gulp.dest(to));
	}

	return build;
};

gulp.task('css:build', () => {
	return build_css(PATH.src.css, PATH.build.css);
});

gulp.task('css', 
	gulp.series('clean:css', 'css:build', (done) => {
		done();
	})
);


/**
 *  Обработка всех файлов.
 */
gulp.task('build', 
	gulp.parallel('js', 'css', (done) => {
		done();
	})
);


/**
 *  Выгрузка обработанных файлов в папку с проектом.
 */
// Обработчик выгрузки.
let upload = (type) => {
	return gulp.src(PATH.upload.from[type]).pipe(gulp.dest(PATH.upload.to));
};

// Выгрузка скриптов.
gulp.task('upload:js', () => {
	return upload('js');
});

// Выгрузка стилей.
gulp.task('upload:css', () => {
	return upload('css');
});

// Выгрузка всех файлов.
gulp.task('upload',
	gulp.series('clean:upload',
		gulp.parallel('upload:js', 'upload:css', (done) => {
			done();
		})
	)
);


/**
 *  Отслеживание изменений в файлах и преждевременная выгрузка.
 */
gulp.task('watch', () => {
	gulp.watch(PATH.watch.js, gulp.series('js', 'upload:js'));
	gulp.watch(PATH.watch.css, gulp.series('css', 'upload:css'));
});


/**
 *  Задача по-умолчанию.
 */
gulp.task('default', 
	gulp.series('build', 'upload', 'watch', (done) => {
		done();
	})
);