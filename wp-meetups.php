<?php
/*
Plugin Name: WordPress Meetups
Version: 1.0 *Beta
Plugin URI: http://visible.company/wordpress-meetups-plugin/
Description: WordPress plugin for showing video hangouts and Twitter Q&A for live events. 
Author: JR Oakes
Author URI: https://visible.company/
Text Domain: wordpress-meetups
License: GPL v3

WordPress Meetups Plugin
Copyright (C) 2015, Visible Company - jroakes@gmail.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


//Define App Constants
define('APPNAME', "WordPress Meetups" );
define('APPVERSION', "1.0" );
define('MEETUPSLOADED', true);
define('MEETUPS_URI', plugin_dir_url( __FILE__ ) );
define('MEETUPS_DIR', plugin_dir_path( __FILE__) );
define('MEETUPS_PLUGIN_BASENAME', plugin_basename(__FILE__) );
define('MEETUPS_TEMPLATE_DIR', MEETUPS_DIR . "templates/" );
define('MEETUPS_DEFAULT_LIMIT', 100 );

//Load the plugin
require_once 'lib/load.php';
	