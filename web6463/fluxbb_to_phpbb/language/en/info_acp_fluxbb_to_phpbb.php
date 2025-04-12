<?php
/**
 *
 * Fluxbb to PHPbb converter for the phpBB Forum Software package.
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
	'ACP_FLUXBB_TO_PHPBB'		                    => 'Fluxbb to phpBB',
	'ACP_FLUXBB_TO_PHPBB_CONF'	                    => 'Configuration',
    'ACP_FLUXBB_TO_PHPBB_CONVERTER'                 => 'Converter',
    'ACP_FLUXBB_TO_PHPBB_CONVERTER_USERS_RESETS'    => 'User data reset was done successfully',
    'ACP_FLUXBB_TO_PHPBB_CONVERTER_DATA_RESETS'     => 'Resetting the forum, topic and posts was done successfully',
    'ACP_FLUXBB_TO_PHPBB_CONVERTER_SUCCESS_TITLE'   => 'Success done',
    'ACP_FLUXBB_TO_PHPBB_USERS_SUCCESS_IMPORT'      => 'Users have been imported successfully',
    'ACP_FLUXBB_TO_PHPBB_FORUMS_SUCCESS_IMPORT'     => 'Insertion of forums was done successfully',
    'ACP_FLUXBB_TO_PHPBB_CATEGORIES_SUCCESS_IMPORT' => 'Insertion of categories was done successfully',
    'ACP_FLUXBB_TO_PHPBB_TOPICS_SUCCESS_IMPORT'     => 'Insertion of topics was done successfully',
    'ACP_FLUXBB_TO_PHPBB_POSTS_SUCCESS_IMPORT'      => 'Insertion of posts was done successfully',
    'ACP_FLUXBB_TO_PHPBB_BOTS_SUCCESS_IMPORT'       => 'Insertion of bots was done successfully.',
    'ACP_FLUXBB_TO_PHPBB_CONVERT_WHAS_DONE'         => 'The operation you want has already been executed.',
    'ACP_FLUXBB_TO_PHPBB_USERS_NOT_IMPORT'          => 'Users have not been imported.',
    'ACP_FLUXBB_TO_PHPBB_FORUMS_NOT_IMPORT'         => 'Forums have not been imported.',
    'ACP_FLUXBB_TO_PHPBB_TOPICS_NOT_IMPORT'         => 'Topics have not been imported.',
    'ACP_FLUXBB_TO_PHPBB_CONVERTER_FROM'            => ' from ',
]);
