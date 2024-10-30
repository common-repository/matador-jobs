=== Matador Jobs Lite ===

- Contributors: jeremyescott, pbearne
- Donate Link: https://matadorjobs.com
- Tags: Bullhorn, job board, matador, google for jobs search, career portal, OSCP, job portal, jobs, ats
- Requires at least: 5.5.0
- Tested up to: 6.6.1
- Stable tag: 3.8.21
- Version: 3.8.21
- Requires PHP: 5.6
- License: GPLv3 or later
- License URI: https://www.gnu.org/licenses/gpl-3.0.html

Connect your WordPress site with your Bullhorn account. Cache job data locally and display it with style inside your WordPress theme.

== Description ==

Connect your Bullhorn Account with your WordPress site and display your valuable jobs on your new self-hosted job board. Matador makes this as easy as it sounds, and lets you seamlessly integrate a powerful job board--a major marketing tool for your business--directly into your WordPress site. Everything that is great about WordPress is extended to Matador: great out-of-the-box SEO, easy templating/theming, endless customization options, and more. Matador goes further by listing your jobs with incredible job-specific SEO customization (optimized for Google Jobs Search), and more.

Use Matador's powerful settings to connect our "Apply Now" button for jobs to a page that will collect applications, or look into purchasing Matador Jobs Pro to accept applications from Matador and see them turned into candidates submitted to jobs directly in your Bullhorn Account!

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the '/wp-content/plugins/matador-jobs-pro' directory, or install the plugin through the
   WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Configure the plugin by going to Matador Jobs > Settings.
1. Connect your site to Bullhorn by clicking on 'Bullhorn Connection Assistant' on the Settings Page, and following the.
   prompts.

== Frequently Asked Questions ==

= Does this Require a Bullhorn Account? =

You must have an active Bullhorn Account to use Matador's Bullhorn Import/Export features. It technically will function as a stand-alone jobs board without a Bullhorn Account, but there are better options out there for that.

= How Do I Get Bullhorn API Credentials? =

You must submit a ticket to Bullhorn support. Merely informing them you will be using Matador should give them all the info they need to help you, as we are now Bullhorn Marketplace Developer Partners and they know what a new Matador user needs. That said, we recommend first installing the plugin, activating it, and starting the Bullhorn Connection Assistant before you do this. Follow the prompts in the easy-to-use assistant and Matador will generate a copy-and-paste email you can send to Bullhorn Support to get you started.

= So Matador downloads jobs from Bullhorn. Does it accept applications too? =

Yes, if your a user of Matador Jobs Pro or All-Access. Once you've connected to Bullhorn and synced your first jobs, your visitors can apply to the jobs. Based on settings, the applications will be sent to your Bullhorn either immediately or in the next regularly scheduled sync with Bullhorn.

If you are only right now a user of the free Matador Jobs Lite, not yet. Matador Jobs lite allows you to designate a destination page for the "Apply" button, but you will need to handle your own applications, perhaps with a contact form plugin.

If you'd like more information on Matador Jobs Pro or All-Access, visit <https://matadorjobs.com/>.

= How Can I Customize the Look of Matador? =

Our documentation site <https://matadorjobs.com/support/documentation/> explains how to use our template system, theme functions, shortcodes, and actions and filters to make your site look amazing. You can also watch out for occasional client showcases on our where we feature creative and amazing looking implementations of Matador.

= How Can I Customize the Function of Matador? =

Matador is built by WordPress users for WordPress users. We included hundreds of Actions and Filters to help you customize how it works just like WordPress core. Some of those are documented at <https://matadorjobs.com/support/documentation/> while others can be discovered with a quick code review.

But that requires a developer and hours of work! If you haven't already, check out our many official extensions that are viewable at <https://matadorjobs.com/products/>. These extend Matador's core functionality in ways that can make each site feel unique! You can use an unlimited number of these All-Access Add-Ons with any Matador Jobs All-Access plan.

If you need something and you don't see an add-on, feel free to write us. Leave a comment in the Support Forum or with our Pro support system <http://www.matadorjobs.com/support/> (requires Matador Jobs Pro or All-Access). Simple modifications might already be documented and we can point you to them. And if you have a more complex modification, we may be able to take your input and idea and turn it into another All-Access Add-On.

= Where can I get support? =

Users of Matador Jobs Lite should use the plugin's WordPress.org support forum. Users of Matador Jobs Pro and All-Access annual or lifetime plans can use our support ticket system at <http://www.matadorjobs.com/support/>.

== Upgrade Notice ==

Upgrading from 'Bullhorn 2 WordPress' 2.4 or 2.5 to Matador Jobs Lite/Pro 3.0 or later includes some breaking updates. This will cause some sites to disconnect or look differently. Back up your site and perform the upgrade on a staging server if possible. We have made every effort to make this smooth, but be warned, it will require extra work to make your site function the same again.

== Screenshots ==

1. Options page - Bullhorn Import settings
2. CV/resume upload form
3. Notifications options page
4. Jobs listings in the admin

= Changelog =

## 3.8.21

- Bugfix: Fixed issue introduced in 3.8.17 that impacted automatic updates for most users. Manual update will be required for versions 3.8.17 to 3.8.21.

## 3.8.20

- Enhancement: Added client-side scripting that appends the CSS class value `file_added` to the file input box for the application form when a file is added. This gives developers and designers more tools to impact the user experience of the site. Matador's default presentation will not change with this update, but may in the future.
- Enhancement: (Bullhorn Integration) Added max field size protections for the following fields: candidateWorkHistory->companyName and all fields under candidateEducation. The Bullhorn resume processor can return data that is invalid with values exceeding the maximum character limits for these fields which would result in an API error when Matador submits the data for saving. This update handles that error.
- Enhancement: (Bullhorn Integration) Added fallback to use dateLastModified as the job posted date/time if the dateLastPublished is selected by the user as the job date but that value isn't set. This is a protection for misuse of the Bullhorn system, as jobs should not be "public" aka "published" without a dateLastPublished value. Unfortunately, customizations of the Bullhorn UI using field mappings and customizations of the Bullhorn experience via API-based applications can "publish" a job without setting the "dateLastPublished" value. This change to Matador protects the user from an error, but users are strongly encouraged to use Bullhorn via its best practices, as Matador is optimized for this.
- Bugfix: Reduced number of javascript remote calls during page loading.
- Bugfix: Fixed a bug causing Matador Jobs job listing schema data to be included in the graphs of two SEO integrations tools when we were not on Matador Jobs job listing single pages.
- Bugfix: Fixed a PHP WARNING encountered for Matador Jobs Lite users.
- Bugfix: (Bullhorn Integration) Fixed issues that could arise when candidate data was missing a Bullhorn ownerID value by adding checks and protections around reference or manipulation of Owner data.
- Internationalization: Added several new language packs based on work by clients and volunteers, with some use of AI translation tools.
- Developer: Updated build routines from Gulp to Webpack. Some stylesheet and javascript files were renamed. While this should not impact our users, it is possible some caching configurations may be impacted by the file name changes.

