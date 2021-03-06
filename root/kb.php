<?php
/**
*
* @package phpBB phpBB3-Knowledgebase Mod (KB)
* @version $Id: kb.php $
* @copyright (c) 2009 Andreas Nexmann, Tom Martin
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/kb.' . $phpEx);
include($phpbb_root_path . 'includes/kb_auth.' . $phpEx);
include($phpbb_root_path . 'includes/constants_kb.' . $phpEx);
include($phpbb_root_path . 'includes/functions_kb.' . $phpEx);
include($phpbb_root_path . 'includes/functions_plugins_kb.' . $phpEx);

// Init session etc, we will just add lang files along the road
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/kb');

display_forums('', $config['load_moderators']);

// Lets start the install/update of the kb
// Automatically install or update if required
if(!isset($config['kb_version']) || version_compare($config['kb_version'], KB_VERSION, '<'))
{
	if(!$auth->acl_get('a_') || empty($user->data['is_registered']) || ((int) $user->data['user_type'] !== USER_FOUNDER))
	{
		// Stop any user in the middle of an install or update
		trigger_error('KB_NOT_ENABLE');
	}

	//include($phpbb_root_path . 'includes/functions_install_kb.' . $phpEx);
	if(!isset($config['kb_version']))
	{
		header('Location:' . $phpbb_root_path . 'kb_install/');//renamed to index.php so I only need to call the folder
	}

}

if (!isset($config['kb_enable']) || !$config['kb_enable'] && !($auth->acl_get('a_') || $auth->acl_get('m_kb')))
{
	trigger_error('KB_NOT_ENABLE');
}

// Initialize KB auth
$kb_auth = new kb_auth;
$kb_auth->acl($user->data, $auth);

$copyright = '';
$copyright .= sprintf($user->lang['KB_COPYRIGHT'], '<a href="http://kb.pertneer.net">', '</a>', KB_VERSION);
$copyright .= ($user->lang['KB_TRANSLATION'] != '') ? '&nbsp;&bull;&nbsp;' . $user->lang['KB_TRANSLATION'] : '';
$copyright .= ($user->lang['TRANSLATION_INFO'] != '') ? '<br />' . $user->lang['TRANSLATION_INFO'] : '';

$user->lang['TRANSLATION_INFO'] = $copyright;

// set cat id here needed for search and random article to work properly
$cat_id = request_var('c', 0);

// For search
$cat_search = ($cat_id == 0) ? '' : '&amp;cat_ids[]=' . $cat_id;

//For kb.pertneer.net Site
//$git = rand(1,20);
//End here for kb.pertneer.net Site

// Some default template variables
$template->assign_vars(array(
	'U_MCP'				=> ($auth->acl_get('m_') || $auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=kb', true, $user->session_id) : '',
	//For kb.pertneer.net Site
	//'KB_GIT'			=> $config['kb_git_link'],
	//'L_GIT_IMAGE'		=> './images/forkme_light_background' . $git . '.png',
	//End Here for kb.pertneer.net Site
));

// Handle all knowledge base related stuff, this file is only to call it, makes the user able to move it around
gen_kb_auth_level($cat_id);
$kb = new knowledge_base($cat_id);

?>