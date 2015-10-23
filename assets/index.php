<?php

// NOTE: SAMPLE

include '../../gtheme_03/assets/functions.php';
include '../../gtheme_03/assets/headers-css.php';

ob_start( 'minify_css' );
	include 'css.php';
ob_end_flush();