## 3.8.19

- Bugfix: This release contains a missing file that was somehow omitted from our release package of Matador Jobs Lite via the WordPress.org repository. We are sorry for this and will be re-evaluating our release and build processes following 3.8.18 to determine how to avoid such an issue in the future.

## 3.8.18

- Feature: Added new code to address user experience issues encountered by job seekers served cached web page data that included Matador application forms with cross-site request forgery (CSRF) protections in place that were stale due to caching. This update will aim to prevent those errors and ensure a smooth experience for users without requiring adjustments to the site's caching settings.
- Feature: Added a WordPress filter called `matador_jobs_query_jobs_args` to filter the search query arguments after code-based processing is complete. This is used in some advanced extension development and should only be used when the filter for before code-based processing will not work, as code-based query arg processing is designed to protect the user from unintentional issues.
- Bugfix: Fixes a bug that caused the Bullhorn Candidate Work History entries for newly created candidates to be missing the job title.
- General: Tested up to WordPress 6.6.1

## 3.8.17

- Feature: Persist Bullhorn connection for 5.5 hours. With an announcement made on March 5th, 2024, Bullhorn's API can now support connections lasting up to 5.6 hours instead of the old default of 10 minutes. Matador is updated to support the full extended connection which will reduce time-consuming and resource-intensive API calls to the Bullhorn Login systems from several times per hour to 4-5 times per day. See https://supportforums.bullhorn.com/viewtopic.php?t=35590 for more information from Bullhorn regarding the announcement.
- Feature: Added a default "action" argument to the Bullhorn sync routine that adds Notes to a created or modified Candidate entries. We recently learned that to apply Bullhorn Automation routines to the Notes, they must have an "action" set, so moving forward Candidate Notes created by Matador will have an action of "other".
- Feature: Added filters to Bullhorn sync routine that adds Notes to created or modified Candidate entries. The filter `matador_bullhorn_candidate_note` allows a developer to modify the string of text saved to the note and the filter `matador_bullhorn_candidate_note_args` allows a developer to modify the other arguments including the "action" value we now set as "other" (see previous changelog entry).
- Feature: The Bullhorn sync routine that creates a Submission between a Candidate and JobOrder will now include the value of the Candidate Owner in the `sendingUser` and `owners` fields. New Candidates created by Matador will be owned by the preferred owner as set in the setting 'Preferred New Candidate "Owner"', falling back to the the API User if the preferred owner is not found. This feature update will give that same assignment to the Submission, preventing issues where Candidate owners cannot see the Submission.
- Feature: Added protections to Bullhorn Candidate Sync for users who enable "allow multiple values" on any of the four phone fields on the Bullhorn Candidate object. _In general, we discourage users from enabling "allow multiple values" settings in the Bullhorn field mappings to any default fields._
- Bugfix: Fixed issue causing Admin Notices handling to sometimes throw a PHP > 8.1 warning. Depending on user error reporting, this could've resulted in errors printed to screen, logs, or had no impact.
- Logging: Various improvements to logging to support plugin development and issue resolution.
- General: Clarified minimum WordPress version required as 5.5.0. In our 3.8.0 release we deployed a feature from 5.3.0 and 5.5.0 and didn't bump our "Requires WordPress" version requirement from 4.9.6. Unofficially we now can run at 5.5.0 and above, however officially, as explained in our Prerequisites help doc on our support website (https://docs.matadorjobs.com/articles/prerequisites/), officially we require WordPress 5.8 beginning January 24th, 2024 and 5.9 beginning May 23rd, 2024.
- General: Tested up to WordPress 6.5.0.

## 3.8.16

- Bugfix: Yoast SEO's Third-Party Integration began triggering a fatal error with Yoast SEO's update 21.9, which removed a deprecated class Matador had previously used to enable our support of their SEO Graph. This fix upgrades to the new Yoast SEO API while also adding code to hopefully prevent future changes of Yoast from breaking Matador.
- Misc: Matador Analytics reporting disabled due to our data aggregation service entering sunset. We will be exploring new solutions for telemetry in the future.

## 3.8.15

- Security: Fixed an issue that resulted in upgrades from Matador Jobs Lite to Matador Jobs Pro not establishing folders for log files or resume files that did not have index.php files to prevent directory indexing.
- Security: Added a routine that double-checks for the presence of an empty index.php file to prevent directory indexing in the resume uploads folder during each candidate file save. This will replace the index.php file if it was missing from a previous bug or user error.
- Security: Added routine that double-checks for presence of an empty index.php file to prevent directory indexing in the Matador log files folder on during the creation of a new log file. This will replace the index.php file if it was missing from a previous bug or user error.

- Bugfix: Added a fix to handle unrecognized arguments/dynamic arguments for the [matador_portal] shortcode
- Bugfix: Added a routine to restore a missing log file folder that may have been deleted or not created properly on install/activation.

- Compatability: Changed an argument in a few WordPress core function calls that previously allowed null but in PHP 8.1 and later requires an empty string.
- Compatability: Added #[ReturnTypeWillChange] attributes to the Cookie Monster class to ensure PHP 8.1 and later compatability.
- Compatability: Modified the log delete function to fix an instantiation of the DateTimeImmutable class from a null value, which threw PHP deprecation warnings in 8.1 and later.
- Compatability: Added a method argument strict typing indicator and modified a function call to prevent an error with PHP's rtrim() after an accepted argument deprecation was added in PHP 8.2.

- Misc: Tested up to WordPress 6.4

## 3.8.14

- Enhancement: Plucked changes from the 3.9.x development branch into Matador Jobs 3.8.x branch to support updates to the Job Syndication Extension, formerly Jobs XML Feeds Extension, prior to the release of Matador Jobs 3.9.0.

