<?php

/**
 * @package ShareThis Topic mod
 * @version 4.2
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

/*
 * Version: MPL 2.0
 *
 * This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0.
 * If a copy of the MPL was not distributed with this file,
 * You can obtain one at http://mozilla.org/MPL/2.0/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');
	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	/* This mod needs php 5.2 or greater, sorry */
	ShareThisCheck();

	$hooks = array(
		'integrate_pre_include' => '$sourcedir/ShareThis.php',
		'integrate_admin_areas' => 'ShareThis::Admin',
	);

	$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);

	function ShareThisCheck()
	{
		if (version_compare(PHP_VERSION, '5.2.0', '<'))
			fatal_error('This mod needs PHP 5.2 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}