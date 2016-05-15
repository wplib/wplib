=== WPLib ===
Contributors: newclarity, mikeschinkel,
Tags: library, mvc
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.12.3
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
=I\'ve Installed it and it Doesn\'t Do Anything=
That is because it is for PHP developers, not end-users. If you are a PHP developer see the [Quick Start](http://wplib.org/quick-start) to learn more.

== Changelog ==
= 0.12.3 =
Fixed bug that falsely through error "Cannot call WPLib::autoload_all_classes() prior to 'init' action, priority 9."

= 0.12.2 =
Added WPLib::set_max_posts_per_page() to allow changing from default of 999.

= 0.12.1 =
Fixed WPLib::get_template() to return instead of echo.

= 0.12.0 =
Added concept of a "helped" class with WPLib::current_helped_class() to WPLib and also renamed WPLib::call_helper() to WPLib::_call_helper().
Added a test in /wplib/defines.php for ! class_exists( 'WPLib_Enum' ) before requiring it and related classes, and before including wplib_define().

= 0.11.18 =
Moved ::maybe_make_abspath_relative() out of helper and into WPLib proper.
Changed WPLib::put_contents() so it can more likely update a file with 644 permissions.

= 0.11.17 =
Fixed bug in regex in WPLib::_load_modules()

= 0.11.16 and before =
All earlier versions


