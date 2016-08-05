IceHrm
===========

IceHrm is a [HRM software](https://icehrm.com) which enable companies of all sizes to [manage HR activities](https://icehrm.com)
properly. 

Note: IceHrm is now fully compatible with PHP 7

Installation
------------
 * Download the latest release https://github.com/gamonoid/icehrm/releases/latest

 * Copy the downloaded file to the path you want to install iCE Hrm in your server and extract.

 * Create a mysql DB for and user. Grant all on iCE Hrm DB to new DB user.

 * Visit iCE Hrm installation path in your browser.

 * During the installation form, fill in details appropriately.

 * Once the application is installed use the username = admin and password = admin to login to your system.

 Note: Please rename or delete the install folder (<ice hrm root>/app/install) since it could pose a security threat to your iCE Hrm instance.


Upgrade from Previous Versions to Latest Version
------------------------------------------------

Refer: [http://blog.icehrm.com/docs/upgrade/](http://blog.icehrm.com/docs/upgrade/)

Following is a list of features supported in each edition of icehrm
-------------------------------------------------------------------

### IceHrm Open Source Edition

![Employee Module](https://icehrm.s3.amazonaws.com/images/blog-images/advanced-employee-module.png)

 #### Employee Management

 - Basic [Employee Management](https://icehrm.com) - Store, manage and retrieve employee information when required
 - Update all employee information without having to switch employees.
 - Search employee skills, qualifications and other information<br/>easily across whole company.
 - Terminate employees while keeping data in system.
 - Re-enable temporarily terminated employees with one click.
 - Employee archive feature to archive data of terminated employee

#### Other Features

 * [Company Information Management](https://icehrm.com) - Store and manage details about how companies, departments and branches of the organisation are connected
 * Time sheets - IceHrm is a [timesheet app](https://icehrm.com) / [Open source timesheet management](https://icehrm.com) application to track time spent by employees on various projects
 * [Attendance Management](https://icehrm.com) - IceHrm can be used as a [attendance management system](https://icehrm.com) effectively for any size a company.
 * [LDAP Login](https://icehrm.com) - Users can share login with company LDAP server
 * [Travel Management](https://icehrm.com) - Module for managing travel requests

 
### IceHrm Pro Edition | [IceHrm.com](https://icehrm.com/modules.php)

IceHrm Profession version (in short IceHrmPro) is the feature rich commercial alternative for icehrm
open source version.  IceHrm Pro supports following features
    
#### Leave Management

IceHrm [Leave management system](https://icehrm.com) is only available in IceHrm Pro or Enterprise versions. IceHrm leave module is a complete [leave management system](https://icehrm.com) for any type of a company

To learn more about leave management in icehrm refer:
- [Leave Admin Guide](http://blog.icehrm.com/docs/leave-admin)
- [Configuring Leave Module](http://blog.icehrm.com/docs/leave-setup)
- [Leave Rules](http://blog.icehrm.com/docs/leave-rules)

#### Audit Trial

Sometimes you need to access audit trail for your HRM system. Audit module records all the write actions (which alters your HRM system) 
of your employees in a quickly accessible and understandable manor. This help you to identify potential issues with the way employees
are using the system.

#### Expense Tracking

[Track Employee Expenses](https://icehrm.com) with expense management module.

You can learn more about [IceHrm Pro here](http://blog.icehrm.com/docs/icehrm-pro/)

To purchase IceHrmPro please visit [https://icehrm.com/modules.php](https://icehrm.com/modules.php)

#### Training Management

Icehrm [training management system](https://icehrm.com) is for Module for managing courses, training sessions and employee attendance to training sessions.
 

### IceHrm Enterprise Edition

In addition to pro version features IceHrm enterprise cloud edition includes following features

#### [Employee History Management](https://icehrm.com)

#### [Payroll](https://icehrm.com)

IceHrm Enterprise has a full featured payroll module including [PDF salary slip generation](https://icehrm.com)

#### Candidate / Recruitment Management

Recruitment module can be used as a [applicant tracking system](https://icehrm.com) or a [recruiting software](https://icehrm.com). IceHrm recruitment management system offers
following features

![Recruitment Job Position Sharing](https://icehrm.s3.amazonaws.com/images/blog-images/recruitment-share.png)
 
- Post jobs
- Let candidates apply for these jobs
- Schedule interviews
- Track candidate progress with notes
- Share job links with linkedIn, facebook, twitter and google+ directly from icehrm

![Candidate Details](https://icehrm.s3.amazonaws.com/images/blog-images/candidates.png)

More about [recruitment module](http://blog.icehrm.com/docs/recruitment/)


Release note v18.0
------------------
### Features
 * Translations (beta) for German, French, Polish, Italian, Sinhala, Chinese, Japanese, Hindi and Spanish
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
 * Fix: subordinates are not showing beyond first page issue.


Release note v16.1
------------------
 
### Fixes
 * Fix LDAP user login issue
 * Allow creating users with username having dot and dash

Release note v16.0
------------------
### Features
 * Advanced Employee Management Module is now included in IceHrm Open Source Edition
 * LDAP Module which was only available in IceHrm Enterprise is now included in open source also
 * Initial implementation of icehrm REST Api for reading employee details
 * Improvements to data filtering
 * Multiple tabs for settings module
 * Overtime reports - now its possible to calculate overtime for employees.compatible with US overtime rules
 * Logout the user if tried accessing an unauthorized module
 * Setting for updating module names
 
### Fixes
 * Fix issue: classes should be loaded even the module is disabled
 * Deleting the only Admin user is not allowed
 * Fixes for handling non UTF-8
 * Fix for non-mandatory select boxes are shown as mandatory

Release note v15.2
------------------

### Features
 * Overtime Reports
 * Overtime calculation for california
 
### Fixes
 * Fix issue: uncaught error when placeholder value is empty
 * Log email sending success status
 * Fix broken longer company name issue
 * Make the application accessible when client on an intranet with no internet connection
 * Fix issue: when a module is disabled other modules depend on it stops working


Release note v15.0
------------------

### Features
 * Clear HTML5 local storage when logging in and switching users
 * Showing a loading message while getting data from server
 * Adding a new field to show total time of each time sheet
 * New report added for listing Employee Time Sheets
 * Company logo uploaded via settings will be used for all email headers
 
### Fixes
 * Fix issue: default module URL is incorrect for Employees
 * Fix date parsing issue in time sheets
 * AWS phar is included only when required

Release note v14.1
------------------

### Features
 * Add Quick access menu

### Fixes
 * Fix issue: salary module not loading
 * Add travel report

Release note v14.0
------------------

### Features
 * IceHrm is now fully compatible with PHP 7
 * Improvements to travel management module to change the process of applying for travel requests
 * New report add for getting travel requests
 * Improvements to user interface
 * Bunch of UI improvements including changing menu order and font sizes
 * Add a setting to use server time for time zone defined on department that a user is attached to create new attendance records
 * Improvements to admin/manager and user dashboard
 * Managers allowed to view/add/edit employee documents
 * New reports added for employee expenses and travel
 
### Fixes
 * Fix unavailable help links


Release note v13.4
-----------------

### Features
 
### Fixes
 * Fix employee leave report leave type field

Release note v13.0
-----------------

### Features
 * Recruitment module
 * Allow managers to edit attendance of direct report employees
 
### Fixes
 * Employee switching issue fixed 
 * Fix terminated employee labels
 * Fix issue with punch-in

Release note v12.6
-----------------

### Features
 * Charts module
 * Code level security improvements
 
### Fixes
 * Employee switching issue fixed 


Release note v11.1
-----------------

### Features
 * Add/Edit or remove employee fields


Release note v11.0
-----------------

### Features
 * Employee data archiving
 * Leave cancellation requests
 * Adding view employee feature

### Fixes
 * Improvements to date time pickers 


Release note v10.1
-----------------

### Features
 * Integration with ice-framework (http://githun.com/thilinah/ice-framework)
 * Option for only allow users to add an entry to a timesheet only if they have marked atteandance for the selected period
 * Restricting availability of leave types to employees using leave groups
 * Admins and add notes to employees

Release note v9.1
-----------------

### Fixes
 * Add missing S3FileSystem class
 * Fix issue: passing result of a method call directly into empty method is not supported in php v5.3


Release note v9.0
-----------------

### Features
 * New user interface
 * Decimal leave counts supported
 
Update icehrm v8.4 to v9.0
--------------------------

 * Make a backup of your icehrm db
 * Run db script "icehrmdb_update_v8.4_to_v9.0.sql" which can be found inside script folder of icehrm_v9.0
 * remove all folders except app folder in icehrm root folder
 * copy all folders except app folder from new installation to icehrm root folder
 
 
Release note v8.4
-----------------

### Fixes
 * Fix leave carry forward rounding issues
 * Fix issue: select2 default value not getting set for select2
 * Fix issue: email not sent when admin changing leave status

Release note v8.3
-----------------

### Fixes
 * Fix user table issue on windows, this will resolve errors such as: (Note that this fix has no effect on unix based installations)
 * Admin not able to view user uploaded documents
 * Admin not able to upload documants for users
 * Admin can not view employee attendance records
 * Employee projects can not be added


Release note v8.2
-----------------

### Features
* Instance verification added

Release note v8.1
-----------------

### Fixes
* Fixed bug that caused a fatal error in php v5.4
* aws2.7.11 phar file replaced by a aws2.7.11 extracted files
* old aws sdk removed

Release note v8.0
-----------------

### Features
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
* Making work_email and private_email fields optional


### Fixes
* Upload dialog close button issue fixed

Release note v7.2
-----------------

### Fixes
* Some critical vulnerabilities are fixed as recommend by http://zeroscience.mk/en/

Release note v7.1
-----------------

### Features
* Improved company structure graph
* Leave notes implementation � Supervisor can add a note when approving or rejecting leaves
* Filtering support
* Select boxes with long lists are now searchable
* Add/Edit/Delete company structure permissions added for managers
* Add ability to disable employee information editing

### Fixes
* Make loans editable only by admin
* Fix: permissions not getting applied to employee documents
* Fix error adding employee documents when no user assigned to the admin

### Code Quality
* Moving all module related code and data into module folders

Release note v6.1
-----------------

Leave carry forwared related isue fixed

Release note v6.0
-----------------

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
* Fix default employee delete issue (when the default employee is deleted the admin user attached to it also get deleted)
* Fix user duplicate email issue
* Fix manager can not logout from switched employee
* Remove admin guide from non admin users

Release note v5.3
-----------------

* Fixes
* Fix missing employee name in employee details report

Release note v5.2
-----------------

* Fixes
* Remove unwanted error logs
* Fix attendance module employee permission issue
* Resolve warnings
* Remove add new button from subordinates module
* Adding administrators' guide

Release note v5.1
-----------------

* Fixes
* Fixing for non updating null fields
* https://bitbucket.org/thilina/icehrm-opensource/commits/df57308b53484a2e43ef5c72967ed1cd0dc756cc

Release note v5.0
-----------------

* Features
* New user permission implementation
* Adding new user level - Manager

* Fixes
* Fixing remote table loading issue

Release note v4.2
-----------------

### Fixes
* https://bitbucket.org/thilina/icehrm-opensource/issue/23/subordinate-leaves-pagination-not-working
* https://bitbucket.org/thilina/icehrm-opensource/issue/20/error-occured-while-time-punch


Release note v4.1
-----------------

### Features
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