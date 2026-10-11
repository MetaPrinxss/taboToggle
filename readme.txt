=== taboToggle: Hide Admin Bar Toggle ===
Contributors: foundrstack
Tags: admin-bar, toolbar, ui
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a toggle in the admin bar to hide or show it.  

== Screenshots ==
1. Admin bar with the Hide Bar toggle.
2. Admin bar hidden with floating restore button.

== Description ==
taboToggle: Hide Admin Bar Toggle adds a control to the right side of the admin bar. When hidden, a small floating button appears at the top right to restore the bar.

It also adds a shared tabomango menu in the WordPress dashboard with the taboToggle settings page, a Get Support link, and a More Plugins link.

== Installation ==
1. Upload the plugin folder to /wp-content/plugins/
2. Activate the plugin through the Plugins screen.

== Frequently Asked Questions ==
= Does this affect visitors? =
No. Only logged-in users who see the admin bar are affected.

= Does it save per user? =
It saves per browser via localStorage. 

== Changelog ==
= 2.6 =
* Renamed plugin to taboToggle: Hide Admin Bar Toggle.
* Added a tabomango top-level admin menu (shared with other tabomango plugins, created only once).
* Added taboToggle settings page under the tabomango menu (placeholder for upcoming options).
* Added Get Support and More Plugins links to the tabomango menu.
* Tested up to WordPress 7.1.3.

= 2.5.3 =
* Prevent admin bar flash on page load.
* Plugin Check compliance adjustments (no heredoc, escaped head output).
