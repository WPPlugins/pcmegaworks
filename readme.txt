=== PCMegaworks ===
=== Version 001.034.035 ===
Contributors: Progressive Coding
Donate link: http://www.progressivecoding.net/donate.php
Tags: Maps, user groups, custom forms, mail blast, 404 redirects, broken links, broken images, basic chat
Requires at least: 4.5.4
Tested up to: 4.7.2
Stable tag: 4.7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
<p>The PC Megaworks Plugin is a consolidation of commonly used functionality in one plugin. Many plugin makers make one thing and make it well. We aspire to do the same thing by providing simple capabilities in abundance.</p>

<p>There are hundreds of very simple features that are commonly desired, but not already built into a theme. There a litany of one-purpose plugins to remedy this obstacle that each need server time and space. By combining them into one plugin we hope to help optimize load time, and potentially improve site speed.</p>

<p>The time of death by a thousand plugins is over. Our plugin is extensible, and we make it better by adding features you suggest. Here's what we have so far:
<ul>
  <li>Basic Chat capabilities with built in session control and handling for custom pages, or all pages.</li>
  <li>Customizable Google maps with groups to display multiple locations on one map (requires Google account for API keys ), directions ( driving and biking ), zoom level, geographic elevation and custom icons</li>
  <li>Custom form creator with customizable login, registration, contact us, and how are we doing forms as starters.</li>
  <li>Built in help desk in the event something goes wrong, including built in debugging with on/off setting in case of trouble</li>
  <li>Admin groups and users for page and content controls, macro role management</li>
  <li>Custom WordPress login logo, in the event you don't need a custom login page</li>
  <li>Mail blast with edit and storage capability, with user control opt-in and out</li>
  <li>Feature request form built in for convenience</li>
  <li>Http 404 redirects for popular pages that are moved, or for new sites where the URL structure is different. This can also be useful for multiple domain websites</li>
  <li>Ala carte installation for major features to avoid file and database cluttering, with no-trace un-installation and cleanup.</li>
  <li>Broken link finder to locate any pages on your site which have unreachable link destinations</li>
  <li>Missing image finder to locate any images that have been removed, incorrectly named, or are otherwise broken</li>
  <li>Custom registration email and landing page text with macro replacement for links and terms so you can control what your welcome email looks like</li>
  <li>Embedded HTML5 video player with display options and shortcode. This includes access control options and groups delegation to limit media content without limiting page access</li>
 </ul>
 <h2>Road Map</h2>
 <ul>
    <li>PC Megatheme is on the way!</li>
    <li>Custom form creation instructions and tricks documentation</li>
    <li>Add wp-admin options restriction for roles, so user groups can define hierarchical controls of simple WordPress capability roles</li>
    <li>Create multi-maps capability allowing multiple form groups to exist on a single page</li>
    <li>Implement form conditional logic user interface for increased capabilities in simple form control</li>
    <li>Build WIKI, and helpdesk ticket response interface from WordPress-admin backend to keep up with issues through the WordPress interface opposed to email contact.</li>
    <li>Add custom admin group creation and WordPress role control tie in to allow any WordPress capability a unique control group</li>
    <li>QR and barcode creator</li>
    <li>Internal helpdesk for your site and users</li>
    <li>SMS and IVR messaging tie in with Mercury Messages engine</li>
</ul>
</p>
Don't see a feature you need? Contact us and it may be in our next release!
== Installation ==
Automatic installation: Download and activate through the plugins section of your site.<br /><br /> <ul>
    Manual installation:
    <li>Download the newest version from progressivecoding.net/WP/</li>
    <li>Unzip the contents and move the pcmegaworks folder to the plugins directory of your site</li>
    <li>visit your sites administration ( wp-admin ) area and naviagte to the plugins section</li>
    <li>Locate the PC Megaworks plugin and click 'activate'</li>
    <li>Enjoy!</li>
  </ul>  <b>NOTE: When manually updating, de-activate the plugin first, then follow the above instructions.</b><br /><b>NOTE: If your server doesn't have access to CURL commands, installation will not be possible.</b>
The software is intended to work AS IS, without any implied expectation of working as you may have intended. If you feel we have overlooked functionality to a feature, please let us know.
== Frequently Asked Questions ==
http://www.progressivecoding.net/FAQs.php


