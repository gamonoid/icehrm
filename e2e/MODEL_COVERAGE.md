# Model coverage map

Every model class registered with `BaseService` (149), the module that surfaces it
in the SPA, and the tab it appears on. Built by cross-referencing the running
app's model registry with `NativeModuleRegistry`, the module JS adapters and each
extension's `meta.json`, so it reflects what the UI actually mounts.

Tab entities are resolved through the registry's `entities` map, so a UI alias
(`MyLoan`) is reported as the model it binds to (`EmployeeCompanyLoan`).

The `tests/model-*.spec.js` suites are generated from this map — one spec per
module, one named test per model — so a model cannot be silently dropped.

| Module | Models | Tabs → model |
|---|---|---|
| `admin::attendance` | `Attendance`, `AttendanceStatus` | Attendance → Attendance<br>Attendance Status → AttendanceStatus |
| `admin::audit` | `Audit`, `EmailLogEntry` | Audit Log → Audit<br>Email Log → EmailLogEntry |
| `admin::custom_fields` | `CustomField` | Custom Fields → CustomField |
| `admin::documents` | `CompanyDocument`, `Document`, `EmployeeDocument`, `PayslipDocument` | Company Documents → CompanyDocument<br>Document Types → Document<br>Employee Documents → EmployeeDocument<br>Employee Payslip → PayslipDocument |
| `admin::employeehistory` | `EmployeeDataHistory` | Employee Basic Details → EmployeeDataHistory |
| `admin::employees` | `ArchivedEmployee`, `EmergencyContact`, `Employee`, `EmployeeCareer`, `EmployeeCertification`, `EmployeeDependent`, `EmployeeEducation`, `EmployeeLanguage`, `EmployeeSkill` | Work History → EmployeeCareer<br>Skills → EmployeeSkill<br>Education → EmployeeEducation<br>Certifications → EmployeeCertification<br>Languages → EmployeeLanguage<br>Dependents → EmployeeDependent<br>Contacts → EmergencyContact<br>Resigned → TerminatedEmployee<br>Archived → ArchivedEmployee |
| `admin::fieldnames` | `FieldNameMapping` | Field Names → FieldNameMapping |
| `admin::jobs` | `EmploymentStatus`, `JobTitle`, `PayGrade` | Job Titles → JobTitle<br>Pay Grades → PayGrade<br>Employment Status → EmploymentStatus |
| `admin::leaves` | `EmployeeLeave`, `HoliDay`, `LeaveGroup`, `LeaveGroupEmployee`, `LeavePeriod`, `LeaveRule`, `LeaveStartingBalance`, `LeaveType`, `WorkDay` | Leave Types → LeaveType<br>Leave Period → LeavePeriod<br>Work Week → WorkDay<br>Holidays → HoliDay<br>Leave Rules → LeaveRule<br>Leave Adjustments → LeaveStartingBalance<br>Leave Groups → LeaveGroup<br>Employee Leave List → EmployeeLeave |
| `admin::loans` | `CompanyLoan`, `EmployeeCompanyLoan` | Loan Types → CompanyLoan<br>Employee Loans → EmployeeCompanyLoan |
| `admin::metadata` | `Country`, `CurrencyType`, `Ethnicity`, `ImmigrationStatus`, `Nationality`, `Province` | Countries → Country<br>Provinces → Province<br>Currency Types → CurrencyType<br>Nationality → Nationality<br>Ethnicity → Ethnicity<br>Immigration Status → ImmigrationStatus |
| `admin::modules` | `Module` | Modules → Module |
| `admin::overtime` | `EmployeeOvertime`, `OvertimeCategory` | Overtime Categories → OvertimeCategory<br>Overtime Requests → EmployeeOvertime |
| `admin::performance` | `EmployeeGoal`, `PerformanceReview`, `ReviewFeedback`, `ReviewTemplate` | Performance Reviews → PerformanceReview<br>Feedback Requests → ReviewFeedback<br>Employee Feedback Templates → ReviewTemplate<br>Employee Goals → EmployeeGoal |
| `admin::permissions` | `Permission` | Permissions → Permission |
| `admin::projects` | `Client`, `EmployeeProject`, `Project` | Projects → Project<br>Employee Project Assignments → EmployeeProject<br>Clients → Client |
| `admin::qualifications` | `Certification`, `Education`, `Language`, `Skill` | Skills → Skill<br>Education → Education<br>Certifications → Certification<br>Languages → Language |
| `admin::salary` | `EmployeeSalary`, `SalaryComponent`, `SalaryComponentType` | Salary Component Types → SalaryComponentType<br>Salary Components → SalaryComponent<br>Salary → EmployeeSalary |
| `admin::training` | `Course`, `EmployeeTrainingSession`, `TrainingSession` | Courses → Course<br>Training Sessions → TrainingSession<br>Employee Training Sessions → EmployeeTrainingSession |
| `admin::travel` | `EmployeeTravelRecord`, `TravelProject` | Travel Projects → TravelProject<br>Travel Requests → EmployeeTravelRecord |
| `admin::users` | `User`, `UserInvitation`, `UserRole` | Users → User<br>User Roles → UserRole<br>User Invitations → UserInvitation |
| `extension::candidates|admin` | `Application`, `Call`, `Candidate`, `CandidateForHiringStage`, `CandidateNote`, `HiringPipeline`, `Interview` | _custom React view — no registry tabs_ |
| `extension::company_assets|admin` | `AssetType`, `CompanyAsset` | _custom React view — no registry tabs_ |
| `extension::demo-mode|admin` | `DemoDataEntry` | _custom React view — no registry tabs_ |
| `extension::directory|user` | `StaffDirectory` | _custom React view — no registry tabs_ |
| `extension::editor|user` | `Content` | _custom React view — no registry tabs_ |
| `extension::esign|admin` | `EsignAuditLog`, `EsignDocument`, `EsignedDoc` | _custom React view — no registry tabs_ |
| `extension::expenses|admin` | `EmployeeExpense`, `EmployeeExpenseApproval`, `ExpensesCategory`, `ExpensesPaymentMethod` | _custom React view — no registry tabs_ |
| `extension::jobpositions|admin` | `Job` | _custom React view — no registry tabs_ |
| `extension::jobsetup|admin` | `Benifit`, `EducationLevel`, `EmployementType`, `ExperienceLevel`, `Industry`, `JobFunction` | _custom React view — no registry tabs_ |
| `extension::learn|admin` | `LmsCourse`, `LmsEmployeeCourse`, `LmsEmployeeLesson`, `LmsLesson` | _custom React view — no registry tabs_ |
| `extension::tasks|admin` | `TaskList`, `TaskListAssignment` | _custom React view — no registry tabs_ |
| `extension::tasks|user` | `MyTaskList` | _custom React view — no registry tabs_ |
| `extension::team|admin` | `Team`, `TeamMember` | _custom React view — no registry tabs_ |
| `modules::attendance` | `Attendance` | Attendance → MyAttendance |
| `modules::dependents` | `EmployeeDependent` | Dependents → EmployeeDependent |
| `modules::documents` | `CompanyDocument`, `EmployeeDocument`, `PayslipDocument` | My Documents → MyDocument<br>Company Documents → MyCompanyDocument<br>Payslips → MyPayslip |
| `modules::emergency_contact` | `EmergencyContact` | Emergency Contacts → EmergencyContact |
| `modules::leaves` | `EmployeeLeave`, `EmployeeLeaveApprove` | Leave Entitlement → EmployeeLeaveEntitlement<br>All My Leaves → EmployeeLeave<br>Approved Leave → EmployeeLeave<br>Pending Leave → EmployeeLeave<br>Leave Requests → EmployeeLeave<br>Cancellation Requests → EmployeeLeave<br>Approval Requests → EmployeeLeaveApprove |
| `modules::loans` | `EmployeeCompanyLoan` | Loans Taken → EmployeeCompanyLoan |
| `modules::overtime` | `EmployeeOvertime`, `EmployeeOvertimeApproval` | Overtime Requests → MyOvertime<br>Direct Reports → EmployeeOvertime<br>Approvals → EmployeeOvertimeApproval |
| `modules::performance` | `CoordinatedPerformanceReview`, `PerformanceReview`, `ReviewFeedback` | Self Assessments → MyPerformanceReview<br>Feedback Requests → MyReviewFeedback |
| `modules::qualifications` | `EmployeeCertification`, `EmployeeEducation`, `EmployeeLanguage`, `EmployeeSkill` | Skills → EmployeeSkill<br>Education → EmployeeEducation<br>Certifications → EmployeeCertification<br>Languages → EmployeeLanguage |
| `modules::time_sheets` | `EmployeeTimeEntry`, `EmployeeTimeSheet` | _custom React view — no registry tabs_ |
| `modules::training` | `CoordinatedTrainingSession`, `EmployeeTrainingSession`, `TrainingSessionWithCourse` | All Training Sessions → TrainingSessionWithCourse<br>My Training Sessions → EmployeeTrainingSession<br>Training Sessions of Direct Reports → SubEmployeeTraining<br>Training Sessions Coordinated by Me → CoordinatedTrainingSession |
| `modules::travel` | `EmployeeTravelRecord` | Travel Requests → MyTravel<br>Travel Requests (Direct Reports) → SubTravel<br>Travel Requests for Approval → TravelApproval |

