<?php

/**
 * Class WPLib_Contributor
 *
 * The user type of 'Contributor'
 */
class WPLib_Contributor extends WPLib_User_Base {

	/**
	 * The user role slug
	 *
	 * @var string
	 */
	const ROLE = 'contributor';


	static function on_load() {



	}

}
WPLib_Contributor::on_load();