== Screenshots ==

1. Overview of available options on primary page
2. Overview of settings page
3. Overview of helpdesk page
4. Overview of Form Manager with mini-nav

== Changelog ==
         <p>
         <ul>
         2017.07.08
            <li>001.034.035 - Corrected user message CSS for use with Bootstrap</li>
            <li>001.034.034 - Updated chatstyle.css for responsive use</li>
            <li>001.034.033 - Removed deprecated styles from style.css</li>
            <li>001.034.032 - Updated registration lead text for clarity</li>
            <li>001.034.031 - Corrected CSS Interface settings for common use</li>
            <li>001.034.030 - Corrected CSS and JavaScript inclusions to use wp_enque_scripts, per best practices</li>
            <li>001.034.029 - Moved custom menu adjustments to theme customizer per best practice</li>
            <li>001.034.028 - replaced PCMW_SERVERADDRESS constant with get_site_url(), per best practice</li>
            <li>001.034.027 - Added chat indicator, and improved user experience</li>
            <li>001.034.026 - Added chat status messages for clear interpretation</li>
            <li>001.034.025 - Added chat meta to maintain temporary data concerning the chat session</li>
            <li>001.034.024 - Added check to chat Ajax for logged out admins to prevent no method alert</li>
            <li>001.034.023 - Added font-awesome icons for controls in place of keyboard input for universal implementation</li>
            <li>001.034.022 - Added anonymous user handle option for chat</li>
            <li>001.034.021 - Set cookie for chat window state</li>
            <li>001.034.020 - Added Ajax register for chat for new sessions</li>
            <li>001.034.019 - Added body CSS adjustment Ajax handlers for improved user experience</li>
            <li>001.034.018 - Added themename to defined variables in constants.php</li>
            <li>001.034.017 - Added body support to CSS interface</li>
            <li>001.034.016 - Added menu support for theme to allow proper customizations</li>
            <li>001.034.015 - Implemented accordion support for theme customizer</li>
            <li>001.034.014 - Added custom body CSS implementations to plugin and theme</li>
            <li>001.034.013 - Added 'log' ajax response option</li>
            <li>001.034.012 - Added document.ready to tooltip options for page load Ajax calls</li>
            <li>001.034.011 - Removed deprecated code</li>
            <li>001.034.010 - Removed duplicate names in vendors for proper grouping</li>
            <li>001.034.009 - Added custom HTML support through bracket replacement for JSON transfer</li>
            <li>001.034.008 - Improved responsive design principles within plugin</li>
            <li>001.034.007 - Added custom menu feature and interface options</li>
            <li>001.034.006 - Added CSS interface class for styling and custom menu adjustments</li>
            <li>001.034.005 - Corrected missing style variable in maps module</li>
            <li>001.034.004 - Removed front facing CSS declarations to avoid styling conflicts</li>
            <li>001.034.003 - Added array checks to avoid notices and E_warnings</li>
            <li>001.034.002 - Removed deprecated filters and actions</li>
            <li>001.034.001 - Corrected login data confirmation on page load</li>
         </ul>
         <ul>
         2017.03.03
          <li>001.027.018 - Added basic chat feature and supporting files</li>
          <li>001.027.017 - Corrected 404redirect evaluation</li>
          <li>001.027.016 - Added BaseClass support for common files</li>
          <li>001.027.015 - Removed code comments and deprecated functionality</li>
          <li>001.027.014 - Added chat constants for ease of user_email</li>
          <li>001.027.013 - Added supporting page creation option for forms</li>
          <li>001.027.012 - Corrected form handler logic flow</li>
          <li>001.027.011 - Moved Google maps core files to proper location</li>
          <li>001.027.010 - Corrected form manager edit buttons location</li>
          <li>001.027.009 - Created retro fitting for login/logout and registration forms for proper redirection</li>
          <li>001.027.008 - Corrected failed login redirect and processing</li>
          <li>001.027.007 - Removed superfluous redirects on non-existing pages</li>
          <li>001.027.006 - Replaced redirect user calls with wp_safe_redirect for compliance</li>
          <li>001.027.005 - Removed login and logout hooks for logical flow</li>
          <li>001.027.004 - Decomposed init hook functions for proper handling</li>
          <li>001.027.003 - Added default values for new installs to prevent unexpected behavior</li>
          <li>001.027.002 - Added short code capability for Basic Chat</li>
          <li>001.027.001 - Added Ajax Nonce support where applicable</li>
         </ul>
         <ul>
         2017.02.15
          <li>001.024.012 - Removed duplicate return values and system messages</li>
          <li>001.024.011 - Removed deprecate functions</li>
          <li>001.024.010 - Improved notice detection and prevention</li>
          <li>001.024.009 - Corrected login and register page creation for legacy users</li>
          <li>001.024.008 - Corrected form copy bug and duplicate name prevention</li>
          <li>001.024.007 - Added button validation prevention</li>
          <li>001.024.006 - Added text:datalist option for forms when input or select is desired</li>
          <li>001.024.005 - Corrected form edit buttons on public forms</li>
          <li>001.024.004 - Corrected login WP user typo</li>
          <li>001.024.003 - Added edit page detection for updates</li>
          <li>001.024.002 - Added video watermark framework for security ( future release )</li>
          <li>001.024.001 - Added secure video management with hide source capability</li>
         </ul>
         <ul>
         2017.02.07
           <li>001.023.012 - Removed site wide css declarations</li>
           <li>001.023.011 - Added oveflow to how to list</li>
           <li>001.023.010 - Created page driven contact us, login, registration, and how are we doing pages for edit capabilities</li>
           <li>001.023.009 - Added debug capture for failed installs and other unexpected behaviors for user experience</li>
           <li>001.023.008 - Moved validate nonce to Abstraction class for reusability</li>
           <li>001.023.007 - Removed deprecated code</li>
           <li>001.023.006 - Removed spurious debug calls</li>
           <li>001.023.005 - Create form capture option in form manager for custom form handling</li>
           <li>001.023.004 - Changed menu addition options to include pages</li>
           <li>001.023.003 - Added count tag chars for textarea limitations</li>
           <li>001.023.002 - Corrected logout redirection bug</li>
           <li>001.023.001 - Removed init action execution of page creation</li>
         </ul>
         <ul>
         2017.02.04
           <li>001.022.008 - Added "how to" support for features and optional settings usage</li>
           <li>001.022.007 - Corrected admin user update for variable</li>
           <li>001.022.006 - Added inline notes for ValidateDefinitionRequires</li>
           <li>001.022.005 - Added default override for pre install debugging</li>
           <li>001.022.004 - Corrected debugging class call in plugincore</li>
           <li>001.022.003 - Created MailBlast class for cleaner code</li>
           <li>001.022.002 - Corrected potential installation error in validation of default user_email</li>
           <li>001.022.001 - Removed extraneous debug calls</li>
         </ul>
         <ul>
         2017.02.01
           <li>001.021.018 - Corrected potential critical failure on login redirection which would generate a potential indefinite redirect loop</li>
           <li>001.021.017 - Updated bootstrap.mi.js version for stability</li>
           <li>001.021.016 - Added bootstrap checkbox library for aesthetics</li>
           <li>001.021.015 - Code cleanup</li>
           <li>001.021.014 - Added array checks before loops to prevent potential E_NOTICE warnings</li>
           <li>001.021.013 - Removed 'sensor' variable in maps JS inclusion per console warning of Google deprecating it</li>
           <li>001.021.012 - Changed static images to use font-awesome icons for aesthetic purposes</li>
           <li>001.021.011 - Corrected login cookie storage location to set even if wp_signon() exits unceremoniously</li>
           <li>001.021.010 - Corrected setcookie call to extract the default domain</li>
           <li>001.021.009 - Moved non-Core related functionality to CoreHelper for best practice purposes</li>
           <li>001.021.008 - Decomposed init calls into specific functions for best practices</li>
           <li>001.021.007 - Added exit statements after all redirects for best practice</li>
           <li>001.021.006 - Removed duplicate notifications from registration</li>
           <li>001.021.005 - Reactivated change login URL for behavioral continuity</li>
           <li>001.021.004 - Corrected bootstrap menu creation redundant display error</li>
           <li>001.021.003 - Added null nav object and empty nav string support for ad hoc pages</li>
           <li>001.021.002 - Removed redirect for post login actions for code simplicity</li>
           <li>001.021.001 - Added WP_options removal for registration email and verbiage to uninstall routine</li>
         <ul>
         2017.02.01
           <li>001.020.008 - Added pointer:cursor class for buttons and unassumed links</li>
           <li>001.020.007 - Redesigned settings for simple customization using tabs</li>
           <li>001.020.006 - Created ala cart update to settings for ease of use</li>
           <li>001.020.005 - Added registration email and landing page customization</li>
           <li>001.020.004 - Added macro replacement options for registration email and landing pages</li>
           <li>001.020.003 - Added parent div ID's for expansion with future custom conditions in form creator</li>
           <li>001.020.002 - Added static array modifier field only option in form manager</li>
           <li>001.020.001 - Removed outline CSS form links</li>
         </ul>
         <ul>
         2017.01.23
            <li>001.019.011 - Re-organized header includes</li>
            <li>001.019.010 - Created helper class for PCMW_PluginCore</li>
            <li>001.019.009 - Added nopriv options to ajax for "contact us" form</li>
            <li>001.019.008 - Added wp-login.php logout adaptation for custom login</li>
            <li>001.019.007 - Refined page capture methods</li>
            <li>001.019.006 - Moved bootstrap menu on login and register pages</li>
            <li>001.019.005 - Changed options anchor tags to use onclick for JavaScript for simplicity</li>
            <li>001.019.004 - Removed forced formatting on menus</li>
            <li>001.019.003 - Added broken image location support</li>
            <li>001.019.002 - Added supporting utility classes for broken image and link search</li>
            <li>001.019.001 - Updated external JavaScript and CSS libraries</li>
         </ul>
         <ul>
         2017.01.22
          <li>001.018.005 - Added link check option</li>
          <li>001.018.004 - Added rudimentary menu for login and registration pages</li>
          <li>001.018.003 - Added http header description options for CURL</li>
          <li>001.018.002 - Corrected error.log path</li>
          <li>001.018.001 - Corrected legacy users for admin groups</li>
         </ul>
         <ul>
         2017.01.16
          <li>001.017.023 - Added default values for configuration options</li>
          <li>001.017.022 - Combined installation messages for readability</li>
          <li>001.017.021 - Removed unnecessary session instantiations</li>
          <li>001.017.020 - Created core functionality installation process to reduce plugin size</li>
          <li>001.017.019 - Optimized installation process for stability</li>
          <li>001.017.018 - Added session removal for new features</li>
          <li>001.017.017 - Corrected Feature request display</li>
          <li>001.017.016 - Combined map manager and map addition pages</li>
          <li>001.017.015 - Removed submenus for a cleaner appearance</li>
          <li>001.017.014 - Moved GET variables to the proper location for optimized flow</li>
          <li>001.017.013 - Removed configuration redundancy checks</li>
          <li>001.017.012 - Removed superfluous registration checks</li>
          <li>001.017.011 - Added successful registration message instructions</li>
          <li>001.017.010 - Removed login variable display panels</li>
          <li>001.017.009 - Moved sanitization to login class for proper handling and optimization</li>
          <li>001.017.008 - Added name values for form groups</li>
          <li>001.017.007 - Corrected form copy bug</li>
          <li>001.017.006 - Added ala carte feature installation for optimization</li>
          <li>001.017.005 - Added noRedirects for resource optimization</li>
          <li>001.017.004 - Corrected 404 redirect storage bug</li>
          <li>001.017.003 - Added session updates for real time adjustments in 404 redirects</li>
          <li>001.017.002 - Corrected verbiage in form leads</li>
          <li>001.017.001 - Restructured interface for ease of use</li>
         </ul>
         <ul>
         2017.01.06
            <li>001.016.008 - Corrected update version variable for incremental updating</li>
            <li>001.016.007 - Added registration instructions and removed redirect</li>
            <li>001.016.006 - Corrected configuration and 404 redirect lead messages</li>
            <li>001.016.005 - Added ID to map location table list</li>
            <li>001.016.004 - Code cleanup</li>
            <li>001.016.003 - Improved user message display</li>
            <li>001.016.002 - Added mandatory pre-usage configuration checks</li>
            <li>001.016.001 - Added pcconfig option data session clearing on logout for improved user experience</li>
         </ul>
         <ul>
            2017.01.01
            <li>001.015.018 - Added 404 redirect options and interface</li>
            <li>001.015.017 - Corrected static admin group designation for dynamic defaults</li>
            <li>001.015.016 - Added front facing header option for PCMW specific JavaScript calls in the WordPress front end</li>
            <li>001.015.015 - Corrected Non-WP compliant $ reference in JQuery calls</li>
            <li>001.015.014 - Added FontAwesome badges to buttons for visual queuing</li>
            <li>001.015.013 - Corrected improper Ajax response directive code</li>
            <li>001.015.012 - Corrected contact us and How are we doing submission bugs</li>
            <li>001.015.011 - Added contact us and How are we doing pre-formatting</li>
            <li>001.015.010 - Corrected short code calls for Contact us and How are we doing</li>
            <li>001.015.009 - Removed deprecated functionality from Google maps API core</li>
            <li>001.015.008 - Added supporting variables to Google maps helper</li>
            <li>001.015.007 - Added JS loaded variable to prevent duplicate JS loading and eventual multi-maps capability</li>
            <li>001.015.006 - Created login and registration classes for unique handling, and clutter management in PCCore</li>
            <li>001.015.005 - Added listedversion payload value for initial install version checks</li>
            <li>001.015.004 - Used non-relative path for admin-ajax.php location</li>
            <li>001.015.003 - Added delete row action for deleted 404 redirect options</li>
            <li>001.015.002 - Corrected UpdateHTMLElements PCMW insertion</li>
            <li>001.015.001 - Added GetFormAndDataByAlias for complete data set and update form retrieval</li>
         </ul>
         <ul>
         2016.12.18
          <li>001.014.020 - Added alias support for form group retrieval</li>
          <li>001.014.019 - Removed deprecated code comments</li>
          <li>001.014.018 - Improved Database error reporting display</li>
          <li>001.014.017 - Added alias support for form group retrieval</li>
          <li>001.014.016 - Corrected hierarchical form display, and permissions for install</li>
          <li>001.014.015 - Corrected string comparison variables for consistency</li>
          <li>001.014.014 - Suppressed warnings for dynamic variables</li>
          <li>001.014.013 - Added NULL support to string comparisons</li>
          <li>001.014.012 - Added correction to DecomposeCurlString to allow for nested equal signs to support simplified string comparison</li>
          <li>001.014.011 - Changed uninstall link in settings page to failed WP uninstallation contingent as a failsafe</li>
          <li>001.014.010 - Improved User messaging for successful and failed actions</li>
          <li>001.014.009 - Improved unique update category for volume updates</li>
          <li>001.014.008 - Corrected ajax form submission variable</li>
          <li>001.014.007 - Added dynamic form update ajax handler for better control through json values</li>
          <li>001.014.006 - Cleaned cod to make reverse engineering easier</li>
          <li>001.014.005 - Removed superfluous shortcode additions</li>
          <li>001.014.004 - Corrected settings processor to repopulate failed entry on validation fail</li>
          <li>001.014.003 - Changed banners to properly display in WP catalog</li>
          <li>001.014.002 - Added contact us and Haw are we doing support wit menu links option</li>
          <li>001.014.001 - Added feature request options</li>
         </ul>
         <ul>
         2016.12.10
          <li>001.013.016 - Added update capabilities for SQL tables</li>
          <li>001.013.015 - Added update version for code version sync</li>
          <li>001.013.014 - Added color update for successful form update</li>
          <li>001.013.013 - Added version to CURL calls for comparison</li>
          <li>001.013.012 - Added mail blast capability</li>
          <li>001.013.011 - Added profile mail blast option</li>
          <li>001.013.010 - Fixed constant redirect for login and registration options</li>
          <li>001.013.009 - Added mail blast update table, and form capture</li>
          <li>001.013.008 - Added initial stages of form conditional logic</li>
          <li>001.013.007 - Added edit button limiter for system forms</li>
          <li>001.013.006 - Added debug message for all failed queries to improve troubleshooting</li>
          <li>001.013.005 - Corrected permissions check for user types</li>
          <li>001.013.004 - Corrected minor variable mismatches</li>
          <li>001.013.003 - Corrected email template path error</li>
          <li>001.013.002 - Removed deprecated functions</li>
          <li>001.013.001 - Corrected titles for forms</li>
         </ul>
         <ul>
         2016.11.27
          <li>001.010.016 - Adjusted tooltip default width for long lines</li>
          <li>001.010.015 - Added submit failed form support within the form manager</li>
          <li>001.010.014 - Added nonce support</li>
          <li>001.010.013 - Moved form update messages display to top of FormToString</li>
          <li>001.010.012 - Corrected ajax call support for nonce</li>
          <li>001.010.011 - Removed redundant call for insert admin group</li>
          <li>001.010.010 - Added simple check for data ID on map group addition</li>
          <li>001.010.009 - Improved inline documentation ( work in progress )</li>
          <li>001.010.008 - Corrected spelling error in data failed message</li>
          <li>001.010.007 - Added deprecation notes for future releases</li>
          <li>001.010.006 - Added noform support for form validation</li>
          <li>001.010.005 - Added zero as a valid input support to form validation</li>
          <li>001.010.004 - Added muted and verbose install messaging</li>
          <li>001.010.003 - Corrected validation for admin user updating</li>
          <li>001.010.002 - Muted post activation/install RedirectUser</li>
          <li>001.010.001 - Added selected string submission for JavaScript</li>
         </ul>
         <ul>
         2016.11.22
          <li>001.008.009 - Corrected lingering $_POST and $_GET references</li>
          <li>001.008.008 - Corrected errant class without PCMW_ prefix</li>
          <li>001.008.007 - Added delete map option</li>
          <li>001.008.006 - Added validation to all object SQL inserts</li>
          <li>001.008.005 - Separated admin user and admin group class objects</li>
          <li>001.008.004 - Added proper object insertion methods to core classes</li>
          <li>001.008.003 - Added validation to ajax intake values</li>
          <li>001.008.002 - Corrected missing table prefix in Database calls</li>
          <li>001.008.001 - Corrected 'alu' ajax trigger</li>
         </ul>
         <ul>
         2016.11.18
          <li>001.007.015 - Corrected ## Please sanitize, escape, and validate your POST calls</li>
          <li>001.007.014 - Corrected ## Calling file locations poorly</li>
          <li>001.007.013 - Corrected global logging  ## Please don't mess with error reporting</li>
          <li>001.007.012 - Corrected ## Generic function (and/or define) names</li>
          <li>001.007.011 - Corrected ## Calling core loading files directly</li>
          <li>001.007.010 - Corrected ## Allowing Direct File Access to plugin files</li>
          <li>001.007.009 - Fixed login, access control, and register bugs</li>
          <li>001.007.008 - Added simple shortcode copy in display table</li>
          <li>001.007.007 - Added 'Add Maps' option to map groups</li>
          <li>001.007.006 - Removed session start from all pages ## Forcing PHP Sessions on all pages</li>
          <li>001.007.005 - Refined script_self access</li>
          <li>001.007.004 - Added custom logo for wp-login page</li>
          <li>001.007.003 - Removed default logo in settings</li>
          <li>001.007.002 - Rewrote ajaxcore for WP usage</li>
          <li>001.007.001 - Added PCMW_ prefix to all constants, files, and classes</li>
          </ul>
         <ul>
         2016.11.06
           <li>001.003.084 - Added screenshots for readme.txt compliance</li>
           <li>001.003.083 - Removed dead links from landing page</li>
           <li>001.003.082 - Created link to donate page</li>
           <li>001.003.081 - Created link to FAQ's</li>
           <li>001.003.080 - Added GPL and proper tags to readme.txt</li>
           <li>001.003.079 - Changed PCPlugin to PC Megaworks for WP naming convention compliance.</li>
        </ul>
        </p>

== Upgrade Notice ==
<b>Our recent modifications may create complications for users who have not updated since 001.021.018, and we advise an uninstall/reinstall for these users. This of course only after backing up database tables or changes made for customization.</b><br />
Any and all modifications of this software could potentially introduce 'bugs' which prevent functionality. We advise alterations to be made by professionals only. It is advised to upgrade this version whenever one is available, after making any needed backups to custom code or to data tables. Depending the type of update, the plugin may not update data tables. If you suspect this, please deactivate then reactivate the plugin through the plugins area.