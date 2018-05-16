=== WPLib ===
Contributors: newclarity, mikeschinkel
Tags: library, mvc
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.14.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Foundation Library for Agency and Corporate Developers.

== Description ==
WPLib is a plugin designed to be used as a Must-Use plugin or to be embedded in a theme to enable the development of complex yet robust and performant WordPress applications and websites.

WPLib is an MV* foundation upon which robust applications can be developed. WPLib is NOT intended for use by end-users but instead for professional PHP developers who are tasked with building specific applications or sites on the WordPress platform by their company or their clients.

The WPLib source code is hosted and development occurs [on GitHub](https://github.com/wplib/wplib/) and the documentation site is located at [wplib.org](https://wplib.org).

== Installation ==
See the [Quick Start](http://wplib.org/quick-start) on wplib.org.

== Frequently Asked Questions ==
=I have installed it and it does not do anything=
That is because it is for PHP developers, not end-users. If you are a PHP developer see the [Quick Start](http://wplib.org/quick-start) to learn more.

== Changelog ==
= 0.14.4 =
- Unmasked extract() that were previously hidden from code reviewers. See https://github.com/wplib/wplib/issues/72

= 0.14.3 =
- Fixed wplib_define() to correctly set a non-default value

= 0.14.2 =
- Adding 2nd parameter (the $post object) to 'get_the_excerpt' hook inside Post_Model_Base->excerpt()

= 0.14.1 =
- Changed WPLib_Post_List_Base constructor to support items of disparate post types
- Moved docs to a wiki repository
- Added a screenshot

= 0.14.0 =
- Fixed lots of edge case bugs related to posts, terms and lists.

= 0.13.4 =
- Fixed bugs related to auto-adding user roles; a regression bug from several revisions back.

= 0.13.3 =
- Fixed bugs in WPLib::get_html_attributes_html(), WPLib::get_contents() and WPLib::put_contents().
- Fixed bugs in $post_model->excerpt(), $post_model->content().
- Slightly improve TEMPLATE comments that are emitted to theme files in WPLIB_DEVELOPMENT mode.

= 0.13.2 =
- A series of bug fixes and error message improvements.
- Fixed nasty bug in WPLib Commit Reviser.

= 0.13.1 =
Had to disable object caching in WPLib::_find_autoload_files() because of difficult to track down bugs.  The next major release (probably 0.14.0) will correct this.

WPLib was designed for use by teams who build custom sites and need professional workflow. The library is a **thin layer** that provides a simple **Module System** and a lightweight **Model+View architecture**.

WPLib is for those professionals who want their custom-developed WordPress sites to be both robust and easy to manage and maintain as site complexity grows.

## Documentation

You will find documentation in its current state on the [**wiki**](https://github.com/wplib/wplib/wiki).  
