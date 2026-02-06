# Release Notes IceHrmPro

## Release note v35.0.0

### ☘️ New Features

#### Enhanced Travel Management
* Travel module completely rebuilt with a modern interface.
* Trip classification: Domestic, International, or Regional.
* Transportation modes: Airplane, Train, Bus, Ride-hailing, Taxi, Personal Vehicle, Rental Car, Company Car, Ferry, Metro, Bike, Helicopter, and more.
* Booking information: Flight/booking confirmation numbers, airline and flight number tracking.
* Project & budget integration with multi-currency support.
* Status change history for complete request tracking.
* Admin override capability to approve or reject any travel request.
* Step-by-step modal form for creating travel requests.
* Location picker with map integration.

#### Project Time Tracking
* New Project Detail View with rich statistics (total hours, approved vs pending, team members, months active).
* Time by Employee chart: Horizontal grouped bar chart showing total, approved, and pending hours.
* Monthly time breakdown chart for tracking project hours over time.
* Team management: Add/remove employees from projects directly.
* Clients module merged into Projects module.

#### Employee Profile Enhancements
* Employee profile images now displayed across all modules (leave, attendance, performance, training, expenses, overtime, travel, salary, documents, forms).
* New Leave tab on employee profile showing leave history and balances.
* New Attendance tab on employee profile for quick access to attendance records.
* Manager profile image shown on employee profiles.
* Renamed "Deactivate" to "Resigned" for clearer terminology.
* Dedicated view for resigned employees with proper filtering.

#### Settings Module Redesign
* Search functionality to quickly find settings by name or description.
* Settings ordered logically within each category.
* Hidden deprecated settings.
* Tabs only show if they have visible settings.
* Switch components for Yes/No settings.

#### Performance Management Improvements ([ Premium Feature ](https:///buy-icehrm-modules))
* Bulk performance review creation for multiple employees at once.
* Bulk feedback request creation.
* Feedback requests only available for pending reviews.
* Default status set to "Pending" for new reviews and feedback requests.
* Improved self-feedback UI.

#### Sandwich Leave Support ([ Premium Feature ](https:///buy-icehrm-modules))
* New sandwich leave setting for leave types.
* When enabled, leaves between holidays and weekends are counted as full days.
* New switch component for sandwich leave toggle.

#### Insights & Analytics Module ([ Premium Feature ](https:///buy-icehrm-modules))
* New **Insights** module with comprehensive analytics dashboards for leave, attendance, and timesheet data.
* Leave Insights: Leave summary, monthly/weekly patterns, leave type distribution, and departmental breakdown.
* Attendance & Timesheet Insights: Combined dashboard for attendance patterns and project time tracking.
* Global Period Selector: Filter all insights by 6 months, 1 year, 2 years, 3 years, all time, or custom date range.

#### Payroll Configuration Module ([ Premium Feature ](https:///buy-icehrm-modules))
* Redesigned payroll configuration with flexible column management.
* Built-in code editor for custom calculation functions with syntax validation.
* Payslip template designer with HTML/CSS support and preview.
* Batch payroll processing with multi-currency support.
* Assign payroll data fields to specific employees.
* Protection against deleting payslip templates in use.

#### Other New Features
* Company Overview module providing organizational overview and statistics.
* Advanced Reports module replacing the legacy reports system.

### 💼 Improvements

* Modernized modals for consistent behavior across the application.
* Loading states on save buttons with better error handling.
* Updated menu names for clarity.
* Highlighted first-level menus.
* Removed animating icons for cleaner look.
* Updated icons across modules.
* Modernized User Leave, Dependent, Emergency Contacts, Qualifications, and Travel modules.

### 🐛 Bug Fixes
* Fixed loading save button in step form modals.
* Fixed loading button state when entry saving fails.
* Fixed subordinate travel requests display.
* Fixed location field in travel user module.
* Fixed location component layout issues.
* Fixed double chart rendering in insights.
* Fixed advance attendance view display.
* Fixed first profile load issue.
* Fixed "switched to" styling issues.
* Fixed form fields with display:none rendering.
* Improved cache fallback when memcache is not available.

### 🗑️ Modules Removed
* Legacy Charts Module (replaced by Insights).
* Legacy Leave Charts Module (replaced by Insights).
* Legacy Data Module.
* Legacy Clients Module (merged into Projects).
* Legacy Report Files Module (replaced by Advanced Reports).

## Release note v34.1.0.PRO

### ☘️ New Features
* Ability to switch between Admin and User views.
* Improvements to candidate feedback section.

### 🐛 Bug Fixes
* Fix custom field order.
* Error displaying leave approval requests.
* Fix SMTP email sending on php 8.1
* Fix loading employee leave report, when downloading the report for all employees.

## Release note v34.0.0.PRO

