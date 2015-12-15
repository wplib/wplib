<?php

/**
 * Class WPLib_Administrator_Module_Base
 */
abstract class WPLib_Administrator_Module_Base extends WPLib_Role_Module_Base {

	static $CAPABILITIES = array(

		'activate_plugins',
		'delete_others_pages',
		'delete_others_posts',
		'delete_pages',
		'delete_posts',
		'delete_private_pages',
		'delete_private_posts',
		'delete_published_pages',
		'delete_published_posts',
		'edit_dashboard',
		'edit_others_pages',
		'edit_others_posts',
		'edit_pages',
		'edit_posts',
		'edit_private_pages',
		'edit_private_posts',
		'edit_published_pages',
		'edit_published_posts',
		'edit_theme_options',
		'export',
		'import',
		'list_users',
		'manage_categories',
		'manage_links',
		'manage_options',
		'moderate_comments',
		'promote_users',
		'publish_pages',
		'publish_posts',
		'read_private_pages',
		'read_private_posts',
		'read',
		'remove_users',
		'switch_themes',
		'upload_files',

	);

}
