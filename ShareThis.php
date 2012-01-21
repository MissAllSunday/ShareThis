<?php

/**
 * @package ShareThis Topic mod
 * @version 4.0
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

if (!defined('SMF'))
	die('Hacking attempt...');

	/**
	 * Wrapper function
	 *
	 * SMF cannot handle static methods being called via a variable: $static_method();
	 */
	function ShareThis_SubActions(){ShareThis::SubActions();};

/**
 * Share This Class
 *
 * Main class. handles everything
 * @package ShareThis Topic mod
 * @todo Build a method to easily use the buttons on external pages/modules/plugins etc
 * @todo build a method to add custom buttons to the $temp array
 */
class ShareThis
{
	/**
	 * @var array Gathers the buttons with a unique ID
	 * @access private
	 */
	private $build = array();
	/**
	 * @var int The message ID, used for identification.
	 * @access private
	 */
	private $msgID;
	/**
	 * @var string The url to be used by the buttons
	 * @access private
	 */
	private $url;
	/**
	 * @var array Used to store the initial data for the buttons.
	 * @access private
	 */
	private $temp = array();
	/**
	 * @var array Holds only the usable buttons (those that are enable).
	 * @access private
	 */
	private $prebuttons = array();
	/**
	 * @var string Holds the final HTML that will be displayed.
	 * @access protected
	 */
	protected $final;

	/**
	 * Initialize the mod and it's settings.
	 *
	 * @global array $modSettings SMF's modSettings variable
	 * @global array $context SMF's context variable
	 * @param string $url The url to be used by the buttons
	 * @param int $msgID The message's unique ID
	 * @return void
	 */
	function __construct($url, $msgID)
	{
		global $modSettings, $context;

		if (!empty($url))
			$this->url = trim($url);

		if (!empty($msgID))
			$this->msgID = $msgID;

		elseif (empty($url) || empty($msgID))
			return;

		/* Replace the spaces (if any) in the forum name to  make a cool "via @my_forum_name" for twitter */
		$this->forum_name = str_replace(' ', '_', $context['forum_name']);
	}

	/**
	 * Holds the initial data for the buttons
	 *
	 * Each item in the array must contain a name, url, code and enable value
	 * @global array $modSettings SMF's modSettings array
	 * @global array $context SMF's context array
	 * @global array $txt SMF's language strings array
	 * @todo call a method to add custom buttons to the $temp array
	 * @access public
	 * @return void
	 */
	public function CreateButtons()
	{
		global $modSettings, $txt, $context;

		/* Call the language file */
		loadLanguage('ShareThis');

		/* Facebook */
		$this->temp['facebook'] = array(
			'name' => 'facebook',
			'url' => $this->url,
			'code' => '<iframe src="http://www.facebook.com/plugins/like.php?href='. $this->url .'&amp;layout=standard&amp;show_faces=false$amp;send=true&amp;width=350&amp;action=like&amp;colorscheme=light&amp;height=:80" scrolling="no" frameborder="0" style="border:none; overflow:visible; width:350px; height:80px;" allowTransparency="true"></iframe>',
			'enable' => !empty($modSettings['share_likebutton_enable']) ? 1 : 0
		);

		/* Twitter */
		$this->temp['twitter'] = array(
			'name' => 'twitter',
			'url' => $this->url,
			'code' => '<a href="https://twitter.com/share" class="twitter-share-button" data-url="'. $this->url .'" data-text="'. $context['page_title_html_safe'] .'" rel="canonical" data-via="'. $this->forum_name .'">'. $txt['tweet_name'] .'</a>',
			'enable' => !empty($modSettings['share_twibutton_enable']) ? 1 : 0,
		);

		/* Google +1 */
		$this->temp['google'] = array(
			'name' => 'google',
			'url' => $this->url,
			'code' => '<g:plusone size="medium" href="'. $this->url .'"></g:plusone>',
			'enable' => !empty($modSettings['share_plusone_enable']) ? 1 : 0
		);

		/* AddThis script */
		$this->temp['addthis'] = array(
			'name' => 'addthis',
			'url' => $this->url,
			'code' => '<span class="addthis_toolbox addthis_default_style"addthis:url="'. $this->url .'"><a class="addthis_button"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" style="border:0"/></a></a></span>',
			'enable' => !empty($modSettings['share_addthismessages_enable']) ? 1 : 0
		);

		/* Add the buttons */
		foreach($this->temp as $add)
			$this->AddButton($add);

		/* Done? then back to zero */
		$this->temp = array();
	}