### ☘️ New Features
* [Learning Management](https://icehrm.com/explore/docs/adding-a-new-course/).
* [Task Management](https://icehrm.com/explore/docs/create-and-assign-tasks/).
* Ability to create the user for an employee, directly from the employee profile.
* Ability to [invite employees](https://icehrm.com/explore/docs/adding-your-first-employee/).
* Module for editing documents.
* Linked documents for projects and clients.

#### 💼 Improvements
* Improvements to time-sheets module.
* Show recent feedback requests at the top of the list.

### 🐛 Bug Fixes
* Making leave note optional.
* Fix issue: employee salary not displayed properly.
* Fix: issues with displaying employees for managers without direct reports.

## Release note v33.5.0.PRO

### ☘️ New Features
* [Ability to invite users easily](https://icehrm.com/explore/docs/adding-your-first-employee/#0-toc-title) (no need to create employees and users separately anymore)
* Revamp login page.
* Ability to login [using Microsoft 360 accounts](https://icehrm.com/explore/docs/sign-in-with-microsoft-entra-id-azure-ad/).
* Ability to create the user for an employee directly from the employee profile.
* Show an alert on the employee profile if the employee is not able to log in due to missing user account.
* Renamed all references of supervisor to manager, and references to subordinate to direct report.
* A setting is added to prevent managers from switching to employee profiles.
* Users can now edit submitted time-sheets, until it get approved.
* A single time-sheet can now hold upto 500 projects.
* Support creating time-sheets for up-coming weeks.

### 🏝️ Leave Request Management
* New and compact Leave Calendar.
* Support setting leave lock period for leave types (how many months an employee should with to get a leave after joining).
* Support setting leave notice period for leave types (how many days in advance a leave should be requested).
* Allow admins to apply for leaves skipping notice period.
* Show leave days on time-sheets.
* Leave reason is mandatory.
* Setting to make an attachment mandatory for selected leave types.
* Show holidays on leave calendar.
* Ability to limit a holiday to a selected leave group.
* Added a notes field to leave type.

#### 💼 Improvements in Recruitment Module
* Enabled custom fields for candidates.
* Job posts can be created with custom questions in addition to default fields.

#### 💳 Improvements in Expense Module
* Introduce additional status to employee expenses.
* Order direct report expenses by submission date by default.

### 🐛 Bug Fixes
* Add signature to local file downloads.
* Fix placeholder editing in employee profile.
* When an approval notification is clicked, the manager is redirected to the correct tab.
* Increase table size for settings.
* Better CSRF checks.

### 🧑🏻‍💻 For Developers
* Rest API endpoints for leave calender.
* Rest API endpoints for document management.
* Increased the allowed size of custom fields.

## Release note v33.0.0.PRO

### ☘️ New Features
* New UI for the employee list.
* New and compact UI for the employee profile page.
* A new Employee directory module with UI improvements.
* Widget to show a compact version of the employee directory on the dashboard.
* Ability to add custom fields for clients and projects modules.
* Improvements to search in general.
* Implement leave lock period: introduce the setting named `Leave: Lock Period After Joined Date` to prevent employees from applying for certain number of months after joining.
* Improvements to base email template.
* A new login page.
* Introduce a new rich text editor and update the editor in company documents to the new editor.
* Improved UI for viewing company documents.
* Ability to set timezone for individual employees.
* Show employees current time on profile.
* Added new fields for employees to store social security number, tax ID, and health insurance details.
* Updated filters on the teams module.

#### 💼 Improvements in Recruitment Module
* When sending application success email to candidate use the hiring manager email if available.
* Make selecting hiring manager mandatory for creating job posts.
* Send candidate interview scheduled email even if no interviewers are defined.
* Set the replyTo email for interview scheduled email sent to candidates to the email of the hiring manager.
* Remove seconds from date/time when setting the time for interview.
* Allow setting the duration of the interview in minutes and update the Google Calendar event created for the interview accordingly.
* Reminder to connect to Google calendar if the Google calendar is not connected when loading candidates.
* Add DOB to the candidate profile.
* Switch the tabs order under candidates profile to show the list of interviews as the first tab.
* Update google calendar event only when parameters related to the Google calendar event is updated.
* Stop updating candidate hiring stage automatically when an interview is scheduled.
* Update hiring stages to make it cleaner and focused.

#### ⏰ Improvements in Attendance Module
* UI upgrade to attendance listing.
* Show user location for attendance record on Google Maps.
* Ability to change the required attendance length for a day using the setting `Attendance: Start Overtime Hours`.
* Improve the UI for attendance status.

### 🐛 Bug Fixes
* Fix: Sorting order in Leave Management module for leave periods and Holidays tab.
* Fix: An issue with leave accrual calculation by calculating the accrual based on number of days passed since the start of the leave period or joined date.
* Fix: Default employee avatar is created based on the employee name if no profile image is uploaded.
* UTF-8 encoding issues in API responses.
* Fix the issue with long company names in the top navigation bar.
* Remove delete access to the managers from document types.
* Fix: employee expense link in task list.

### 🍓 Other Minor Improvements
* Remove view button from assets module.
* Performance management: show most recent reviews, feedbacks and goals at the top of the listing.
* New leave application emails can be CCed or BCCed to upto 10 stakeholders using the settings `Leave: CC Eamils` or `Leave: BCC Emails`.

### 🧑🏻‍💻 For Developers
* Updated the [fontawesome](https://fontawesome.com/start) version to v6.4.0
* Add the ability to set the date time format for date time fields.


## Release note v32.4.0.PRO

### 🧲 New features
* Ability to search by employee name in all modules.
* Introducing [IceHrm extension market-place](https://icehrm.com/explore/extensions/).

### 🧑🏻‍💻 For developers
* PHP 8.1 and MySQL 8.0.32 support.
* Updated docker setup to use PHP 8.1 and MySQL 8.0.32.
* [Custom extension development](https://github.com/gamonoid/icehrm#extend-icehrm-with-custom-extensions).

## Release note v32.3.0.PRO

### 🧲 New features
* New applicant tracking interface.
* Improvements to recruitment emails.
* Improvements to leave management emails.
* New UI for scheduling interviews and Google calendar sync for interviews.
* Ability to provide employees access to admin only modules with custom user roles.

### 🐛 Bug fixes
* Some minor fixes to validations.
* Fixes for performance review feedback templates.
* Fixes for delayed payroll processing.
* Fix: candidates are not able to submit CVs when the API is disabled.

## Release note v32.2.0.PRO

### 🧲 New features
* New interface for candidates to apply for jobs.
* Creating google calendar events when applying / approving leave.
* Delayed payroll processing.
    * Now the payroll is processed using a background cron job. This makes it possible to process a huge amount of employee data in a single payroll. 
* Bulk payslip generation.
    * After the payroll is processed employees will be able to download the payslip via `My Documents` module.
    * Admins can download the payslips for employees via `Document Management` module.
    * Employees will be notified when a new payslip is available.
* Adding a new tab to `Documents` module for downloading payslips.

### 🧑🏻‍💻 For developers
* IceHrm developer shell.

### 🐛 Bug fixes
* Fix: employees are not able to edit own profile.
* Fix: Pagination issues when searching.
* Fix: Not being able to add long text values to custom field options.
* Fix Payslip download issue.
* Fix: Payroll editable fields after the first page cannot be edited.

## Release note v32.1.0.PRO

### 🧲 New features

* PHP 8 support
* Add department to attendance status filter
* Add employee management reminders
* Add leave and expense management reminders

### 🐛 Bug fixes
* Fix table pagination when filters are applied
* Fix the issue with showing employee name on attendance status
* Fixed the issue with loading some approval requests.

### 🧑🏻‍💻 For developers
* AdoDB is replaced with a mysqli based fast and simple ORM
* URL based API
* Sort attendance API response based on time


### 🛡️ Security improvements
* Fixing [code injection](https://github.com/gamonoid/icehrm/issues/303) during the installtion.

## Release note v32.0.0.PRO

### 🧲 New features

* Employee work history module.
* UI improvements to attendance module.
* UI Improvements to employee filed names module.
* Restructuring main menu.
* Adding non-binary gender.
* Payroll template design improvements.
* Automatically set payroll column order.
* Improvements to payroll calculations.

### 🐛 Bug fixes
* Change attendance punch In/Out labels based on users' current attendance status.
* Payroll - fix issue with custom calculations with no variables.

### 🧑🏻‍💻 For developers
* Ability to process data import files using custom javascript code.
* Ability to return strings from payroll column functions.
* Ability to access employee custom field data from payroll custom functions.

### 🗑️ Features removed
* Removing photo attendance.

### 🛡️ Security improvements
* Fixing recently detected XSS issues.

## Release note v31.0.0.PRO

### 🧲 New features

* Redesigned performance review module.
* Improvements to leave type form.
* Allow filtering employee skills using either skill or the employee.
* Employee training sessions ability to filter by employee.
* Improvements to employee filters in education, certifications and languages.
* Add category and employee to employee expense filters.
* Allow filtering leave rules by leave type.
* Ability set employee status and daily goals.

### 🐛 Bug fixes
* Filtering fixed on monitor attendance page.
* Filters on employee travel request are fixed.
* Fix issue: searching from any other page than first page is not showing search results.
* Fix filters for employee projects.
* Changes to employee field names are now correctly reflected on employee profile.
* Fix company document visibility issues.
* Issue with saving leave rules.
* Show active employee count under connection module.

### 🗑️ For developers
* Add slider component.
* Add editor js.

### 🗑️ Features removed
* Announcement and discussions modules are removed.
* Module grouping feature is removed.

### 🛡️ Security improvements
* Fixing [https://github.com/gamonoid/icehrm/issues/284](https://github.com/gamonoid/icehrm/issues/284)
* Fixing [https://github.com/gamonoid/icehrm/issues/285](https://github.com/gamonoid/icehrm/issues/285)


## Release note v30.0.0.PRO

### 🛡️ Security improvements

* This release is fixing some critical security issues related to file storage. All IceHrm installations should be upgraded to this version immediately.
  Please review instructions provided under [this link](https://icehrm.gitbook.io/icehrm/getting-started/securing-icehrm-installation) to make sure your IceHrm installation is secure.

### 🧲 New features

* Ability to use Amazon S3 as a secure file storage for IceHrm [find instructions here](https://icehrm.gitbook.io/icehrm/getting-started/using-aws-s3-for-icehrm-file-storage)
* Secure local file storage
* Connection module for showing critical errors in installation and sending extracting system information
* Improvements to HR forms module. Now you can add signatures to HR forms and download the completed form as a PDF
* Ability to change AWS region via settings

### 🐛 Bug fixes
* Displaying employees list with non-UTF employee names
* Fix extension loading issue. This fixes the issue with loading new extension created following the [documentation](https://icehrm.gitbook.io/icehrm/developer-guide/creating-first-extension).
* Fix the issue with editing employee fields

## Release note v29.0.0.PRO

### 🧲 New features

* Much better UI for recruitment setup and job positions module.
* SAML support [https://icehrm.gitbook.io/icehrm/api-and-single-sign-on/sign-in-with-saml-okta](https://icehrm.gitbook.io/icehrm/api-and-single-sign-on/sign-in-with-saml-okta)
* UI improvements on leave settings module.
* Ability to control who can see employee documents
* New custom field module. With this module users can manage all the custom fields via a single module.
* Adding custom field support for company structure.
* UI improvements for client module.
* UI improvements for both admin and employee expense modules.
* Introducing encrypted settings.
* Adding additional fields such as total time to employee time tracking report.
* Improvements to icehrm custom extension development [https://icehrm.gitbook.io/icehrm/developer-guide/creating-first-extension](https://icehrm.gitbook.io/icehrm/developer-guide/creating-first-extension)

### 🛡️ Security improvements

* More restrictive criteria for user passwords.
* Removing support for legacy API tokens. (if you are using the mobile app your users will need to re authorize)
* Removing unused custom field values.

### 🐛 Bug fixes
* Only relevant settings are displayed, under the `Other` tab on settings module

## Release note v28.2.0.PRO

###  New features

* 🦠 💉 Custom extensions [https://icehrm.gitbook.io/icehrm/developer-guide/creating-first-extension](https://icehrm.gitbook.io/icehrm/developer-guide/creating-first-extension)

### 🐛 Bug fixes

* Fixing inability to filter employee documents
* Fixed the issue with selecting projects when adding timesheets details
* Fix issues occurred due to incorrectly configured API

## Release note v28.1.0.PRO

### 🧲 New features

* Improved tables for displaying data in several modules
* Faster data loading (specially for employee module)
* Initials based profile pictures
* Re-designed login page
* Re-designed user profile page
* Improvements to filtering
* New REST endpoints for employee qualifications 

### 🐛 Bug fixes
* Fixed, issue with managers being able to create performance reviews for employees who are not their direct reports
* Fixed, issues related to using full profile image instead of using smaller version of profile image
* Changing third gender to other
* Fixed, not listing countries on job application page
* Improvements and fixes for internal frontend data caching

## Release note v27.1.0.PRO

### New features

* UI/UX improvements
* Custom user role permissions
* Employee edit form updated
* Employee daily task list
* Attendance and employee distribution charts on dashboard
* Improvements to company structure and company assets module

[Read more about the release here](https://medium.com/icehrm/some-new-compelling-changes-to-icehrm-eba62c40ef9f)

### 🐛  Bug fixes
* Fix leave carry forward issues, when employee leave periods used in combination with leave rules

## Release note v27.0.2.PRO

This fixes some major issues found in v27.0.1.PRO

### 🐛  Bug fixes

* Filtering across whole application was broken and now it's fixed
* Fixed the issue related to photo not being shown to the admin when photo attendance is enabled

### 🧑🏻‍💻 For developers

* We have added support for vagrant development environment based on Debian 10 / PHP 7.3 \(with Xdebug\)  / Nginx / MySQL

## Release note v27.0.0.PRO

### New features

* UI/UX improvements \(new fonts / better spacing\)
* Payroll module improvements
* Security improvements to password policy
* Adding total leave days column, when displaying employee leave data
* Adding more leave information to the email sent to managers for approving leave requests
* Ability to limit leave requests to full-day and half-day
* Albanian language is now available
* Ability to deploy using docker

### For developers

* Developer environment based on docker [https://www.youtube.com/watch?v=sz8OV\_ON6S8](https://www.youtube.com/watch?v=sz8OV_ON6S8)
* [Developer guide](https://icehrm.gitbook.io/icehrm/developer-guide/create-new-module)
* Fully supports all php versions &gt;= 5.6 upto v7.3 \(php 5.6 support is deprecated and not recommended\)

### Bug fixes

* Fixes to newly found vulnerabilities \([https://github.com/gamonoid/icehrm/issues/213](https://github.com/gamonoid/icehrm/issues/213)\): credits to: [Talos](https://talosintelligence.com/)
* Fixing leave module, calculations for complex leave rule combinations
* Fixed the travel request approval for managers
* Fixed the issue with attendance source IP display
* Fixing Api issues in PHP 7.3

## Release note v26.9.0.PRO

### New features

* Teams module for creating teams in company
* Enable custom user roles
* Introducing fine-grained user role management by adding restricted user roles
* Introducing new Gender group 'Divers' for employees and candidates \(according to German intersex law\)

### Bug fixes

* Show only active job posts
* For employee leave period based leave types - fix available leave balance amount when applying for a leave
* Fix eave calculations using leave rules depends on employee experience

## Release note v26.7.0.PRO

### Leave Management

* Leave periods can be based on employee joined date or confirmation date
* Now you can see the detailed leave calculation for each leave type in leave entitlement

### Data Import Improvements

* Download file template via data importer \(the file will be automatically generated using fileds in data importer\)
* Add a common data importer which makes it easy to create custom data importers
* Custom field data can now be imported
* Improvemnts to import file validations

### Payroll & Payslip

* Payroll columns can have simple java script based calculations
* Any employee information \(general fields or custom fields\) can now be shown on payslip
* Any employee information \(general fields or custom fields\) can be used for payroll calculations

### Other

* Improvements to automated E2E test

## Release note v26.6.0.PRO

### Improved Recruitment

* Now you can add rich text when creating Job posts
* Ability to select a hiring manager for each job post, who is getting email updates about now candidates and interviews
* Improvements to the page job position page such as showing hiring manager details and simplified application form
* Send a confirmation email to candidate after receiving job application
* Ability to schedule interviews with multiple interviewers
* Sending email notifications to interviewers and hiring manager when an interview is scheduled
* Fixing issues related to sharing jobs posts on social media

### Leave Module

* Ability to create leave rules targeting specific leave periods. \(Example use case: configuring different leave carry forward settings for each leave periods\)
* Fixing calculation issues occurred due to having future leave periods

### Improvements to Attendance Tracking

* Tracking IP and location of the employee when marking attendance, this is done when updating attendance via mobile
* Ability to control location tracking via mobile using server side settings
* Compatible with location tracking with latest version of Icehrm Mobile app on AppStore \([https://apple.co/2Yrtxoy](https://apple.co/2Yrtxoy)\) and Google Play \([http://bit.ly/2OkMmKe](http://bit.ly/2OkMmKe)\)

### Other Features

* Some Improvements to UI such as updating Icons and upgrading font-awesome to its latest version
* Improvements to translations

### Other Fixes

* Order projects by name on Timesheet project listing \(This is to make it easier to edit timesheets with many projects\)
* Link home page user profile to employee profile update page
* Fix issues related to configuring Api with mobile app

### Security Improvements

* Upgrade npm missing dependencies

## Release note v26.4.0.PRO

### Features

* Add staff directory module 
* Update client-side js to ES6 
* Compatible with IceHrm Mobile App 
* Use npm libraries when possible
* Add gulp build for frontend assets
* Allow generating QR code with rest api key \([https://github.com/gamonoid/icehrm/issues/169](https://github.com/gamonoid/icehrm/issues/169)\)
* Updated readme for development setup with vagrant
* Changes to leave entitlement layout
* Show leave breakdown properly on leave entitlement
* Display the leave type or rule affecting the leave entitlement
* Order all leave listings by latest start date
* Exclude PTO and carried forward leave days from accrued leave calculation
* New api endpoints for leave and expense

### Fixes

* Add missing employee details report
* Fix: Labels of 'Employee Custom Fields' not displayed 
* Fix: Work week for all counties can not be edited 
* Fix: Custom fields are not shown under employee profile 
* Fix: Additional buttons shown below timesheet list 
* Updates to Italian translations  by [https://github.com/nightwatch75](https://github.com/nightwatch75)
* Fix issue: incorrect leave carry forward
* Fix: Work week for all counties can not be edited
* Error sending notification when expense is approved 
* Fix: When cancelling a leave request no email is sent to the manager \([https://github.com/gamonoid/icehrm/issues/158](https://github.com/gamonoid/icehrm/issues/158)\)

## Release note v25.1.0.PRO

### Features

* Multiple leave period support
* Arabic language support

### Fixes

* Fix PHP v5.6 compatibility issues

## Release note v25.0.0.PRO

### Features

* Performance review module
* Company asset management module
* Improvements to email templates
* Support for Serbian, Portuguese, Norwegian, Swedish and Dutch languages

### Fixes

* Allow making non required date fields empty
* Fix leave approval issue for languages other than English

## Release note v24.0.0.PRO

This release includes some very critical security fixes. We recommend upgrading your installation to latest release.

### Features

* Allow passing additional parameters to payroll predefined methods
* Pass leave type name in function field to get leave count for a given type
* Make document valid until field optional
* Add employee name to payroll report
* Show supervisor name on employee profile
* Add custom fields to employee report
* Add filter by status feature to subordinate time sheets
* Make document attachment mandatory

### Security Fixes

* Fix missing login form CSRF token
* Fix risky usage of hashed password in request
* Fixing permission issues on module access for each user level
* Prevent manager from accessing sensitive user records

### Other Fixes

* Hide employee salary from managers
* Prevent manager from accessing audit, cron and notifications
* Prevent managers from deleting employees
* Remove manager access from employee history
* Fix recruitment module security issues
* Fix: Training coordinator is not able to edit training session
* Validate overtime start and end times
* Fix: Employee "Subordinate expense" status filter is not visible
* Do not allow employees or managers to delete expense requests which are not pending
* Fix issue: employee can download draft payroll

## Release note v23.0.0.PRO

This release include some security fixes. We recommend upgrading your installation to latest release. Now feature wise IceHrm cloud and pro are same

### Features

* Recruitment module
* Announcement module
* Conversation module - company public discussion board
* Loading last used module when revisiting application
* Finnish language support \(Beta\)
* Improvements to German, Italian and Chinese language translations
* Allow quickly switching languages
* Improvements to security for preventing possible LFI attacks
* Allow manual date inputs
* Custom fields for travel requests
* Allow importing approved overtime hours into payroll
* Add date and time masks

### Fixes

* Fix logout cookie issue, by clearing remember me cookie when logging out
* Improve privacy for GDPR
* Improvements to file upload field
* Fix issue: attendance rest end point not working on php 5.6
* Fix, leave request attachment can not be seen

## Release note v22.0.0.PRO

### Features

* Support multiple leave groups \(\)
* Allow filtering by all leave statuses
* New view for leave time-line
* Improvements to leave module documentation
* Improvements to module naming  

### Fixes

* Remove unwanted default leave periods
* Fix issue with approved leave cancellation
* Fix issue: filter dialog default values are not selected
* Fix issue: department head can be an employee outside the department
* Fix issue: department head or supervisor \(who has manager leave access\) can't use switch employee feature
* Fix issue: employee name is not visible on report if middle name is empty
* Fix issue with viewing files attached to leave requests

## Release note v21.1.0.PRO

### Features

* Creating leave rules based on years of experience of employees
* UI improvements \(help button and error messages\)
* Allow adding placeholders to test fields
* Improvements to German Translations

### Fixes

* Fix leave rule selection issue when leave groups are used
* Fixing notification issues

## Release note v21.0.0.PRO

### Features

* Improvements to leave/PTO module
* Ability to carry forward leave balance indefinitely

### Fixes

* Fix outdated Mail library
* Fix issue: JSON strings not supported in GET request

## Release note v20.3.0.PRO

### Features

* Employee and Attendance REST Api Released
* Import/Export for Payroll Configurations
* Ability to import employee approved time sheet hours to payroll
* Ability to import approved expenses into payroll
* Swift Mailer based SMTP support \(no need to install Net\_SMTP anymore\)
* Add direct Edit button on employee list

### Fixes

* Fix DB connection issues due to special characters in password
* Fixes for custom field saving issues in mysql v5.7.x

## Release note v20.2

### Fixes

* Fix for resetting modules

## Release note v20.1

### Features

* Compatible with MySQL 5.7 Strict Mode
* PSR-2 compatible code
* Employee History Module
* Staff Directory

### Fixes

* Fix: password reset not working
* Fix: limiting selectable countries via Settings

## Release note v19.4

### Features

* DB migration support - no need to upgrade your database manually for every release
* Adding calender view to timesheet module
* Ability to quickly edit timesheets using an editable table
* Subordinate timesheets can be filtered by employee

### Fixes

* Fix for setting user language
* Fixing issues with Employee time entry report
* Fix for displaying custom fields under employee profile page

## Release note v19.0

### Features

* DB migration support - no need to upgrade your database manually for every release
* Payroll Module Improvements
* Development environment and vagrant machine is available now
* Department heads who can manage all employees attached to a company structure

## Release note v18.0

### Fixes

* Fix issue: admin dashboard translations are not working

## Release note v18.0

### Features

* Translations \(beta\) for German, French, Polish, Italian, Sinhala, Chinese, Japanese, Hindi and Spanish
* PDF Reports
* Ability to specify department heads
* Add advanced custom fields to employees via UI
* Allow indirect admins to approve travel requests
* Adding more languages to Language meta data table
* Improvements to report module
* Ability to select sections for placing custom fields on employee detail view screen
* Introducing clone button
* Unlimited custom fields for employees
* PDF report for monitoring time employee spent on projects
* Report files module - Allow downloading all previously generated reports

### Fixes

* Fix: subordinates are not showing beyond first page issue

## Release note v17.1

#### Fixes

* Fix: recruitment module candidates not loading issue
* Fix report files issue

## Release note v17.0

#### Features

* Introducing clone button
* Unlimited custom fields for employees
* PDF reports using wkhtmltopdf
* Introducing pdf report for monitoring time employee spent on projects
* Report files module - Allow downloading all previously generated reports

#### Fixes

* Attendance sheets module - allow setting overtime calculation period
* Remove pwd button from user roles tab
* Allow viewing paid invoices \(Enterprise only\)
* Adding missing jQuery UI

## Release note v16.1

#### Fixes

* Fix LDAP login issues
* Prevent webserver from printing errors on screen
* Allowing "." and "-" on username

## Release note v16.0

#### Features

* Payroll reports module for IceHrm Enterprise
* Improvements to salary module
* Employee History Tracking module - Track all important changes to an employee
* Initial implementation of icehrm REST Api
* Improvements to data filtering
* Multiple tabs for settings module
* Overtime reports - now its possible to calculate overtime for employees.compatible with US overtime rules
* A tab to list documents added under employee view
* Logout the user if tried accessing an unauthorized module
* Setting for updating module names
* Add department filter to employee leave report

#### Fixes

* Fix issue: classes should be loaded even the module is disabled
* Checking user permission before adding default module for user
* Deleting the only Admin user is not allowed
* Fixes for cron issues
* Fixes for handling non UTF-8
* Fix for non-mandatory select boxes are shown as mandatory
* Fix: Indirect supervisor full leave list not showing

## Release note v15.0

#### Features

* Training module added to IceHrm Pro
* LDAP module added to IceHrm Pro
* Clear HTML5 local storage when logging in and switching users
* Adding indirect supervisors to employees
* Allow indirect supervisors to approve leave requests
* Improve leave status change workflow
* Showing a loading message while getting data from server
* Adding a new field to show total time of each time sheet
* New report added for listing Employee Time Sheets
* Company logo uploaded via settings will be used for all email headers

#### Fixes

* Fix issue: default module URL is incorrect for Employees
* Fix date parsing issue in time sheets
* AWS phar is included only when required

## Release note v14.6

#### Features

* Adding a parameter for leave types and leave rules for setting maximum number of leave days that can be carried forward from one year to another

#### Fixes

* When a leave is cancelled, another leave can not be applied on the same day
* Fix dashboard attendance count

## Release note v14.4

#### Fixes

* Fix for Paid time off not getting bound to leave period issue
* Fix amount label name in expense module
* Fix for expenses report \(payee field is not in report\)
* Fix issue: users are not redirected to default module after login \(IcehrmPro\)
* Run cron jobs only is the file exists

## Release note v14.0

#### Features

* Expense management module
* Improvements to travel management module to change the process of applying for travel requests
* Employee document expiry notifications
* Immigration documents has been removed from travel module and users should use documents module instead
* Leave filtering with leave period
* New report add for getting travel requests
* Bunch of UI improvements including changing menu order and font sizes
* Ability to stop all notifications for certain leave types
* Add a setting to use server time for time zone defined on department that a user is attached to create new attendance records
* Improvements to admin/manager and user dashboard
* Managers allowed to view/add/edit employee documents
* New reports added for employee expenses and travel

#### Fixes

* Fix issue: leave type not included in employee leave report
* Fix invoice ordering
* Fix unavailable help links
* Remove manager access from recruitment setup
* Remove Add New button from employees module for managers
* Remove Add New button from archived and terminated employees tabs
* Fix - training module view session info from my training sessions tab not working

## Release note v13.4

#### Features

#### Fixes

* Fix employee leave report leave type field

## Release note v13.0

#### Features

* Recruitment module
* Allow managers to edit attendance of direct report employees

#### Fixes

* Employee switching issue fixed
* Fix terminated employee labels
* Fix issue with punch-in

## Release note v12.6

#### Features

* Charts module
* Code level security improvements

#### Fixes

* Employee switching issue fixed

## Release note v11.1

#### Features

* Add/Edit or remove employee fields

## Release note v11.0

#### Features

* Employee data archiving
* Leave cancellation requests
* Adding view employee feature

#### Fixes

* Improvements to date time pickers

## Release note v10.1

#### Features

* Integration with ice-framework \([http://githun.com/thilinah/ice-framework](http://githun.com/thilinah/ice-framework)\)
* Option for only allow users to add an entry to a timesheet only if they have marked atteandance for the selected period
* Restricting availability of leave types to employees using leave groups
* Admins and add notes to employees

## Release note v9.1

#### Fixes

* Add missing S3FileSystem class
* Fix issue: passing result of a method call directly into empty method is not supported in php v5.3

## Release note v9.0

#### Features

* New user interface
* Decimal leave counts supported

## Update icehrm v8.4 to v9.0

* Make a backup of your icehrm db
* Run db script "icehrmdb\_update\_v8.4\_to\_v9.0.sql" which can be found inside script folder of icehrm\_v9.0
* remove all folders except app folder in icehrm root folder
* copy all folders except app folder from new installation to icehrm root folder

## Release note v8.4

#### Fixes

* Fix leave carry forward rounding issues
* Fix issue: select2 default value not getting set for select2
* Fix issue: email not sent when admin changing leave status

## Release note v8.3

#### Fixes

* Fix user table issue on windows, this will resolve errors such as: \(Note that this fix has no effect on unix based installations\)
* Admin not able to view user uploaded documents
* Admin not able to upload documants for users
* Admin can not view employee attendance records
* Employee projects can not be added

## Release note v8.2

#### Features

* Instance verification added

## Release note v8.1

#### Fixes

* Fixed bug that caused a fatal error in php v5.4
* aws2.7.11 phar file replaced by a aws2.7.11 extracted files
* old aws sdk removed

## Release note v8.0

#### Features

* Admin dashbord module
* If the employee joined in current leave period, his leave entitlement is calculated proportional to joined date
* Improvements to reporting module
* Adding new employee time tracking report
* Join date for employees made mandatory
* Sending welcome email when a user is added
* Let users directly reply to admin user from any email sent out from icehrm
* All the users who are not admins must have an employee object attached
* Upgrade aws sdk to v2.7.11
* Allow employees to change password
* Use only the email address defined under user for sending mails
* Making work\_email and private\_email fields optional

#### Fixes

* Upload dialog close button issue fixed

## Release note v7.2

#### Fixes

* Some critical vulnerabilities are fixed as recommend by [http://zeroscience.mk/en/](http://zeroscience.mk/en/)

## Release note v7.1

#### Features

* Improved company structure graph
* Leave notes implementation � Supervisor can add a note when approving or rejecting leaves
* Filtering support
* Select boxes with long lists are now searchable
* Add/Edit/Delete company structure permissions added for managers
* Add ability to disable employee information editing

#### Fixes

* Make loans editable only by admin
* Fix: permissions not getting applied to employee documents
* Fix error adding employee documents when no user assigned to the admin

#### Code Quality

* Moving all module related code and data into module folders

## Release note v6.1

Leave carry forwared related isue fixed

## Release note v6.0

* Features
* Notifications for leaves and timesheets
* Leave module accrue and leave carry forward
* Employee leave entitlement sub module
* Ability to put system on debug mode
* Allow admins to see documents of all the employees at one place
* Backup data when deleting an employee
* Employee attendance report added
* Changes to time entry form in timesheet module to make time entry process faster
* Admin can make all projects available to employees or just the set of prjects assigned to them using Setting "Projects: Make All Projects Available to Employees"
* Employee document, date added field can not be changed by the employee anymore
* About dialog added for admins
* Fixes
* Fix default employee delete issue \(when the default employee is deleted the admin user attached to it also get deleted\)
* Fix user duplicate email issue
* Fix manager can not logout from switched employee
* Remove admin guide from non admin users

## Release note v5.3

* Fixes
* Fix missing employee name in employee details report

## Release note v5.2

* Fixes
* Remove unwanted error logs
* Fix attendance module employee permission issue
* Resolve warnings
* Remove add new button from subordinates module
* Adding administrators' guide

## Release note v5.1

* Fixes
* Fixing for non updating null fields
* [https://bitbucket.org/thilina/icehrm-opensource/commits/df57308b53484a2e43ef5c72967ed1cd0dc756cc](https://bitbucket.org/thilina/icehrm-opensource/commits/df57308b53484a2e43ef5c72967ed1cd0dc756cc)

## Release note v5.0

* Features
* New user permission implementation
* Adding new user level - Manager
* Fixes
* Fixing remote table loading issue

## Release note v4.2

#### Fixes

* [https://bitbucket.org/thilina/icehrm-opensource/issue/23/subordinate-leaves-pagination-not-working](https://bitbucket.org/thilina/icehrm-opensource/issue/23/subordinate-leaves-pagination-not-working)
* [https://bitbucket.org/thilina/icehrm-opensource/issue/20/error-occured-while-time-punch](https://bitbucket.org/thilina/icehrm-opensource/issue/20/error-occured-while-time-punch)

## Release note v4.1

#### Features

* Better email format for notifications
* Convert upload dialog to a bootstrp model
* Fixes
* Fix error sending emails with amazon SES
* Fix errors related to XAMPP and WAMPP servers
* Fix php warnings and notifications
* Fix company structure graph issues
* Allow icehrm client to work without an internet connection
* Fix installer incorrect base url issue
* Fix empty user creation issue

