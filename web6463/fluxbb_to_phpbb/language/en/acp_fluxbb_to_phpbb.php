<?php
/**
 *
 * FluxBB to PHPbb for the phpBB Forum Software package.
 *
 * @copyright (c) 2023 - web6463
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}
$lang = array_merge($lang, [
	'ACP_FLUXBB_TO_PHPBB_HEADING'				=> 'Fluxbb To PhpBB configuration',
	'ACP_FLUXBB_TO_PHPBB_TB_PREFIX'				=> 'Fluxbb table prefix',
	'ACP_FLUXBB_TO_PHPBB_TB_PREFIX_EXPLAIN'		=> 'Enter Fluxbb Tables Prefix.',
	'ACP_FLUXBB_TO_PHPBB_AVATARS_DIR'			=> 'Fluxbb Avatars Dir',
	'ACP_FLUXBB_TO_PHPBB_AVATARS_DIR_EXPLAIN'	=> 'Enter the full address of the path where the fluxbb avatar files are located whitout end slash',
	'ACP_FLUXBB_TO_PHPBB_TB_LIMIT'				=> 'The number of loops queries per run',
	'ACP_FLUXBB_TO_PHPBB_TB_LIMIT_EXPLAIN'		=> 'If your site hosting resources are limited, please choose a smaller number. The default value is 20',
	'ACP_FLUXBB_TO_PHPBB_SAVE'					=> 'configuration saved',
	'CONVERT_RESET_USERS'						=> 'reset phpbb users',
	'CONVERT_RESET_DATA'						=> 'reset phpbb data',
	'CONVERT_IMPORT_USERS'						=> 'import Users',
	'CONVERT_IMPORT_FORUMS'						=> 'import Forums',
	'CONVERT_IMPORT_CATEGORIES'					=> 'import Categories',
	'CONVERT_IMPORT_TOPICS'						=> 'import Topics',
	'CONVERT_IMPORT_POSTS'						=> 'import Posts',
	'CONVERT_IMPORT_BOTS'						=> 'import Bots',
	// Welcome message
	'ACP_FLUXBB_TO_PHPBB_WELCOME'				=> 'Welcome to the Converter section. Here you can manage the conversion from FluxBB to phpBB. <br /> Please note that all steps must be performed in order. And until the end of each stage, it is not possible to execute the next stage. If you encounter an error during import, continue with the same section after refreshing the page.',

	// Labels for buttons
	'CONVERT_USERS'								=> 'Convert Users',
	'CONVERT_FORUMS'							=> 'Convert Forums',
	'CONVERT_CATEGORIES'						=> 'Convert Categories',
	'CONVERT_TOPICS'							=> 'Convert Topics',
	'CONVERT_POSTS'								=> 'Convert Posts',
	'CONVERT_REPLIES'							=> 'Convert Replies',
	'CONVERT_RESET'								=> 'Reset',
	'CONVERT_IMPORT'							=> 'Start Import',
]);