## 3.8.13

- Enhancement: It appears that Bullhorn is currently experiencing some issues with its connection quality and in reviewing our automatic reconnection routine we identified an opportunity to add finesse around a temporary error that was being misinterpreted by Matador as a permanent error, resulting in Matador determining it could no longer connect and send notice to the administrator. We will now retry a reconnection on these temporary errors while continuing to stop attempts to reconnect when the permanent error is encountered.
- Bugfix: Fixes a bug impacting the functionality of the Test Auto Reconnect feature of the Bullhorn Connection Assistant that was introduced with performance improvements in 3.8.7.

## 3.8.12

- Bugfix: Fixes a bug introduced in 3.8.10 around deprecated function update impacting the job info header.

## 3.8.11

- Bugfix: Bullhorn resume parser can return no values for job title in a work history entry, and when that occurred would generate a PHP Warning in error logs. Added protections to supress the error and allow a resume
- Bugfix: Identified and fixed issue causing Matador to create a WordPress transient with no name, which disrupted object caching when Redis Object cache was deployed.
- Misc: Tested up to WordPress 6.3

## 3.8.10

- Enhancement: Matador Search previously supported an additional field that searches job by the external ID, aka Bullhorn ID. Now, when the search field is used to search for a whole (integer) positive number greater than zero, search will be performed on the external ID field and not the title and description. This makes it easier to implement ID-based searching on your site without changing previous behavior. Future implementations of Matador that import non-numeric ID values from external services will still need to use the separate external ID search field, however.
- Enhancement: Modified Matador's Bullhorn Connection routine to leverage the use of the datacenter-specific OAuth and Rest servers to optimize for changes to the Bullhorn API authorization systems July 11th and 12th, 2023. The changes to Bullhorn's system did made connections in our prior integrations less performant (slower, less reliable) and subject to occasional failure, while these changes will result in more performant connections.
- Enhancement: Bullhorn Connection Assistant now no longer inquires about the users' Bullhorn Datacenter. Matador will now determine the proper Datacenter based on client cluster information gathered from the first successful login to Bullhorn, and on an update to 3.8.10, if an existing active login is present, the Datacenter will be detected from the client cluster information in that login. In the event a user's company moves server client clusters, a one-time reset of the Bullhorn Connection Assistant will be required to ensure Matador acknowledges the move, however, it is not common for Bullhorn to move a client to another cluster.
- Enhancement: Added handling for when "Allow Multiple Values" is selected for the required Education Degree Name field on the Job Order.
- Enhancement: Remove decimal values in salary fields when the number is greater than or equal to 1000. This prevents salary strings like "$64,000.00 per year" but leaves "$17.50 per hour" in place.
- Enhancement: Improved error logging for issues encountered during license activation.
- Bugfix: Do not set the 'salary_string' and 'salary_formatted' values for a job when the fallback/default 'salary' value is used and is not set or set too zero.
- Bugfix: When a fallback/default 'salary' value is used and is a non-zero, set the 'salary_string' value.
- Bugfix: Fixed issue where a Google reCAPTCHA installed by another theme or other plugin while not using Matador's Google reCAPTCHA solution would cause issue with Matador's form validating.
- Bugfix: Identified and fixed issue in Matador's beta new sync routine that could be encountered when a sync step is not found as a class method or callable, resulting in an infinite loop.
- Bugfix: Identified and fixed two issues in Matador's new sync routine (currently in beta) where a class property that was to be read via late static binding was being read as an instance property, causing issues in some implementations. Notably, locally created jobs were being removed during a remote sync.
- Misc: Updated template use of deprecated function to use the replacement function for the same behavior.

## 3.8.9

- Enhancement: A legacy behavior of Matador is to include all custom form fields in the Notes section of a newly created Candidate. This behavior allows site operators to collect custom data that isn't mapped to a Bullhorn field. Recently, anti-spam and user-behavior tracking tools started to add meta-data to forms on sites using "hidden" form fields. This would result in loads of "junk" data being scooped up by Matador and appended to the notes. An enhancement to our form processing will retain the legacy behavior of the form data processor while preventing this junk data from being collected in the Candidate data record.
- Enhancement: The `publishedZip` field will be included in the default job data import and used in lieu of the `address->zip` when found. The `publishedZip` is a newer field in the Bullhorn API and many users do not use it beyond default behavior, which is to copy the value of the job address zip into the `publishedZip` when a publishing action is taken. For that reason, in most use cases, `publishedZip` it matches the `address->zip`. Given its description on the job data object, however, some users of Matador have brought it to our attention that they provide an alternate "published postal code" at publishing that is different than the jobs' address postal code. This change will benefit those users largely without modifying the behavior of Matador for existing users.
- Bugfix: Fixed issue caused by change to how Bullhorn's returned resume object formats secondarySkills data. The issue was not impacting sync of Applications but was logging a PHP Warning and resulted further in the secondary skill data not saving to the Applicant record.
- Bugfix: Fixed an issue with one of Matador's site health monitoring systems failing when a transient is not set properly on first use of the day.
- Bugfix: Fixed a bug where some salary strings would not have the proper salary unit field due to a variable name for the salary unit being misspelled.
- Bugfix: Added logic to prevent a PHP "warning" from being logged when a job did not have a value in the "last post meta" meta array. The "last post meta" meta array was added in 3.8.7 so this would only occur on the first job update (due to republishing or a hard sync) on each job record after an upgrade from before 3.8.6 or earlier.
- Bugfix: Fixed issue where items appended to the content of a job posting description, ie: apply button, application, job info bar, etc, was being added to XML feed content when it should not.
- Misc: Added improved logging for the new/experimental sync experience.

## 3.8.8

- Enhancement: Added copies of WordPress 5.9 Polyfills for PHP 7.3 and 8.0 string and array functions. This is to protect users of WordPress before 5.8 who also use PHP before 7.3 and before 8.0.
- Enhancement: Updated a log line to display the ID of the Submission when a candidate is added to a job as a Web Response or Job Submission.
- Enhancement: Added filter `matador_bullhorn_applicant_countryID_default` to override the Bullhorn CountryID default on new Applications
- Enhancement: Added protections for improperly formatted WorkHistory job titles returned from the Bullhorn API resume processor.

