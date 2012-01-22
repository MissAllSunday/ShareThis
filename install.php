<?php

/**
 * @package ShareThis Topic mod
 * @version 4.1
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://www.mozilla.org/MPL/ MPL 1.1
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://innovatenotimitate.com/ code.
 *
 * The Initial Developer of the Original Code is
 * Arantor.
 * Portions created by the Initial Developer are Copyright (C)
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 * mirahalo <webmaster@oharascans.com>
 * Jessica González <missallsunday@simplemachines.org>
 *
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');
	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	$hooks = array(
		'integrate_pre_include' => '$boarddir/Sources/ShareThis.php',
		'integrate_admin_areas' => 'ShareThis::Admin',
	);

	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);