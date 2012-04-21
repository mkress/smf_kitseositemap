<?php
/*******************************************************************************
* SEO Sitemap © 2012, Markus Kress - Kress.IT							       *
********************************************************************************
* hooks.php																	   *
********************************************************************************
* License http://creativecommons.org/licenses/by-sa/3.0/deed.de CC BY-SA 	   *
* Support for this software  http://kress.it and							   *
* http://custom.simplemachines.org/mods/index.php?mod=3393					   *
*******************************************************************************/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin privileges required.');
	
$hooks = array(
	'integrate_pre_include' => '$sourcedir/Subs-KitSitemap.php',
	'integrate_load_theme' => 'kit_sitemap_load_theme'
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';

else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);
	
if (SMF == 'SSI')
	echo 'Database changes are complete! Please wait...';