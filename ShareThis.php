<?php

/**
 * @package ShareThis Topic mod
 * @version 4.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2012 Suki
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

	/* Wrapper function for SMF */
	function ShareThis_SubActions(){ShareThis::SubActions();};

class ShareThis
{
	private $build = array();
	private $msgID;
	private $url;
	private $temp = array();
	private $html;
	private $prebuttons = array();
	protected $final;
	protected $enable;
	private $page_title_html_safe;
	private $forum_name;

	function __construct($url, $msgID)
	{
		if (!empty($url))
			$this->url = trim($url);

		if (!empty($msgID))
			$this->msgID = $msgID;

		/* @todo for 4.1, build a method to easily use the buttons on external pages/modules/plugins etc */
		elseif (empty($url) || empty($msgID))
			return;

		global $context;

		$this->page_title_html_safe = $context['page_title_html_safe'];
		$this->forum_name = str_replace(' ', '_', $context['forum_name']);
	}

	/* Create the buttons with some parameters as an array
	 * @todo make a method the add custom buttons to the array*/
	public function CreateButtons()
	{
		global $modSettings, $txt;

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
			'code' => '<a href="https://twitter.com/share" class="twitter-share-button" data-url="'. $this->url .'" data-text="'. $this->page_title_html_safe .'" rel="canonical" data-via="'. $this->forum_name .'">'. $txt['tweet_name'] .'</a>',
			'enable' => !empty($modSettings['share_twibutton_enable']) ? 1 : 0,
		);

		/* Google +1 */
		$this->temp['google'] = array(
			'name' => 'google',
			'url' => $this->url,
			'code' => '<g:plusone size="medium" href="'. $this->url .'"></g:plusone>',
			'enable' => !empty($modSettings['share_plusone_enable']) ? 1 : 0
		);

		/* Add the buttons */
		foreach($this->temp as $add)
			$this->AddButton($add);
	}

	/* Identifies how many buttons are in the array */
	private function CountButtons()
	{
		return count($this->build);
	}

	/* Adds a button to the build array and set a unique id*/
	private function AddButton($button)
	{
		$button['id'] = $this->CountButtons();
		$this->build[$button['id']] = $button;
	}

	/* If the button is not enable then don't show it */
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

	/* Use this if you want to show a single button, this will return raw code */
	public function GetSingleButton($button)
	{
		if (in_array($button, $this->Enable()))
			return $this->prebuttons[$button]['code'];

		else
			return false;
	}

	/* Displays the HTML properly formatted */
	public function Display()
	{
		return $this->HTML();
	}

	/* Format the buttons and return the HTML */
	private function HTML()
	{
		$this->final .= $this->JS() .'<div class="sharethis_'. $this->msgID .'" id="sharethis"><ul>';

		foreach($this->Enable() as $a)
			$this->final .= '<li class="sharethis_'. $a['name'] .'">'. $a['code'] .'</li>';

		$this->final .= '</div>';

		return $this->final;
	}

	/* Build the JavaScript for each message */
	private function JS()
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

	/* Admin stuff and hooks */
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

	static function GeneralShareSettings($return_config = false)
	{
		global $txt, $scripturl, $context, $sourcedir;

		loadLanguage('ShareThis');

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
			array('check', 'share_all_messages', 'subtext' => $txt['share_all_messages_sub']),
			array('text', 'share_options_boards', 'size' => 36, 'subtext' => $txt['share_options_boards_sub']),
			'',
			array('check', 'share_addthisbutton_enable', 'subtext' => $txt['share_addthisbutton_enable_sub']),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=general;save';
		$context['page_title'] = $txt['share_default_menu'];

		if (isset($_GET['save']))
		{
			if (!empty($_POST['share_options_boards']))
			{
				$share_options_boards = explode(',', preg_replace('/[^0-9,]/', '', $_POST['share_options_boards']));

				foreach ($share_options_boards as $key => $value)
					if ($value == '')
						unset($share_options_boards[$key]);

				$_POST['share_options_boards'] = implode(',', $share_options_boards);
			}

			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=general');
		}

		prepareDBSettingContext($config_vars);

	}

	static function ButtonsShareSettings($return_config = false)
	{
		global $txt, $scripturl, $context, $sourcedir;

		loadLanguage('ShareThis');

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
			array('check', 'share_buttons_enable', 'subtext' => $txt['share_buttons_enable_sub']),
			'',
			array('check', 'share_plusone_enable'),
			array('check', 'share_twibutton_enable'),
			array('check', 'share_likebutton_enable'),
		);

		if ($return_config)
			return $config_vars;

		$context['post_url'] = $scripturl . '?action=admin;area=sharethis;sa=buttons;save';
		$context['page_title'] = $txt['share_default_menu'];

		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=sharethis;sa=buttons');
		}
		prepareDBSettingContext($config_vars);
	}

	/* DUH! WINNING! */
	static function ShareThisWho()
	{
		$MAS = '<a href="http://missallsunday.com" title="Free SMF Mods">Share This Topic mod &copy Suki</a>';

		return $MAS;
	}

	static function Headers()
	{
		global $modSettings, $context, $settings;

		/* JQuery here please */
		if (!empty($modSettings['share_buttons_enable']) || !empty($modSettings['share_addthisbutton_enable']))
			$context['html_headers'] .= '
			<!-- Share This Topic Mod -->
<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/jquery.hoverIntent.minified.js"></script>
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
#sharethis ul li {
	display: inline;
}
.sharethis_twitter, .sharethis_google
{
'. (!empty($modSettings['share_likebutton_enable']) ? '
	position: relative;
	top: -60px;
' : '') .'
}

</style>';

		if(!empty($modSettings['share_plusone_enable']) && !empty($modSettings['share_buttons_enable']))
			$context['html_headers'] .= '<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>';

		if (!empty($modSettings['share_twibutton_enable']) && !empty($modSettings['share_buttons_enable']))
			$context['html_headers'] .= '<script type="text/javascript">!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';

		/* @todo Let the admin decide what actions they want to share */
		/* I'ts more easy to identify the actions where the script will be showed rather than the actions where it won't be showed */
		$addthis_show = array(
			'calendar',
			'display',
			'profile'
		);

		if (!empty($modSettings['share_addthisbutton_enable']))
		{
			if (in_array($context['current_action'], $addthis_show) || isset($_REQUEST['topic']) || !isset($_REQUEST['action']))
				$context['html_headers'] .= '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4f0f3b943805e0af"></script>
				<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				jQuery(function()
				{
					jQuery(\'.pagesection\').append(\'<a class="addthis_button" href="http://www.addthis.com/bookmark.php?v=250&amp;pubid=xa-4f0f51eb17eb2a19"><img src="http://s7.addthis.com/static/btn/v2/lg-share-en.gif" width="125" height="16" style="border:0"/></a>\');
				});
			});
			</script>';
		}
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