	/**
	 * Identifies how many buttons are in $build and returns the number
	 *
	 * @access private
	 * @return int The number of buttons
	 */
	private function CountButtons()
	{
		return count($this->build);
	}

	/**
	 * Adds a button to the build array and set a unique id
	 *
	 * @access private
	 * @return void
	 */
	private function AddButton($button)
	{
		$button['id'] = $this->CountButtons();
		$this->build[$button['id']] = $button;
	}


	/**
	 * Checks the $build array and unset the values that aren't enable by the Admin
	 *
	 * If the button is not enable then don't show it
	 * @access private
	 * @return array A new array holding only the enable buttons
	 */
	private function Enable()
	{
		foreach($this->build as $button)
		{
			if ($button['enable'] == 0)
				unset ($button);

			else
				$this->prebuttons[$button['name']] = $button;
		}

		return $this->prebuttons;
	}

	/**
	 * Returns a single button's code
	 *
	 * Use this if you want to show a single button, this will return raw code without any format, you must specify the name of the button
	 * you want to get, if a button does not exist it will return false
	 * @access public
	 * @param string The name of the button you want
	 * @return string raw code for the button
	 */
	public function GetSingleButton($button)
	{
		if (in_array($button, $this->Enable()))
			return $this->prebuttons[$button]['code'];

		else
			return false;
	}

	/**
	 * Displays the HTML properly formatted
	 *
	 * @access public
	 * @return void
	 */
	public function Display()
	{
		return $this->HTML();
	}

	/**
	 * Gives format to the buttons via a HTML list, adds the Javascript and return the final product
	 *
	 * @access public
	 * @return array the html ready to be used
	 */
	private function HTML()
	{
		$this->final .= $this->JS() .'<div class="sharethis_'. $this->msgID .'" id="sharethis"><ul>';

		foreach($this->Enable() as $a)
			$this->final .= '<li class="sharethis_'. $a['name'] .'">'. $a['code'] .'</li>';

		$this->final .= '</div>';

		return $this->final;
	}