- Bugfix: Fixed an issue that caused an improper salary range string (used to display salary information in templates since 3.8.4) to be generated when there was a zero or null value in the Bullhorn data object's `salary` field for the job but a properly configured salary high and salary low field was defined in Matador settings.
- Bugfix: Fixed issue that may occur during source tracking when a server HTTP User Agent is blank.

## 3.8.7

- Performance: Removed any reliance on the WordPress Transients API for handling Bullhorn connection variables, falling back to explicit use of the options database. This is to prevent inconsistent handling of transients by load balancers and persistent object caching systems we've encountered since WordPress 6.1 and Matador 3.8.0.
- Performance: Prevented "race condition" bugs when multiple concurrent Bullhorn connections are running at once. These will now be considerably more rare.
- Performance: Removed a developer debugging feature that could interrupt regular Bullhorn communication if a logged in user was accessing WP Admin during a specific window of time during an active Bullhorn communication.

- Feature: Added special handling for users of the Akismet comment/form spam prevention plugin. This plugin adds a number of hidden form fields to be used as form validation and anti-spam validation to every form on a WordPress site including Matador Jobs application forms. Users of Akismet and Matador would see this now otherwise junk data in their Bullhorn records and emails after submission. This feature will strip this junk data from the form before processing.
- Feature: The `matador_delete_job` action is an alias for the WordPress core `delete_post` action, but to enhance its usefulness and give it access to items removed earlier in the core WordPress delete post process, it will now run on WordPress's `before_delete_post`. It now also has access to a WP Post object as a second argument.
- Feature: Code infrastructure added for rapid development of "Developer Tools". These can now be created by extending the `matador\MatadorJobs\Developer\DevToolAbstract` class, which will add a menu under Matador Jobs called "Matador Developer" with the registered tool(s).
- Feature: Application Processor can now also accept and process files that already exist on the web server, as opposed to those only in the form submission temporary memory. This allows extensions to download and store resume files and then process applications with them.

- Enhancement: As of Matador 3.8.4, new Salary Range features format the raw numbers from the API into localized numbers based on the locale of the site. This feature requires the PHP `NumberFormatter` which may not be included on all installations of PHP. The PHP module `intl` is not required by WordPress (though is recommended) but is required for this feature. A consequence was that on a few users' sites that did not have PHP's `intl` module, a PHP fatal error would be triggered. This release adds a failsafe that ignores number formatting when the `NumberFormatter` class of the `intl` module is unavailable.
- Enhancement: Salary Range would fall back to the value of the `salary` field when the high and low were the same non-zero value. Some users, we learned, have begun to use the default `salary` field for internal uses only and thus we modified the behavior to use the value of the high salary field when high and low are the same. That said, we encourage users to never use standard Bullhorn fields for any purpose other than its assumed or defined use case.
- Enhancement: Added exception handling for occurrences of a customized Job Object from Bullhorn featuring a multi-value `onSite` field. Prior to this update, a multi-value `onSite` field may result in a failed job sync. We again warn our users to, within reason, use standard Bullhorn fields in its assumed and defined use case.
- Enhancement: Admin Email notices will no longer send on Staging and Local sites. Basic automatic detection of staging/local is used but users wishing to leverage these features are encouraged to set WP_ENVIRONMENT constants in their wp-config.php file (since 5.9).
- Enhancement: Admin Emails are now throttled on a per-email basis, instead of all. This may result in more total emails per day but still only per issue, per day.
- Enhancement: All registered Job Taxonomies will now have sidebar menu item. We previously disabled these by default because most users don't need them and some sites that make heavy use of Job Taxonomies saw their sidebars taken over by the Matador Jobs menu, which was poor user experience. Unfortunately, we are temporarily enabling this due to the popular Elementor page builder hiding taxonomies if they do not show in side menu. We will explore improving User Experience around this in a future release.

- Bugfix: Fixed an issue causing .docx files generated by Google Docs to fail validation prior to processing to external systems. This actually wasn't "fixed," but rather avoided, as the cause of the issue is in libmagic, the Unix/Linux file handler that PHP relies on. The fix runs an after-validation check on files that fail validation to detect the bugged Google Docs generated MS Office Open Office formatted files.
- Bugfix: Fixed the "Test Auto Reconnect" button to once again work immediately, which due to 3.8.0 performance improvements, still technically worked but with a delay.
- Bugfix: Prevented the careerPortalDomainRoot test from attempting to run until Matador is connected to Bullhorn.
- Bugfix: Resolved issue where the setting "Log Googlebot Visits to Jobs" was not honoring the "off" selection.
- Bugfix: Modified the application sync routine to prevent a rare bug where too many applications could cause a memory overflow and cause all applications to fail to process.
- Bugfix: Fixed issue where date last updated/synced information would not store in the job object local copy. This would not impact sync routines, but could cause a PHP Notice, which may display on-screen or logged in error logs depending on site settings, and also impact sites that might want to show date/time of job added or updated.
- Bugfix: For reasons we still cannot explain, some sites will rarely save temporary Bullhorn connection data without a natural expiration. Since subsequent Bullhorn connections will use this transient data (or create new data if it is expired), we added a behavior that when a connection fails it can force-refresh this prior to a reconnection attempt.
- Bugfix: Fixed a bug affecting is_staging_site() when the Automattic JetPack plugin is active.
- Bugfix: Fixed a PHP Warning caused by sites that either have the matador_uploads folder deleted and/or not created upon plugin install/activation (due to any other bugs on the site at the time).
- Bugfix: Fixed a bug that could be encountered by Matador's template helpers aggressively escaping HTML in places where Matador would put some HTML for styling purposes.
- Bugfix: Fixed a bug that allowed field values completely removed from a Bullhorn job would persist on the Matador-cached job object. This will begin to impact all syncs moving forward; jobs with this existing bad data will not be updated.

- Misc: Removed a hidden debugging tool. This tool, while known to some of our users, was complex to use and if misused, could cause irreversible changes to Bullhorn data. The tool is now available as separate download upon request. The tool's page is held by a temporary error screen explaining its removal for user experience purposes.

## 3.8.6

- Feature: Extended Salary/Pay Range to now Support customFloat1 - customFloat3 options. This is for users whose high/low salary values are not whole numbers (especially likely when the rate is per hour.)
- Bugfix: Resolved issue where the shortcode for the Job Info Bar would not show a users custom meta fields selections.

