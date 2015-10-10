IceHrm
===========

Installation
------------
Download the latest release https://github.com/thilinah/icehrm/releases/latest

Copy the downloaded file to the path you want to install iCE Hrm in your server and extract.

Create a mysql DB for and user. Grant all on iCE Hrm DB to new DB user.

Visit iCE Hrm installation path in your browser.

During the installation form, fill in details appropriately.

Once the application is installed use the username = admin and password = admin to login to your system.

Note: Please rename or delete the install folder (<ice hrm root>/app/install) since it could pose a security threat to your iCE Hrm instance.

Release note v12.6
-----------------

### Features
 * Charts module
 * Code level security improvements
 
### Fixes
 * Employee switching issue fixed 

Release note v10.2
-----------------

### Fixes
 * Improvements to date time pickers 
 * Fixing fatal error due to non writable log file
 * Latest changes from ice-framework v2.0
 

Release note v10.1
-----------------
### Features
 * Integration with ice-framework (http://github.com/thilinah/ice-framework)
 * Travel module
 * Meta data module 
 * Option for only allow users to add an entry to a timesheet only if they have marked atteandance for the selected period
 * Restricting availability of leave types to employees using leave groups

### Fixes
 * Add missing S3FileSystem class
 * Fix issue: passing result of a method call directly into empty method is not supported in php v5.3


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


Update icehrm v8.2 to v8.3
--------------------------

Copy server.includes.inc.php from v8.3 to your icehrm folder 

Update icehrm v7.x to v8.0
--------------------------
Delete all folders except <icehrm>/app directory

Copy contents of icehrm_v8.0.zip to existing icehrm directory except app directory

Execute 'icehrmdb_os_update_v7.x_to_v8.0.sql' on your icehrm database

Update icehrm v7.1 to v7.2
--------------------------
Download update from https://bitbucket.org/thilina/icehrm-opensource/downloads/icehrm_update_from_7.1_to_7.2.zip

Unzip icehrm_update_from_7.1_to_7.2.zip inside your icehrm installation

Update icehrm v6.1 to v7.1
--------------------------
Delete all folders except <icehrm>/app directory

Copy contents of icehrm_v7.1.zip to existing icehrm directory except app directory

Execute 'icehrmdb_os_update_v6.1_to_v7.1.sql' on your icehrm database

Release note v8.2
-----------------
### Features
*Instance verification added

Release note v8.1
-----------------
### Fixes
*Fixed bug that caused a fatal error in php v5.4
*aws2.7.11 phar file replaced by a aws2.7.11 extracted files
*old aws sdk removed

Release note v8.0
-----------------
### Features
*Admin dashbord module
*If the employee joined in current leave period, his leave entitlement is calculated proportional to joined date
*Improvements to reporting module
*Adding new employee time tracking report
*Join date for employees made mandatory
*Sending welcome email when a user is added
*Let users directly reply to admin user from any email sent out from icehrm
*All the users who are not admins must have an employee object attached
*Upgrade aws sdk to v2.7.11
*Allow employees to change password
*Use only the email address defined under user for sending mails
*Making work_email and private_email fields optional


### Fixes
*Upload dialog close button issue fixed

Release note v7.2
-----------------
*Fixes
*Some critical vulnerabilities are fixed as recommend by http://zeroscience.mk/en/

Release note v7.1
-----------------
*Features
*Improved company structure graph
*Leave notes implementation � Supervisor can add a note when approving or rejecting leaves
*Filtering support
*Select boxes with long lists are now searchable
*Add/Edit/Delete company structure permissions added for managers
*Add ability to disable employee information editing

*Fixes
*Make loans editable only by admin
*Fix: permissions not getting applied to employee documents
*Fix error adding employee documents when no user assigned to the admin

*Code Quality
*Moving all module related code and data into module folders

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
* Fixes
* https://bitbucket.org/thilina/icehrm-opensource/issue/23/subordinate-leaves-pagination-not-working
* https://bitbucket.org/thilina/icehrm-opensource/issue/20/error-occured-while-time-punch


Release note v4.1
-----------------
* Features
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
