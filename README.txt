=== WPLib ===
Contributors: newclarity, mikeschinkel
Tags: library, mvc
Requires at least: 4.9.x
Tested up to: 4.9.5
Stable tag: 0.14.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Foundation Library for Agency and Corporate Developers.

== Description ==
WPLib is a plugin designed to be used as a Must-Use plugin or to be embedded in a theme to enable the development of complex yet robust and performant WordPress applications and websites.

WPLib is a foundation library upon which robust applications can be developed. WPLib is <strong>not</strong> intended for use by end-users but instead by professional PHP developers who are tasked with building specific applications or sites on the WordPress platform by their company or their clients.

Think of developing with WPLib to be somewhat like:
<blockquote><em>"The joy of programming in Lavarel while respecting everything about WordPress that makes it an ideal platform for developing content management solutions."</em></blockquote>

The WPLib source code is hosted and development occurs [on GitHub](https://github.com/wplib/wplib/) and the documentation site will soon be located at [wplib.org](https://wplib.org).

== Installation ==
See the [docs to come soon].

== Frequently Asked Questions ==
=I have installed it and it does not do anything=
That is because it is for PHP developers, not end-users. If you are a PHP developer see the [docs to come soon] to learn more.

== Changelog ==
= 0.14.6 =
- Allow multiple space-separated CSS classes in "class" argument for WPLib::get_link()
- Prefix the result of get_html_attributes_html() with a space to avoid attribute squishing
- Use $args['link_text'] for WPLib_Theme_Base::get_next_posts_link() and get_previous_posts_link()
- Improved WordPress.org publishing script

= 0.14.5 =
- Allowed add_class_action() and add_class_filter() to transform periods in hook names to underscores in hook methods.
- Changed from esc_attr() to sanitize_html_class() for class sanitization in _WPLib_Html_Helpers::get_link().
- Wrapped numerous reflection calls with try {} catch {}.

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
- Had to disable object caching in WPLib::_find_autoload_files() because of difficult to track down bugs.  The next major release (probably 0.14.0) will correct this.

## Documentation

You will find documentation in its current state on the [**wiki**](https://github.com/wplib/wplib/wiki).  