## 3.8.5

- Fixed issue with updater script that caused people who updated to 3.8.0 or later to not get automatic updates.

## 3.8.4

- Feature: Support for importing and displaying a Pay Range. This is in response to upcoming laws and regulations in various jurisdictions that will require hiring agencies that meet certain qualifications to publish "pay ranges" for each position. A full help document will be written to explain how to use this feature, but the cliffs' notes are:
-- New Settings Section "Salary Options" Under the Job Listings Tab
---- Formerly Job Structured Setting "Show 'Pay Rate' Data" was moved and renamed "Display Salary/Pay Rate". This will now add a Salary Transparency string to the Job Information Bar under the title automatically.
---- New Settings "Salary Range Low Field" and "Salary Range High Field" allow users to select which fields, from a list of two default and 8 custom number fields, to use for the two parts.
-- When imported, the following existing or new post meta fields will be made on the job object if supporting data was found on the remote job record:
---- salary_currency: the value of the Bullhorn setting or a custom text field with the salary's currency
---- salary, salary_low & salary_high: The raw number(s) from the imported data. salary_low can be 0 while salary and salary_high cannot be 0 and will be left unset if 0.
---- salary_formatted, salary_low_formatted, salary_high_formatted: a localized formatted string of text containing the value with currency symbols and number/decimal separators. salary_formatted is formatted value of the Bullhorn salary field, unless it was blank and a high or low value was present, which would be used instead in that order.
---- salaryUnit: The text string that represents the unit, ie: Annually, Per Hour, etc.
---- salary_string: This is a string of text that dynamically combines the pieces we imported into a succient summary of the salary or salary range, eg: "$100,000 USD per year" or $97,500 - $105,000 USD per year", etc.
-- The following WordPress filters were added to customize the behavior of the above features:
---- matador_bullhorn_import_bullhorn_salary_currency_field will allow you to assign a custom text field to your job import that will designate the currency of the pay rate. This is optional, but helpful for users offering roles paid in separate currencies. Default is the value of the default currency in Bullhorn settings.
---- matador_bullhorn_import_salary_range_separator filter will allow you to change the characters or text that separate two numbers in a range. Default is an n-dash, or -.
---- matador_bullhorn_import_salary_unit_separator filter will allow you to change the characters or text that separate the last pay rate with the pay unit. Default is a space, eg, the space between 'USD' and 'per year' in the string '$100,000 USD per year'.
---- matador_bullhorn_import_salary_string filter will allow you to further customize the text string that makes up a salary statement. Default is the string we construct and it is passed an array of arguments with all the imported salary parts.
---- matador_structured_data_include_salary filter can be passed true or false to hide salary fields from the external structured data (used by Google, others to aggregate job data). It defaults to the settings option "Display Salary/Pay Rate". If you are required by local law or regulation to show pay rate, Google may not present your jobs if this is not provided.
---- matador_template_job_info_show_pay filter can be passed true or false to show or hide the Salary/Salary Range text in the Job Info Bar. The Job Info Bar is getting a little crowded, we'll admit, but it is the most consistent way we can include this by default without breaking users' layouts. It defaults to the settings option "Display Salary/Pay Rate".
- Feature: Bullhorn's newly implemented isWorkFromHome field will now be used to flag "Remote" or "Work From Home Jobs" in addition to the legacy behavior of matching certain values from the onSite field.
- Feature: New "Information Form Field" to simplify passing instructions via settings fields.
- Bugfix: Fixed issue that could cause the job importer to crash if the user has modified their Bullhorn field mappings to expose and then manually set or manipulate the publishing status. While this was introduced to Matador Jobs to prevent a crash of our importer, users who leverage Bullhorn customization to create non-standard records may be subject to unexpected and undesired outcomes. For example, while this fix will prevent an importer crash, it will drastically increase the amount of data imported in each job sync due to the missing dateLastPublished value. For best results with not only Matador but all Bullhorn Marketplace Partners, consider using as standard of an implementation as you can.
- Bugfix: Fixed an issue that could cause recruiter emails to fail when a Published Response User is not set during Publishing. This is related to the prior patch note and carries the same warnings.
- Bugfix: Fixed an issue preventing the assumed behavior of the "Sync This Job" button to not work as intended. The intent of this function was that a manual per-job sync would always fully download and overwrite the job data, but it was honoring dateLastPublished/dateLastModified rules intended for only the bulk sync. The button will now work as expected.
- Bugfix: Added action so extensions that extend "Sync All Applications" can use it more reliably.
- Bugfix: Fixed issue introduced in 3.8.0 that impacted certain sites using Persistent Object Caching with load balancers.
- Bugfix: Related to the 3.8.2 bugfix "When a certain of combination of settings were selected, the recommended value for careerPortalDomainRoot was incorrect", we fixed that in 3 of the 4 places it occurred, but not the fourth! So trying again for 4/4.

## 3.8.3

- Bugfix: Fixed poorly formed if...then condition related to Analytics reporting.

## 3.8.2

- Bugfix: When a certain of combination of settings were selected, the recommended value for careerPortalDomainRoot was incorrect. When presented to users, the careerPortalDomainRoot value will be right moving forward.
- Bugfix: A "nice to have" feature disabled in 3.8.1 due to causing new installations of 3.8.0 to fail was fixed and restored. The recommended value for careerPortalDomainRoot will now be displayed under the "Job Listings Slug" option and under the "Disable CareerPortalDomainRoot Check" option in the Bullhorn Connection Assistant.
- Bugfix: In order to ensure daily analytics do not send more than intended, Matador now tracks analytics calls via an explicit option instead of a transient. We observed that some sites with aggressive or buggy persistent object caching could send Analytics hourly, which is not the intent.
- Bugfix: Fixed issue where blank/empty values could be saved to the application data when a checkbox field type was used. When a checkboxes field type is used in the application, an empty value used in form submissions for validation purposes but would persist and be saved into the application data. While often not an issue in and of itself, when syncing an application to Bullhorn, this empty value could result in an `IMPROPERLY_STRUCTURED_ASSOCIATION` API error when being submitted to `HAS_ONE` or `HAS_MANY` field types. This bugfix will prevent the empty checkbox from being saved in the first place while also fix data before API submission to avoid future `IMPROPERLY_STRUCTURED_ASSOCIATION` API errors.

## 3.8.1

