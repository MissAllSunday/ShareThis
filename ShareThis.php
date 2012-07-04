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
	function __construct($url, $msgID = false)
	{
		global $context, $modSettings;

		if (!empty($url))
			$this->url = trim($url);

		if (!empty($msgID))
			$this->msgID = $msgID;

		elseif (empty($url))
			return;

		/* Does the user wants to use a custom via @username setting? */
		if (!empty($modSettings['share_twitter_options_via']))
			$this->twitter_via = $modSettings['share_twitter_options_via'];

		/* No?  then use the forum name */
		else
			$this->twitter_via = str_replace(' ', '_', $context['forum_name']);
	}

	/**
	 * Holds the initial data for the buttons
	 *
	 * Each item in the array must contain a name, url, code and enable value
	 * @global array $modSettings SMF's modSettings array
	 * @global array $context SMF's context array
	 * @global array $txt SMF's language strings array
	 * @access public
	 * @return void
	 */
	public function CreateButtons($custom = array())
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
			'code' => '<a href="https://twitter.com/share" class="twitter-share-button" data-url="'. $this->url .'" data-text="'. $context['page_title_html_safe'] .'" rel="canonical" data-via="'. $this->twitter_via .'">'. $txt['tweet_name'] .'</a>',
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

		/* Are there any custom buttons to add? */
		if ($custom)
			$this->AddCustomButton($custom);

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
	 * This method lets you add custom buttons to the temp array before the process begins
	 *
	 * @access private
	 * @return void
	 */
	public function AddCustomButton($array)
	{
		if (empty($array))
			return false;

		/* Let's append the url to the button */
		if ($this->IsMulti($array))
			foreach ($array as $a)
			{
				$a['url'] = $this->url;
				$a['code'] = sprintf($a['code'], $this->url);

				/* Add the button to the temp array */
				$this->temp[] = $a;
			}

		else
		{
			$array['url'] = $this->url;
			$array['code'] = sprintf($array['code'], $this->url);

			/* Add the button to the temp array */
			$this->temp[] = $array;
		}
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
	 * @return string
	 */
	public function Display()
	{
		return $this->HTML();
	}

	/**
	 * Displays the HTML properly formatted for custom display
	 *
	 * @access public
	 * @return string
	 */
	public function CustomDisplay()
	{
		return $this->HTML(true);
	}

	/**
	 * Gives format to the buttons via a HTML list, adds the Javascript and return the final product
	 *
	 * @access public
	 * @return array the html ready to be used
	 */
	private function HTML($custom = false)
	{
		$this->final .= ($custom ? '' : $this->JS()) .'<div class="sharethis_'. ($custom ? '' : $this->msgID) .'" id="sharethis'. ($custom ? 'custom' : '') .'"><ul>';

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
		global $modSettings;

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
		}

		/* We just need the overflow */
		else
			$js = '<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				jQuery(function()
				{
					jQuery("#msg_'. $this->msgID .'").css("overflow-y", "hidden");
				});
			});
			</script>';

		return $js = str_replace(array("\r\n", "\r", "\n", "\t"), '', $js);
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
			array('int', 'share_options_show_space', 'size' => 3, 'subtext' => $txt['share_options_show_space_sub']),
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

			/* If for some reason the user put something like this:  12px, then remove the "px" part, we want only numbers! */
			if (!empty($_POST['share_options_show_space']))
				$_POST['share_options_show_space'] = preg_replace('/[^0-9,]/', '', $_POST['share_options_show_space']);

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
			array('check', 'share_addthismessages_enable', 'subtext' => $txt['share_addthismessages_enable_sub']),
			'',
			$txt['share_twitter_options_dec'],
			array('text', 'share_twitter_options_via', 'size' => 20, 'subtext' => $txt['share_twitter_options_via_sub']),
		);

		if ($return_config)
			return $config_vars;

		/* Page settings */
		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=buttons;save';
		$context['page_title'] = $txt['share_default_menu'];

		/* Save */
		if (isset($_GET['save']))
		{
			/* Just an extra check... */
			if (isset($_POST['share_twitter_options_via']))
				$_POST['share_twitter_options_via'] = str_replace('@', '', $_POST['share_twitter_options_via']);

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
		if (!empty($modSettings['share_buttons_enable']) || !empty($modSettings['share_addthisbutton_enable']))
			$context['html_headers'] .= '
<style type="text/css">
#sharethis
{
	'. (!empty($modSettings['share_disable_jquery']) ? 'display:visible' : 'display:none;') . '
	position:relative;
	top:5px;
	left:5px;
	z-index:100;
	min-height:30px;
	'. (!empty($modSettings['share_options_show_space']) && !empty($modSettings['share_options_position']) && $modSettings['share_options_position'] == 'below' ? 'padding-top: '. $modSettings['share_options_show_space'] .'px' : '') . '
}
#sharethiscustom
{
	position:relative;
	top:5px;
	left:5px;
	z-index:100;
	min-height:30px;
}

#sharethis ul, #sharethiscustom ul
{
	margin: 0;
	padding: 0;
	list-style-type: none;
	text-align: left;
	list-style-position:inside !important;
}
#sharethis ul li, #sharethiscustom ul li
{
	display: inline;
}
'. (!empty($modSettings['share_addthismessages_enable']) ? '
#sharethis ul li.sharethis_addthis, #sharethiscustom ul li.sharethis_addthis
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

		/* We need an array */
		if (!empty($modSettings['share_options_boards']))
			$share_options_boards = explode(',', $modSettings['share_options_boards']);

		else
			$share_options_boards = array();

		/* By default this is set to disable */
		$AddThisEnable = false;

		/* Are we in the profile action without any subaction? */
		if ($context['current_action'] == 'profile' && !isset($_GET['sa']) && !isset($_GET['area']))
			$AddThisEnable = true;

		/* Are we in a topic? */
		if (isset($_GET['topic']) && !isset($_GET['sa']) && !isset($_GET['action']) && isset($context['current_board']) && !in_array($context['current_board'], $share_options_boards))
			$AddThisEnable = true;

		/* Are we in a board? */
		if (isset($_GET['board']) && !isset($_GET['sa']) && !isset($_GET['action']) && isset($context['current_board']) && !in_array($context['current_board'], $share_options_boards))
			$AddThisEnable = true;

		/* Are we in the BoardIndex? */
		if (!isset($_GET['action']) && !isset($_GET['topic']) && !isset($_GET['board']))
			$AddThisEnable = true;

		if ($AddThisEnable && !empty($modSettings['share_addthisbutton_enable']))
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

	private function IsMulti($a)
	{
		foreach ($a as $v)
			if (is_array($v))
				return true;

		return false;
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