<?php

/**
 * @package ShareThis Topic mod
 * @version 4.2
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

global $txt;

/* General Settings */
$txt['share_default_menu'] = 'Share This Topic';
$txt['share_general_settings'] = 'General Settings';
$txt['share_admin_panel_desc'] = 'This is the admin panel for the Share This Topic mod, from here you can configure the mod to suit your needs.<br /> "General" page contains all the general settings for this mod.<br />
"Button Settings" Let\'s you enable/disable each button as well as enable/disable all the buttons at once.';
$txt['share_all_messages'] = 'Show the buttons on every message?';
$txt['share_all_messages_sub'] = 'If empty, the buttons and icons will be showed <strong>only</strong> in the first message.';
$txt['share_options_boards'] = 'Write the ID of the boards you <strong>DO NOT</strong> want to show the buttons.';
$txt['share_options_boards_sub'] = 'Comma separate, example: 1,2,3,4,  Leave it in blank to show the buttons on every board';
$txt['share_options_show_space'] = 'Increase the space between the message and the buttons.';
$txt['share_options_show_space_sub'] = 'This is only valid if you selected the "Below" option in the previous setting</br><strong>Use numbers only</strong>, the higher the number the larger will be the space between the message and the buttons.<br> Leave it in blank to use the default space.';
$txt['share_options_position'] = 'Select the position for the buttons.';
$txt['share_options_position_sub'] = 'Above: Will show the buttons before the actual message.<br />Below: Will show the buttons after the message.';
$txt['share_options_position_above'] = 'Above';
$txt['share_options_position_below'] = 'Below';
$txt['share_disable_jquery'] = 'Disable the jQuery effect.';
$txt['share_disable_jquery_sub'] = 'This will disable the jQuery effect making your buttons visible all the time.';

/* Buttons Settings */
$txt['share_buttons_settings'] = 'Button Settings';
$txt['share_buttons_enable'] = 'Enable the share buttons.';
$txt['share_buttons_enable_sub'] = 'This is the master setting for the buttons, check it to enable the buttons.';
$txt['share_likebutton_enable'] = 'Enable the Facebook Like Button.';
$txt['share_addthismessages_enable'] = 'Enable the AddThis script for the messages.';
$txt['share_addthismessages_enable_sub'] = 'This will include the AddThis script along with the rest of the buttons.';
$txt['share_addthisbutton_enable'] = 'Enable the AddThis script.';
$txt['share_addthisbutton_enable_sub'] = 'This will add the addthis script below the menu on the following pages:<br />-Profile<br />-Message Index<br />-Board Index<br />-Topic page.<br />
the Addthis script will share the entire page rather than an specific part of the forum.';
$txt['share_twibutton_enable'] = 'Enable the Tweet Button.';
$txt['share_plusone_enable'] = 'Enable the Google Plus Button.';
$txt['tweet_name'] = 'Tweet';

/* Twitter options */
$txt['share_twitter_options_dec'] = 'Twitter specific options';
$txt['share_twitter_options_via'] = 'Write the specific twitter username that will be used for the twitter button, if empty, it will show the forum name.';
$txt['share_twitter_options_via_sub'] = 'For example, if you type: MissAllSuki the twitter buttons will appear as via @MissAllSuki, don\'t include the @, just the username';