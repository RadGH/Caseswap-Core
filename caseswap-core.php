<?php
/*
Plugin Name: CaseSwap Core
Version: 1.0
Plugin URI: http://www.caseswap.com/
Description: Integrates Contact Form 7 and Paid Memberships Pro into a subscription-based "submit-a-case" system where visitors can submit their case to paying members of the website.
Author: Radley Sustaire
Author URI: mailto:radleygh@gmail.com

Copyright 2015 CaseSwap.com
*/

define( 'CSCore_URL', plugins_url() );
define( 'CSCore_PATH', dirname(__FILE__) );

require_once( CSCore_PATH . '/classes/caseswap.php' );

global $CSCore;

// Create CaseSwap Core object, which will automatically initialize itself.
$CSCore = new CSCore();