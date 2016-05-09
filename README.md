[![Stories in Ready](https://badge.waffle.io/wplib/wplib.png?label=ready&title=Ready)](https://waffle.io/wplib/wplib)
# WPLib
**A PHP Library that Adds Modules and Applications to WordPress**

WPLib was designed for use by teams who build custom sites and need professional workflow. The library is a **thin layer** that provides a simple **Module System** and a lightweight **Model+View architecture**.

WPLib is for those professionals who want their custom-developed WordPress sites to be both robust and easy to manage and maintain as site complexity grows.

##Features and Benefits

The **WPLib Library includes:**

1. Implementation of a Module system with Auto-Loader,
2. The optional concept of an "Application" that uses app-specific Modules,
3. Code optimized for use with a persistent cache, and
4. Prescriptive conventions for code consistency across many developers.
5. A Model+View Architecture _([_see next_](#modelview-architecture))_

###<span id="model-view-architecture" >Model+View Architecture</span>

In order to enable the goal of truly reusable modules objects are needed. And the MVC pattern is a well-known and workable pattern. 

But unlike MVC plugins WPLib does not take control of WordPress URL routing. Instead Views are not about HTML pages as in Ruby on Rails, they are about adding output methods to Model objects and Model objects abstract and simplify programming with Post Types, Taxonomies, User Roles, and more. 

Currently in WPLib core we have Model+View classes for Posts and Post Types, Taxonomy and Terms; and Roles and Users.

The Model+View architecture provides:

1. The ability to build really complex yet maintainable WordPress sites,
3. A clear separation of concerns between backend developer and front-end themer,
2. Familiar yet consistent functionality to address common use-case, and
4. An architecture that results in naturally reusable modules.

###Can Be Used Incrementally
Note WPLib can be used a little or a lot. It can be added incrementally to a site, or a site can be started with WPLib as its base. The latter obviously provides the most benefits.


##Status
WPLib is in beta status at version 0.13.0 in master.

We are actively working on it with a goal to develop stable beta versions that can be used in production, and then a 1.0.0 version when we are comfortable that the majority of the API will not need to change.

##Demo Themes
Unfortunately these are **woefully out-of-date**, and may have bugs with the latest version of WPLib _(remember, we are still pre-1.0 and are still changing things.)_

We plan to update them in the near future, sooner if we have people who are pushing us to do so. :-)

- [Underscores4WPLib](https://github.com/wplib/underscores4wplib) - A port of an [Underscores](http://underscores.me) starter theme that can be used as a starter theme but is intended more to allow for comparison between standard WordPress theming practices and using WPLib best practices instead.
- [LawPress](https://github.com/wplib/lawpress) - A demo theme that will eventually be a full-fledged law firm CMS starter theme.

##History
WPLib is based on at least five (5) generations of code developed over five (5) years.

The 1st and 2nd generations were named [_Sunrise_](https://bitbucket.org/newclarity/sunrise-1) and the 3rd and 4th were called _NewClarity Lib (we did not release that publicly as it was focused on meeting the short term needs of clients and not focused on being widely usable.)_

The architecture concepts and the implementation is now **simple enough** and **mature enough** to release publicly.

##Discussion
**Join the discussion** on [Slack](https://wplib.slack.com).  To gain access email [team@wplib.org](mailto:team@wplib.org).

##Core Committers

- Mike Schinkel ([mikeschinkel](https://github.com/mikeschinkel))
- Micah Wood ([wpscholar](https://github.com/wpscholar))
- Daryl Lozupone ([clubdeuce](https://github.com/clubdeuce))

##License

GPLv2+