	/**
	 * Build the JavaScript for each message
	 *
	 * @access private
	 * @global array $modSettings
	 * @return string the JavaScript code without any spaces or tabs.
	 */
	private function JS()
	{
		if (empty($modSettings['share_disable_jquery']))
		{
			$js = '<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				jQuery(function()
				{
					jQuery("#msg_'. $this->msgID .'").css("min-height", "50px");
					jQuery("#msg_'. $this->msgID .'").hoverIntent(function()
					{
					jQuery("#msg_'. $this->msgID .'").css("overflow-y", "hidden");
					jQuery(".sharethis_'. $this->msgID .'").delay(100).fadeIn();
					},function(){
							jQuery(".sharethis_'. $this->msgID .'").delay(300).fadeOut();
						});
				});
			});
			</script>';

			return $js = str_replace(array("\r\n", "\r", "\n", "\t"), '', $js);
		}

		else
			return $js = '';
	}

	/**
	 * Builds the admin button via hooks
	 *
	 * @access public
	 * @static
	 * @param array The admin menu
	 * @return void
	 */
	static function Admin(&$admin_areas)
	{
		global $txt;

		loadLanguage('ShareThis');

		$admin_areas['config']['areas']['sharethis'] = array(
					'label' => $txt['share_default_menu'],
					'file' => 'ShareThis.php',
					'function' => 'ShareThis_SubActions',
					'icon' => 'posts.gif',
					'subsections' => array(
						'general' => array($txt['share_general_settings']),
						'buttons' => array($txt['share_buttons_settings'])
				),
		);
	}

	/**
	 * Creates the pages for the admin panel via hooks
	 *
	 * @access public
	 * @static
	 * @param boolean
	 * @return void
	 */
	static function SubActions($return_config = false)
	{
		global $txt, $scripturl, $context, $sourcedir;

		loadLanguage('ShareThis');

		require_once($sourcedir . '/ManageSettings.php');

		$context['page_title'] = $txt['share_default_menu'];

		$subActions = array(
			'general' => 'ShareThis::GeneralShareSettings',
			'buttons' => 'ShareThis::ButtonsShareSettings'
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['share_default_menu'],
			'description' => $txt['share_admin_panel_desc'],
			'tabs' => array(
				'general' => array(
				),
				'buttons' => array(
				)
			),
		);

		call_user_func($subActions[$_REQUEST['sa']]);

	}

	/**
	 * The General settings page
	 *
	 * @access public
	 * @static
	 * @param boolean
	 * @return void
	 */
	static function GeneralShareSettings($return_config = false)
	{
		global $txt, $scripturl, $context, $sourcedir;

		loadLanguage('ShareThis');

		/* We need this */
		require_once($sourcedir . '/ManageServer.php');

		/* Generate the settings */
		$config_vars = array(
			array('check', 'share_all_messages', 'subtext' => $txt['share_all_messages_sub']),
			array('check', 'share_disable_jquery', 'subtext' => $txt['share_disable_jquery_sub']),
			array('text', 'share_options_boards', 'size' => 36, 'subtext' => $txt['share_options_boards_sub']),
			array(
				'select',
				'share_options_position', array(
					'below' => $txt['share_options_position_below'],
					'above' => $txt['share_options_position_above']
				),
				'subtext' => $txt['share_options_position_sub']
			),
			'',
			array('check', 'share_addthisbutton_enable', 'subtext' => $txt['share_addthisbutton_enable_sub']),
		);

		if ($return_config)
			return $config_vars;

		/* Set some settings for the page */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=general;save';
		$context['page_title'] = $txt['share_default_menu'];

		if (isset($_GET['save']))
		{
			/* Clean the boards var, we only want integers and nothing else! */
			if (!empty($_POST['share_options_boards']))
			{
				$share_options_boards = explode(',', preg_replace('/[^0-9,]/', '', $_POST['share_options_boards']));

				foreach ($share_options_boards as $key => $value)
					if ($value == '')
						unset($share_options_boards[$key]);

				$_POST['share_options_boards'] = implode(',', $share_options_boards);
			}

			/* Save the settings */
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=general');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * The Buttons settings page
	 *
	 * @access public
	 * @static
	 * @param boolean
	 * @return void
	 */
	static function ButtonsShareSettings($return_config = false)
	{
		global $txt, $scripturl, $context, $sourcedir;

		loadLanguage('ShareThis');

		/* We need this */
		require_once($sourcedir . '/ManageServer.php');

		/* Generate the settings */
		$config_vars = array(
			array('check', 'share_buttons_enable', 'subtext' => $txt['share_buttons_enable_sub']),
			'',
			array('check', 'share_plusone_enable'),
			array('check', 'share_twibutton_enable'),
			array('check', 'share_likebutton_enable'),
			array('check', 'share_addthismessages_enable', 'subtext' => $txt['share_addthismessages_enable_sub'])
		);

		if ($return_config)
			return $config_vars;

		/* Page settings */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=buttons;save';
		$context['page_title'] = $txt['share_default_menu'];

		/* Save */
		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=buttons');
		}
		prepareDBSettingContext($config_vars);
	}

	/**
	 * Shows the mod's author copyright
	 *
	 * Show the copyright in the credits action,  ?action=credits
	 * @access public
	 * @static
	 * @return string The copyright link
	 */
	static function ShareThisWho()
	{
		$MAS = '<a href="http://missallsunday.com" title="Free SMF Mods">Share This Topic mod &copy Suki</a>';

		return $MAS;
	}

	/**
	 * Set all the necessary CSS and JavaScript
	 *
	 * Via $context['html_headers'] that means no template edits.
	 * @access public
	 * @static
	 * @return void
	 */
	static function Headers()
	{
		global $modSettings, $context, $settings;

		/* JQuery here please */
		if (!empty($modSettings['share_buttons_enable']) || !empty($modSettings['share_addthisbutton_enable']))
			$context['html_headers'] .= '
			<!-- Share This Topic Mod -->
<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/jquery.hoverIntent.minified.js"></script>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4f0f3b943805e0af"></script>
';

		/* Don't show this if the mod is not enable */
		if (!empty($modSettings['share_buttons_enable']))
			$context['html_headers'] .= '
<style type="text/css">
#sharethis
{
	display:none;
	position:relative;
	top:5px;
	left:5px;
	z-index:100;
	min-height:30px;
}
#sharethis ul
{
	margin: 0;
	padding: 0;
	list-style-type: none;
	text-align: left;
	list-style-position:inside !important;
}
#sharethis ul li
{
	display: inline;
}
'. (!empty($modSettings['share_addthismessages_enable']) ? '
#sharethis ul li.sharethis_addthis
{
	float:left;
	margin-right:15px;
}
' : '') .'


.sharethis_twitter, .sharethis_google
{
'. (!empty($modSettings['share_likebutton_enable']) ? '
	position: relative;
	top: -60px;
' : '') .'
}

'. (!empty($modSettings['share_addthisbutton_enable']) ? '
.sharethis_addthis_script
{
	float: right;
	display:inline;
	position:relative;
	top: -20px;
}
' : '') .'

</style>';

		if(!empty($modSettings['share_plusone_enable']) && !empty($modSettings['share_buttons_enable']))
			$context['html_headers'] .= '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>';

		if (!empty($modSettings['share_twibutton_enable']) && !empty($modSettings['share_buttons_enable']))
			$context['html_headers'] .= '<script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';

		/* @todo Let the admin decide what actions they want to share */
		/* I'ts more easy to identify the actions where the script will be showed rather than the actions where it won't be showed */
		$addthis_show = array(
			'display',
			'profile'
		);

		/* We need an array */
		if (!empty($modSettings['share_options_boards']))
			$share_options_boards = explode(',', $modSettings['share_options_boards']);

		else
			$share_options_boards = array();

		/* Show the script on a board or topic only if it isn't denied to show in the settings */
		if (!empty($modSettings['share_addthisbutton_enable']) && !empty($context['current_board']) && !in_array($context['current_board'], $share_options_boards) && isset($_REQUEST['topic']) || isset($_REQUEST['board']))
			$context['html_headers'] .= '
			<script type="text/javascript">
		jQuery(document).ready(function($)
		{
			jQuery(function()
			{
				jQuery(\'.navigate_section\').append(\'<span class="sharethis_addthis_script"><a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4f0f51eb17eb2a19"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" style="border:0"/></a></span>\');
			});
		});
		</script>';

		/* We aren't in a board and we are on a OK action */
		elseif (!empty($modSettings['share_addthisbutton_enable']) && in_array($context['current_action'], $addthis_show) && empty($context['current_board']))
			$context['html_headers'] .= '
			<script type="text/javascript">
		jQuery(document).ready(function($)
		{
			jQuery(function()
			{
				jQuery(\'.navigate_section\').append(\'<span class="sharethis_addthis_script"><a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4f0f51eb17eb2a19"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" style="border:0"/></a></span>\');
			});
		});
		</script>';
	}
}
	/* Se que volverás el día
	 * En que ella te haga trizas
	 * Sin almohadas para llorar
	 * Pero si te has decidido
	 * Y no quieres más conmigo
	 * Nada ahora puede importar
	 * Porque sin ti
	 * El mundo ya me da igual... */