<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();
	
delete_option( 'wppush_notices_settings' );