## Models with no list view (41)

These have no CRUD list of their own. Triaged by whether anything outside the
model's own class file references them, i.e. whether some code path could drive
them through `BaseService::getData` / `addElement` / `deleteElement` /
`getElement`.

### Not worth covering (11) — pure infrastructure

Nothing references these outside their own class. They are written by migrations,
cron, the mailer or the framework itself, never by a user action, so there is no
UI path an e2e test could exercise.

- `Backup` (table `Backups`)
- `Cron` (table `Crons`)
- `DataEntryBackup` (table `DataEntryBackups`)
- `IceEmail` (table `Emails`)
- `Migration` (table `Migrations`)
- `RestAccessToken` (table `RestAccessTokens`)
- `SecureResource` (table `SecureResources`)
- `StatusChangeLog` (table `StatusChangeLogs`)
- `SystemData` (table `SystemData`)
- `UserMeta` (table `UserMeta`)
- `UserReport` (table `UserReports`)

### Reachable — need an action-level spec (30)

Each is written or read by some user-visible action rather than by a list of its
own: applying a leave writes `EmployeeLeaveDay`, saving a custom field writes
`CustomFieldValue`, running payroll writes the `Payroll*` set. The spec for each
is therefore "perform the action, assert the record", not "open the list".

| Model | Table | Referenced by (evidence) |
|---|---|---|
| `CalculationHook` | `CalculationHooks` | `core/src/Metadata/Admin/Api/MetadataAdminManager.php` |
| `CompanyStructure` | `CompanyStructures` | `web/admin/src/attendance/lib.js`, `web/admin/src/company_structure/lib.js` |
| `CustomFieldValue` | `CustomFieldValues` | `core/src/Metadata/Admin/Api/MetadataAdminManager.php` |
| `Deduction` | `Deductions` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `core/src/Payroll/Admin/Api/PayrollActionManager.php` |
| `DeductionGroup` | `DeductionGroup` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `core/src/Payroll/Admin/Api/PayrollActionManager.php` |
| `EmployeeAccess` | `None` | `core/src/Employees/Admin/Api/EmployeesAdminManager.php` |
| `EmployeeApproval` | `EmployeeApprovals` | `core/src/Employees/Admin/Api/EmployeesAdminManager.php` |
| `EmployeeDocumentNotification` | `EmployeeDocumentNotifications` | `core/src/Documents/Admin/Api/DocumentsAdminManager.php` |
| `EmployeeImmigration` | `EmployeeImmigrations` | `core/src/Travel/Admin/Api/TravelAdminManager.php` |
| `EmployeeLeaveDay` | `EmployeeLeaveDays` | `extensions-pro/leave_and_performance/core/src/Leaves/User/Api/LeavesModulesManager.php`, `extensions-pro/leave_and_performance/main.php` |
| `EmployeeLeaveLog` | `EmployeeLeaveLog` | `extensions/demo-mode/admin/src/DemoModeTracker.php`, `extensions-pro/leave_and_performance/core/src/Leaves/User/Api/LeavesModulesManager.php` |
| `EmployeeStatus` | `EmployeeStatus` | `web/modules/src/dashboard/lib.js`, `core/src/Employees/Admin/Api/EmployeesAdminManager.php` |
| `EmployeeTravelRecordApproval` | `EmployeeTravelRecords` | `core/src/Travel/Admin/Api/TravelAdminManager.php` |
| `File` | `Files` | `web/bower_components/tinymce/themes/modern/theme.js`, `core/lib/composer/vendor/monolog/monolog/src/Monolog/Formatter/WildfireFormatter.php` |
| `ImmigrationDocument` | `ImmigrationDocuments` | `web/admin/src/travel/lib.js`, `web/modules/src/travel/lib.js` |
| `Notification` | `Notifications` | `web/shell/src/Notifications.jsx` |
| `PayFrequency` | `PayFrequency` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `extensions-pro/payroll_config/admin/src/Extension.php` |
| `Payroll` | `Payroll` | `core/migrations/v20171003_200302_payroll_meta_export.php`, `core/src/Model/Report.php` |
| `PayrollCalculations` | `None` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `extensions-pro/payroll_config/admin/src/Extension.php` |
| `PayrollColumn` | `PayrollColumns` | `core/src/Classes/Cron/Task/PayrollProcessTask.php`, `core/src/Payroll/Admin/Api/PayrollAdminManager.php` |
| `PayrollColumnTemplate` | `PayrollColumnTemplates` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `extensions-pro/payroll_config/admin/src/Extension.php` |
| `PayrollData` | `PayrollData` | `core/src/Classes/Cron/Task/PayrollProcessTask.php`, `core/src/Payroll/Admin/Api/PayrollAdminManager.php` |
| `PayrollEmployee` | `PayrollEmployees` | `core/src/Classes/Cron/Task/PayrollProcessTask.php`, `core/src/Salary/Admin/Api/SalaryAdminManager.php` |
| `PayslipTemplate` | `PayslipTemplates` | `core/src/Payroll/Admin/Api/PayrollAdminManager.php`, `extensions-pro/payroll_config/admin/src/Extension.php` |
| `QTDays` | `None` | `web/shell/src/TimeSheets.jsx`, `web/modules/src/time_sheets/index.js` |
| `Report` | `Reports` | `web/admin/src/employees/lib.js`, `core/lib/composer/vendor/phpunit/php-code-coverage/tests/tests/HTMLTest.php` |
| `ReportFile` | `ReportFiles` | `core/src/ReportFiles/Admin/Api/ReportFilesAdminManager.php`, `core/src/ReportFiles/User/Api/ReportFilesModulesManager.php` |
| `Setting` | `Settings` | `web/admin/src/settings/index.js`, `core/admin/settings/index.php` |
| `SupportedLanguage` | `SupportedLanguages` | `web/admin/src/settings/lib.js`, `web/admin/src/settings/lib-react.js` |
| `Timezone` | `Timezones` | `web/admin/src/company_structure/lib.js`, `web/admin/src/users/lib.js` |
