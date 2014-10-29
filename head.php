<?php defined( 'ABSPATH' ) or die( 'Restricted access' ); ?><!doctype html>
<?php gThemeWrap::html_open(); ?>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="HandheldFriendly" content="true" />
<title><?php gThemeWrap::html_title(); ?></title>
<?php wp_head(); ?>
</head><body <?php body_class(); ?>>
<?php do_action( 'template_body_top' ); ?>