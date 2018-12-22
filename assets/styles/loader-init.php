<?php

error_reporting(0);

$debug   = isset( $_GET['debug'] );
$version = isset( $_GET['ver'] ) ? $_GET['ver'] : FALSE;
$ltr     = isset( $_GET['ltr'] ) || ( isset( $_GET['dir'] ) && 'ltr' == $_GET['dir'] );