- Bugfix: Temporarily removed the "nice to have" feature of 3.8.0 which displays the expected/desired value for the careerPortalDomainRoot in certain settings screens which, due to how this is added to the settings screens, could cause the initial installation of Matador Jobs on fresh WordPress websites to crash. Per our investigation, this only impacted users installing Matador Jobs on a fresh installation of WordPress where the "pretty permalinks" options were not yet set. No active jobs sites were impacted, as the nature of this bug would never have impacted them.

## 3.8.0

Matador Jobs 3.8.0 contains changes in 318 commits changing 104 files and over 14,000 lines of code revised or added. It is a large update with many quality-of-life changes that will make Matador Jobs more reliable and easier to use for all our users.

This will be the last major version of Matador Jobs to support PHP 5.6 and WordPress versions prior to 5.8. When released early to mid next year, our next version will require PHP 7.4 or higher and WordPress 5.8 or higher.

### Connectivity & Stability

Highlights:

- **New Sync (Beta)**: This release includes a beta release of our updated sync routine. The sync routine was redesigned to pause prior to triggering "long processor killer" triggers at the web host and resume, picking up where it left off, over and over again as needed, to ensure a complete sync consistently occurs. Some users, especially those with more than 2000 active openings, would see syncs fail due to "long process killers." While this new sync routine is very reliable, we have opted to make it optional until 3.9.0. Opt-in to the new sync by adding this developer flag to your site `add_filter( 'matador_experimental_sync', '__return_true' );`.

- **CareerPortalDomainRoot Detection**: Several Bullhorn systems, including Bullhorn Automation (formerly Herefish) and Bullhorn Publish to Indeed will generate a web address (URL) for the job based on the value in Bullhorn of the careerPortalDomainRoot setting. Matador users who use Matador as their primary job board (or portal) should notify Bullhorn to update this setting. Now, Matador will read the value of careerPortalDomainRoot in Bullhorn and let you know if there is a mismatch. Further, this detection will not trigger on common local, staging, development, or test environments and can be disabled.

- **Added "Cookie Bug" Warning**: Long time Matador users will know about the "Cookie Bug." This is a bug in the Bullhorn authentication system that allows a logged-in user's cookie to hijack the Matador Jobs authentication with Bullhorn and authorize as that user, not the API user. This has serious potential consequences including job syncs bringing in the wrong jobs and the site being unable to auto reconnect in the event of a downtime. Now Matador can detect when a "Cookie Bug" has occurred and will warn the user.

- **Added Matador Unsafe Password Warning**: Matador Job's auto reconnect routine cannot reconnect when the API User Password has certain "unsafe" characters. Valid Bullhorn passwords can still cause an issue, so Matador will now detect if the password would not allow for an automatic reconnection.

More:

- Fixed an issue where the "Test Auto Reconnect" routine would result in a disconnection fail following changes to Bullhorn's API.
- DISABLE_WP_CRON warnings will now not display as admin notices. Many managed WP platforms have moved to disable WP_CRON and replace it with system cron. This is a good thing! Unfortunately, this also turned our formerly helpful heads-up into an annoying notice for many users.
- The Administrator Disconnected Site Warning Email will now only send after several attempts have been made to reconnect. This, in combination with the existing 24-hour timeout, should minimize the occurrence of admin warning emails when the software can reconnect naturally. The recent higher occurrence of Disconnection Notices has been due to a high frequency of 429 Too Many Requests errors on the Bullhorn API, which means there is a "traffic jam" on their system.
- The Administrator Disconnected Site Warning Email has two changes. First, the subject line formerly had the "Site Name" but will now have the "Site URL". Second, a line is added to the content of the email with the "Site Name" and "Site URL". These two changes will help site operators differentiate between disconnections of live/production sites and disconnections of local, test, staging, or development sites, and how much urgency they need to apply to the email notification.
- Fixed a bug that caused logging options to not save on "lite" versions of Matador Jobs.

### Marketing/SEO

Highlights:

- **Experience and Education Updates**: Google for Jobs Search and other systems that consume Job Structured Data/LD+JSON have new expectations for Experience and Education details. Matador will now serve the updated schema.

- **Employment Types Detection**: Matador will now map more user values to the default Job Structured Data/LD+JSON employment types. We still encourage you to review the values you are passing from Bullhorn into Matador Jobs for employment type and ensure they are mapping to Job Structured Data/LD+JSON expected values, especially if your primary language is not English.

- **Integration with All in One SEO (AIOSEO) Plugin**: Matador will now integrate its Job Structured Data/LD+JSON features into All in One SEO plugin. This happens automatically when the AIOSEO plugin is included. AIOSEO now joins RankMath and Yoast SEO in the list of 3rd-party SEO plugins that Matador seamlessly integrates with.

More:

- Matador Jobs can now detect visits by the 'Googlebot', aka Google Indexing systems, on jobs. All visits are added to your Matador log file, or if you have Google Indexing API active, you can log only visits after an Indexing API call.
- Added a validation to the Job Structured Data/LD+JSON inclusion to validate that we indeed have valid JSON. This prevents issues with page validation (ie: from Google Search Console) if the JSON is invalid. Invalid JSON is sometimes created when customization developer filters are improperly used.
- Fixed issue where JSON+LD was being loaded on certain jobs archive/job listing pages, resulting in possible issues with page validation (ie: from Google Search Console).
- Updates to Yoast SEO integration to reflect changes to their plugin.
- Fixed bug on RankMath SEO integration causing the Job Structured Data/LD+JSON to be included in the wrong part of the graph. As of discovery of this error, page validation tools (ie: from Google Search Console) still considered the old (wrong) implementation valid.

### Privacy

Highlights:

- **Terms of Service Consent Field**: A Terms of Service consent field was added to Application forms. It behaves similarly to the existing Privacy Policy consent field. This gives users the ability to have separate Privacy Policy and Terms of Service consents, which is recommended for adhearance with the ever-changing data security and privacy regulations. This update includes the following:
    - New checkbox form field for Terms of Service with basic text description. *International users please send us a literal translation for the field and we will update translations for your language.*
    - New admin settings "Require User to Accept Terms of Service" and "Terms of Service Page". When the "require" setting is toggled on, Matador will add a form field in the application footer that requires applicants to check a box acknowledging the website Terms of Service. If a page is selected in the "page" setting, a link to the WordPress page with the Terms of Service will be included.
    - If your Bullhorn install includes the Candidate Consents object, a new consent will be added for "Accepted Terms of Service" with the justification of "Contract Necessity". The description will be "Candidate accepted Terms of Service." followed by a log of their IP address at time of submission.

