<?php

// NOTE: SAMPLE

include '../../gtheme_03/assets/styles/loader-init.php';
include '../../gtheme_03/assets/styles/loader-functions.php';
include '../../gtheme_03/assets/styles/loader-headers.php';

ob_start( 'minify_css' );
	include 'css.php';
ob_end_flush();
