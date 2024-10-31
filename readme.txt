=== RSS Feed Importer ===

Contributors: Taurean Wooley
Donate link: http://taureanwooley.com/?p=445
Tags: RSS Feed, RSS, Feed Aggregator 
Requires at least: Wordpress 3.9
Tested up to: 4.9.3.1
Stable tag: trunk
License: GNU

== Description ==
The following plugin allows for several different rss feed uploads.  It takes in the formatted xml's and converts them into a custom post type to keep
things clean.  There are several options in the work which will include full-content (currently experimental) as well as several options to see what
will be posted for cleaner feed posts.

The current options are available for showing the feeds:
1) shortcode [feed_info] and [feed_search] which will allow for searching and displaying based upon feed information and category information.
2) full-content and updated feed content.
3) custom feed names and custom feed categories which work in conjunction with already created categories in your wordpress sytem.

== Screenshots ==
1. RSS Feed Importer
2. Impressions
3. Categories
4. Manual Update
5. Feed Content Page
6. Monitization Page (includes autoposting on twitter)
7. Settings
8. Analytics

== Upgrade Notice ==
None at the moment.

== Installation ==
Manual installation: Extract zip file into the plugins directory
Automated installation: Find on plugin in wordpress plugin search under add plugin and click install, the rest is handled from there.

== Frequently Asked Questions ==
None at the moment

== Changelog ==
v.4.0.1 - Fixed issue with analytics page

v.4.0 - Major updated with many changes, most importantly automation for adding feeds and monitization.

v.3.2.8  - Fixed issue with title tags

v.3.2.7  - Fixed issue with admin backend

v.3.2.6  - Fixed issue with compatibility

v.3.2.5  - Fixed issues with rawurldecode as well as htmlspecialchars_decode for twitter feeds, also fixed links with twitter for clarification.
	   * Made sure that there were tests driven to create better content for the majority of users

v.3.2.4  - Fixed issue with displaying posts information on admin backend (still in the works, but should be able to read the titles)

v.3.2.3  - Fixed issue with titles duplicating for ever :) ... might hate me for it, using rawasciicode ... will fix later

v.3.2.2  - Fixed issue with titles for twitter and stopped duplicated posts

v.3.2.1  - Fixed issue with twitter feeds not showing up (minor issue with new feeds)

v.3.2	 - Updated to allow for autoposting on twitter (only allows 5 tweets per feed extration)

v.3.1.28 - Fixed issue with rss feeds that use <thumbnail> <url> for images

v.3.1.27 - Fixed issue with versioning

v.3.1.26 - Fixed issue with feeds that use id instead of link and other issues resolved.

v.3.1.25 - Fixed issue with pagination and widget

v.3.1.24 - Fixed issue with images not displaying (should have other issues completed in the following weeks)

v.3.1.23 - Fixed speed issue with updating feeds system

v.3.1.22 - Fixed issue with pagination numbering and reading total posts

v.3.1.21 - Fixed issue with pagination

v.3.1.20 - Fixed issue with loading impressions for servers with priority issues

v.3.1.19 - Fixed issue with several runtime errors based upon server restraints. Working on better solutions to keep the download_url from faulting and having conflicts with priorities.

v.3.1.18 - Fixed issue with feeds and other tag issues, can now post using several other options

v.3.1.17 - Fixed issue with impressions (server end) as well as fixed issues with saving custom_css for new installs and old installs

v.3.1.16 - Fixed issue with read more as well as issue with several different custom_css_ settings for updates as well as new system stats

v.3.1.15 - Fixed issue with impressions

v.3.1.14 - Fixed issue with updating new rss feed file structure for new and old installs ... not able to save information that was previously deleted, sorry.

v.3.1.13 - Fixed issue with saving custom css files for multiple-servers as well as removed custom_style.css from repository ... sorry for any inconvenience, if there is any needed assistance, please feel free to contact me at taurean.wooley@gmail.com

v.3.1.12 - Fixed issue with full-content pull for links with CDATA tags.

v.3.1.11 - Fixed issue with descriptions, now has the ability to load in CDATA content with tags and information not allowed in htmlentities conversion

v.3.1.10 - Fixed issue with multiple-languages, works with russian, spanish, english, french, korean, etc. hebrew is still being fixed.

v.3.1.9 - Fixed issue with utf-8 encoding for several accent driven languages

v.3.1.9 - Fixed issue with encoding for utf8 and other language settings. Should now work with various content driven elements which include but are not limited to several other options related to rss feeds and information driven xml files (will be implimenting content templates later down the development cycle).

v.3.1.8 - Added new features which include custom css editor as well as analytics information for users opting into the advertisement network

v.3.1.7 - Fixed issues with new media tags

v.3.1.6 - Fixed issue with media tags inside rss feeds for uploading images to wordpress (still need information for other media types)

v.3.1.5 - Fixed issue with pagination to reflect categories

v.3.1.4 - Fixed issue with widget system for old installs

v.3.1.3 - Fixed issue with pagination layout page

v.3.1.2 - Fixed issue with feeds with content and description tags and feeds that do not.

v.3.1.1 - Updated to include auto create pages as well as pagination for feeds

v.3.1.0 - Updated to include analytics as well as updated feed importing for numerous rss feed imports.

v.3.0.2 - fixed issue with deleting feeds from the bottom of the feed list

v.3.0.1 - fixed issue with deleting similar feeds

v.3.0.0 - Updated auto feed posting and allowing several other options to allow for quicker feed posts.

v.2.0.6 - Updated impressions and added hints and remote server access for code hinting.  Also made sure that the new code has most of the bugs fixed, there are several other issues that need to be addressed like the addition of full content downloading and reverse engineering layouts on the admin front-end.

v.2.0.2 - Update impressions page to allow for various options that were required to clean up the impressions flow.  It now allows viewing of impressions with updates based on scheduling.

v.2.0.1 - Updated wordpress feed_info and feed_search issue as well as placing new monitization options and social sharing on the blogging sections. Should allow for better scaling later down the line.

v.2.0 - Updated with listings of feeds as well as the ability to delete directly from the plugin. Also have added the ability to view impressions.

v.1.0.1 - Fixed bug with deletes and fixed issue with rss feed importer for most (if not all) rss feeds

v.1.0 - Initial Installation