- **Discontinuing Recognition of "Do Not Track"**: Matador Jobs Pro will no longer consider your site users' web browser "Do Not Track" setting when determining whether to load Matador's Source Tracking features. This browser-level setting is largely ignored by the big trackers and was intended to only apply to 3rd party, and not 1st party like Matador's, trackers. We recommend our users review the documentation on Matador Traffic Source Tracking features to ensure compliance with local laws and regulations.

More:

- The settings section "Application Privacy Settings" is renamed to "Consents, Privacy, and Data Security Settings" to better communicate the expanded purpose of the section.
- The description text for the Privacy Policy consent saved to the Bullhorn Candidate Consents object will now read "Candidate accepted Privacy Policy." followed by a log of their IP address at time of submission. Previously, the description was not clear, listing applied jobs.
- Removed links to the applicant's submitted files in the application transcript, which was often used as the body text of a recruiter email notification. This makes it easier for those wishing to not distribute those files when considering local privacy law compliance.
- Remove legacy method for candidate IP address and candidate Privacy Policy Acceptance tracking. Released in 3.4.0, most users never took advantage of the feature Matador included in our code to track the time and date of a Privacy Policy acceptance for a candidate. This is partly due to how complicated it was to set up, requiring no less than 6 developer filters and 4 customText and customDate fields set up in Bullhorn. With 3.7.0 and later 3.8.0, Matador now tracks this data automatically with no setup required in the Bullhorn Candidate Consents Object, and thus we are removing the legacy features in favor of the new, easier to use, replacement.

### Templating/Theming

Features:

- **New Shortcode [matador_portal]**: A new shortcode `[matador_portal]` is introduced. This shortcode will help users of "Classic Editor" and "page builders" other than the WordPress block editor to streamline access to a standard "job portal" like page with a single shortcode. [See more here.]()

- **New Shortcode `[matador_general_application_link]`**: A new shortcode `[matador_general_application_link]` and associated template functions `matador_{get}_general_application_link()` and `matador_{get}_general_application_url()` will provide a simple button link to the "General Application". This must be used in coordination with a new setting "General Application Page" to determine the destination. The purpose is to provide mostly developers an easy way to link to the General Application page, or page that contains an application not assigned to a specific job, while leaving the user space to update or change it at a later time.

- **New option for Taxonomy "method", "search"**: The matador_taxonomy() function (and associated shortcode) now accepts a new value for the 'method' argument: search. When passed 'search' the link will be to the jobs listing page (as set in settings) or jobs archive with the taxonomy argument appended as if it were a search term. If used on the jobs listing page, this will reset the search, so on the jobs listing page you will still want to use 'method=filter'. This makes it easy to include the list of taxonomy terms in your design without requiring you to have a taxonomy archive page prepared.

More:

- Updated default stylesheet to better display the `[matador_search]` and `matador_search()` shortcode/function responsiveness on small screens. The changes were designed to be minimal so as not to impact user stylesheets but make it less necessary for user stylesheet overrides to be even necessary in the first place.
- Reverted a bugfix from 3.7.7 and created a new one that changed behavior around Job Sorting on Taxonomy Archive pages. Our fix was actually too aggressive, and caused sorting rules to be not applied in some instances where they were desired. We reviewed the initial bug report after reverting the flawed patch and came up with a solution that is more reliable.
- Fixed issue where the [matador_types] and [matador_locations] alias shortcodes, when used with no passed arguments, would result in an "Illegal String Offset" PHP error. This was caused by a WordPress quirk where no arguments passed to a shortcode passes an empty string into the shortcode callback function instead of an empty array. Funnily enough, once again, we have major release with a fix for a bug that has technically existed since launch of Matador! THREE IN A ROW!

### Job Import

Highlights:

- **New Sync*: See notes on the new sync in the "Connectivity and Reliability" section of this Changelog.

More:

- Matador will now gracefully handle data that comes over the Bullhorn API as multiple values when only a single value was expected. In some cases, it will combine provided values, in others it will use only the first provided value. The reason for this change was because we found users enabled the "allow multiple values" setting in Bullhorn for standard fields that should not, in our opinion, have multiple values, like Job Title. We recommend users do not "allow multiple values" on standard Bullhorn fields, even though Matador can now handle these.
- Fixed an issue causing `dateLastModified` and/or `dateLastPublished` values to be saved in the WordPress database with timezone offset, which caused issues with "last updated" related syncing for sites in a GMT+ timezone (east of Prime Meridian).
- `dateEnd` and all `customDate` job properties from Bullhorn will now properly save as a MySQL-formatted Date & Time object. Previously, dates like these were saved to the database as Unix timestamps, which many third-party templating systems could not format easily.
- An extremely rare issue could occur when concurrent Bullhorn calls interrupt the other and, if the timing was right, result in some or all jobs being removed from the site until the next uninterrupted sync. Changes to the syncing code were added to ensure that the interrupted sync fails gracefully, instead of destructively, when this occurs. Thank you to Lara at MCM Staffing for bringing our attention to this bug.

### Applications/Submissions

Features:

- **New Company and Occupation Form Fields**: Added `companyName` or "Your Current Company" and `occupation` or "Your Current Occupation" form fields to the core form offering. Include them by passing them to the fields argument of the [matador_application] shortcode or via the `matador_default_application_fields` filters.

- **New Cover Letter and "Files" Form Fields**: Added `letter` or "Cover Letter" and `files` or "Other Files (Multiple)" form fields to the core form offering. Include them by passing them to the fields argument of the [matador_application] shortcode or via the `matador_default_application_fields` filters. Letter will save into Bullhorn as a "cover letter" while "Files", which we imagine can be repurposed to allow for submission of portfolios or examples of work and/or answers to assessments, will be saved into Bullhorn as "other" type files.

- **Select Candidate "Owner" Setting**: Prior to this release, the "owner" in Bullhorn of a new candidate would be the API User. Certain agencies would prefer to set the "owner" based on the Job's "ownership" and a new setting will allow for this. Select from 'API User', 'Job's "Owner"', or 'Job's "Published Contact Recruiter"'.

- **Existing Candidate's Name Not Updated**: Matador will no longer update a Candidate's name after an application where a matching candidate record was found. Assumption shall be that a Candidate's name was correct on initial application. A few rare situations occurred where bad user input on a subsequent application changed a record in an unexpected way. On that note, if a candidate makes submission with a misspelled first name or nickname instead of legal name, a change will now need to be made by the contacting recruiter.

- **Removed Email Validation**: With this update, all email address validation is removed from the plugin. Our email address validation was too strong and based on an outdated international standard (RFC2822) which didn't allow for certain international characters in the email address or name as well as certain internationalized URLs after the `@` symbol. New email address standards (RFC6854) are now so relaxed that it is consensus among developers that there is no reliable way to validate the format of an email address without creating false negatives. Further, assuming that risk is deemed worthwhile, even a properly structured email address that passes validation does not a guarantee that an email box even exists at that address. For those reasons we will no longer validate email addresses.

More:

- Form processing used to reject all empty() values during processing, which included `false` and zero numbers. We've encountered use cases where saving those as-is are necessary. As a result, form processing will now accept zeros and `false` while still properly rejecting null and empty values.
- Fixed a bug that could occur when a custom form field passes a string to a field that expects an array of values. Matador will now verify the string value and convert it to a single-member array containing that string.
- Fixed an error being caused by changes to the Bullhorn API that now enforce previously unenforced length limits to certain fields. User input that exceeds the limits will now be truncated at the character limit.
- Added handling for changes to the Bullhorn API (or its resume processor) that now return `HTTP 500 Internal Server Error` errors instead of the former `HTTP 400 Bad Request` errors when a resume is submitted that cannot be processed.
- Standardized the methods Matador checks the uploaded file size and extension across various systems (client side validation, server-side validation, etc). Now, in order to change the allowed file types and max uploaded file size, a change only needs to be made in one place.
- Matador can now set the "File Type" of files saved into Bullhorn. Previously, when a form accepted multiple files, all would be saved as "Resume."
- To make accessing submitted files easier, a new dialogue box was added to the Submission record with links to the files and, if processed, the raw data from the resume processor.
- Improved handling of Field Labels in the Submission transcript (which is saved to the `post_content` field of the WordPress Submissions post type). The transcript is used both as a backup for the application data in the event of an issue and also as default content in generated emails for recruiters and applicant email confirmations. The field labels will now more consistently draw their values from the application structure as modified by the user's custom code or the Advanced Applications extension.
- Added developer filter `matador_application_transcript_include_field` to be used to remove items from the application transcript. This might include confidential applicant demographics data and other sensitive information that shouldn't be readily accessible or sent via email.
- Removed links to the applicant's submitted files in the application transcript, which was often used as the body text of a recruiter email notification. This makes it easier for those wishing to not distribute those files when considering local privacy law compliance.
- Submissions (Applications) were showing as available for inclusion in the WordPress menu builder even though they would not load on the site front end. This was unintentional and has been changed; submissions will not be available as options for WordPress menu builder items.

### Notifications/Emails

*Note: some items that also belong in this category were previously included in the Privacy and Submissions sections.*

Highlights:

- **Recruiter Notifications Bullhorn Deeplink**: Recruiter Notification emails will now include a deeplink into Bullhorn if the email setting is set to "send email after the candidate sync".

More:

- Added filter `matador_email_additional_headers` to allow developers to add additional headers to Matador-generated emails.
- Added developer filter `matador_email_allowed_html_tags` to give users granular ability to extend which HTML tags are allowed in Matador emails. The use case, notably, is from Matador user Nils with WebReact who explained that custom XML tags readable by MSExchange and Outlook can add interactivity to emails on that platform but the XML tags were being stripped by Matador's security routine. Now a user like Nils can teach Matador that those XML tags are safe to allow in the email.

### General

- **Added WP-CLI command `wp matador sync`**: Our first WP-CLI commands are live! This first command, `wp matador sync` runs the full sync process from WP-CLI. This can be used on webhosts with system cron options and WP-CLI to disable Matador's sync and rely on a custom sync scheudle. Future updates will expand WP-CLI commands.

- **Added Analytics:** Matador will now report simple annonymous analytics when certain actions occur, including plugin activation, upgrade, and connection to Bullhorn. The purpose for these analytics is to help us understand our users' needs and provide more reliable support. As per our terms of service, we will also share some of these analytics with Bullhorn. Bullhorn likes to know who is using Matador, and accounts marked as Matador users will recieve an enhanced support experience, so everyone wins!

- **Update to Software Licensing and Automatic Updates**: Completely overhauled Matador's handling of Licensing and software updates, fixing many bugs and adding features and utility:
  - Previously Matador would not deactivate a url from a settings change. This would result in users needing to manually deactivate URLs from MatadorJobs.com. Now, a button allows manual deactivation and changing the key triggers automatic deactivation, making it much easier for site owners to authorize production sites after development.
  - Matador respects more wildcard Staging and Development URLs, and will honor `WP_ENVIRONMENT_TYPE` variables when considering whether to consume an activation against your license.
  - Matador will check your activation every four hours and detect if an external deactivation was made, which helps keep sites working as expected.
  - Matador plugins should now play nice with WordPress Automatic Plugin Updates (since WordPress 5.5).
  - When attempting a site activation, Matador will provide more detailed descriptions of issues, including where helpful, a link to MatadorJobs.com to resolve the issue.

More:

- Fixed issue causing PHP 5.6 incompatibility. We strongly recommend our users run sites powered by PHP 7.4 or later, however.
- Fixed issues causing PHP 8.1 incompatibility. Code written for the Traffic/Source feature issues a `PHP NOTICE` but fixing it will make the code not valid for PHP 5.6. This will updated prior to Matador Jobs 3.9.0 or 4.0.0 which will require PHP 7.4 or later for Matador Jobs.
- Revised some use of PHP's DateTime class to use PHP's DateTimeImmutable instead, which prevents some rare cases where dates could be changed in unexpected ways.
- To further support extension development, some functions were converted to static functions.
- Refactored how third-party plugin/theme support is included in the codebase, properly isolating it into separate files for ease of maintenance and code readability.
- Updated dependence jQuery.Validation from 1.17.0 to 1.19.5
- Updated dependency mustache/mustache from 2.13.0 to 2.14.2

== Previous Versions ==

- See CHANGELOG.md