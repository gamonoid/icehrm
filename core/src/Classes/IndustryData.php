<?php

namespace Classes;

/**
 * A reusable catalogue of industries and representative data for each (job
 * titles today; more categories can be added per industry over time).
 *
 * This is the single source of truth for "generate sample data for an industry"
 * features across IceHRM — e.g. the Job Titles screen generates titles from a
 * selected industry, and other modules can generate their own data the same way.
 * Keep it framework-free (pure static data) so any layer can consume it.
 */
class IndustryData
{
    /** @var array<string, array{label:string, jobTitles:string[]}> */
    private static $industries = array(
        'software_it' => array(
            'label' => 'Software & IT',
            'jobTitles' => array(
                'Software Engineer', 'Senior Software Engineer', 'Lead Software Engineer',
                'Frontend Developer', 'Backend Developer', 'Full Stack Developer',
                'Mobile Application Developer', 'DevOps Engineer', 'Site Reliability Engineer',
                'QA Engineer', 'QA Automation Engineer', 'Data Engineer', 'Data Scientist',
                'Machine Learning Engineer', 'Solutions Architect', 'Engineering Manager',
                'Product Manager', 'Product Owner', 'Scrum Master', 'UI/UX Designer',
                'Database Administrator', 'Systems Administrator', 'Network Engineer',
                'Information Security Analyst', 'Technical Support Engineer',
            ),
        ),
        'healthcare' => array(
            'label' => 'Healthcare',
            'jobTitles' => array(
                'General Practitioner', 'Registered Nurse', 'Nurse Practitioner',
                'Medical Assistant', 'Surgeon', 'Anesthesiologist', 'Pediatrician',
                'Radiologist', 'Pharmacist', 'Pharmacy Technician', 'Physiotherapist',
                'Occupational Therapist', 'Laboratory Technician', 'Medical Records Clerk',
                'Healthcare Administrator', 'Billing Specialist', 'Phlebotomist',
                'Dietitian', 'Paramedic', 'Dental Hygienist',
            ),
        ),
        'finance_banking' => array(
            'label' => 'Finance & Banking',
            'jobTitles' => array(
                'Accountant', 'Senior Accountant', 'Financial Analyst', 'Investment Analyst',
                'Auditor', 'Internal Auditor', 'Bank Teller', 'Branch Manager',
                'Loan Officer', 'Credit Analyst', 'Financial Advisor', 'Risk Manager',
                'Compliance Officer', 'Portfolio Manager', 'Actuary', 'Bookkeeper',
                'Payroll Specialist', 'Treasury Analyst', 'Tax Consultant', 'Chief Financial Officer',
            ),
        ),
        'manufacturing' => array(
            'label' => 'Manufacturing',
            'jobTitles' => array(
                'Production Manager', 'Production Supervisor', 'Machine Operator',
                'Assembly Line Worker', 'Quality Control Inspector', 'Quality Assurance Manager',
                'Manufacturing Engineer', 'Industrial Engineer', 'Maintenance Technician',
                'Plant Manager', 'Process Engineer', 'Warehouse Supervisor', 'Materials Planner',
                'Health and Safety Officer', 'CNC Machinist', 'Welder', 'Tool and Die Maker',
                'Supply Chain Coordinator',
            ),
        ),
        'retail_ecommerce' => array(
            'label' => 'Retail & E-commerce',
            'jobTitles' => array(
                'Store Manager', 'Assistant Store Manager', 'Sales Associate', 'Cashier',
                'Visual Merchandiser', 'Inventory Manager', 'Buyer', 'Category Manager',
                'E-commerce Manager', 'Merchandising Manager', 'Customer Service Representative',
                'Loss Prevention Officer', 'Warehouse Associate', 'Fulfillment Specialist',
                'Retail Operations Manager', 'District Manager',
            ),
        ),
        'education' => array(
            'label' => 'Education',
            'jobTitles' => array(
                'Teacher', 'Senior Teacher', 'Teaching Assistant', 'Lecturer',
                'Assistant Professor', 'Associate Professor', 'Professor', 'Principal',
                'Vice Principal', 'Academic Coordinator', 'School Counselor', 'Librarian',
                'Registrar', 'Admissions Officer', 'Curriculum Developer', 'Special Education Teacher',
                'Education Administrator', 'Research Assistant',
            ),
        ),
        'construction' => array(
            'label' => 'Construction',
            'jobTitles' => array(
                'Civil Engineer', 'Structural Engineer', 'Site Engineer', 'Project Manager',
                'Construction Manager', 'Site Supervisor', 'Quantity Surveyor', 'Architect',
                'Draftsman', 'Foreman', 'Electrician', 'Plumber', 'Carpenter', 'Mason',
                'Heavy Equipment Operator', 'Safety Officer', 'Estimator', 'Surveyor',
            ),
        ),
        'hospitality' => array(
            'label' => 'Hospitality & Tourism',
            'jobTitles' => array(
                'Hotel Manager', 'Front Desk Agent', 'Front Office Manager', 'Concierge',
                'Housekeeping Supervisor', 'Executive Chef', 'Sous Chef', 'Line Cook',
                'Restaurant Manager', 'Waiter', 'Bartender', 'Event Coordinator',
                'Banquet Manager', 'Guest Relations Officer', 'Travel Consultant',
                'Tour Guide', 'Reservations Agent', 'Food and Beverage Manager',
            ),
        ),
        'logistics' => array(
            'label' => 'Logistics & Transportation',
            'jobTitles' => array(
                'Logistics Manager', 'Logistics Coordinator', 'Supply Chain Manager',
                'Warehouse Manager', 'Inventory Controller', 'Fleet Manager', 'Truck Driver',
                'Delivery Driver', 'Dispatcher', 'Freight Forwarder', 'Customs Broker',
                'Shipping Clerk', 'Procurement Officer', 'Operations Manager',
                'Distribution Coordinator', 'Import/Export Specialist',
            ),
        ),
        'marketing_advertising' => array(
            'label' => 'Marketing & Advertising',
            'jobTitles' => array(
                'Marketing Manager', 'Marketing Coordinator', 'Digital Marketing Specialist',
                'SEO Specialist', 'Content Writer', 'Copywriter', 'Social Media Manager',
                'Brand Manager', 'Graphic Designer', 'Art Director', 'Creative Director',
                'Public Relations Manager', 'Market Research Analyst', 'Media Planner',
                'Campaign Manager', 'Growth Marketer', 'Email Marketing Specialist',
            ),
        ),
        'legal' => array(
            'label' => 'Legal Services',
            'jobTitles' => array(
                'Attorney', 'Associate Attorney', 'Senior Partner', 'Paralegal',
                'Legal Assistant', 'Legal Secretary', 'Corporate Counsel', 'Compliance Officer',
                'Contract Manager', 'Litigation Attorney', 'Notary', 'Legal Researcher',
                'Court Clerk', 'Mediator',
            ),
        ),
        'real_estate' => array(
            'label' => 'Real Estate',
            'jobTitles' => array(
                'Real Estate Agent', 'Real Estate Broker', 'Property Manager', 'Leasing Consultant',
                'Real Estate Appraiser', 'Mortgage Broker', 'Facilities Manager',
                'Real Estate Analyst', 'Development Manager', 'Sales Manager',
                'Escrow Officer', 'Property Maintenance Supervisor',
            ),
        ),
        'telecommunications' => array(
            'label' => 'Telecommunications',
            'jobTitles' => array(
                'Telecommunications Engineer', 'Network Architect', 'RF Engineer',
                'Field Service Technician', 'Network Operations Engineer', 'Systems Engineer',
                'VoIP Engineer', 'Telecom Project Manager', 'Customer Support Specialist',
                'Sales Engineer', 'Billing Analyst', 'Infrastructure Engineer',
            ),
        ),
        'energy_utilities' => array(
            'label' => 'Energy & Utilities',
            'jobTitles' => array(
                'Electrical Engineer', 'Mechanical Engineer', 'Power Plant Operator',
                'Energy Analyst', 'Field Technician', 'Lineman', 'Renewable Energy Engineer',
                'Environmental Engineer', 'Operations Supervisor', 'Utilities Manager',
                'Meter Reader', 'Health and Safety Officer', 'Project Engineer',
            ),
        ),
        'media_entertainment' => array(
            'label' => 'Media & Entertainment',
            'jobTitles' => array(
                'Producer', 'Director', 'Video Editor', 'Cameraman', 'Sound Engineer',
                'Journalist', 'Editor', 'Content Producer', 'Motion Graphics Designer',
                'Animator', 'Broadcast Technician', 'Scriptwriter', 'Photographer',
                'Production Coordinator', 'Presenter',
            ),
        ),
        'human_resources' => array(
            'label' => 'Human Resources',
            'jobTitles' => array(
                'HR Manager', 'HR Generalist', 'HR Business Partner', 'Recruiter',
                'Talent Acquisition Specialist', 'HR Coordinator', 'Compensation and Benefits Analyst',
                'Learning and Development Manager', 'Training Specialist', 'Payroll Administrator',
                'Employee Relations Manager', 'HR Director', 'Onboarding Specialist',
            ),
        ),
    );

    /**
     * Representative skills per industry (a mix of domain and transferable
     * skills). Kept parallel to $industries so either can grow independently.
     * @var array<string, string[]>
     */
    private static $industrySkills = array(
        'software_it' => array(
            'Software Development', 'Cloud Computing', 'DevOps', 'Database Management',
            'Cybersecurity', 'Agile Methodologies', 'System Architecture', 'Data Analysis',
            'Quality Assurance', 'Technical Support', 'Problem Solving', 'Communication',
        ),
        'healthcare' => array(
            'Patient Care', 'Clinical Documentation', 'Medical Terminology', 'Infection Control',
            'Medication Administration', 'Electronic Health Records', 'Emergency Response',
            'Empathy', 'Attention to Detail', 'Communication',
        ),
        'finance_banking' => array(
            'Financial Analysis', 'Accounting', 'Risk Management', 'Financial Reporting',
            'Auditing', 'Budgeting', 'Regulatory Compliance', 'Investment Analysis',
            'Taxation', 'Analytical Thinking',
        ),
        'manufacturing' => array(
            'Production Planning', 'Quality Control', 'Lean Manufacturing', 'Six Sigma',
            'Machine Operation', 'Supply Chain Management', 'Occupational Safety',
            'Inventory Management', 'Process Improvement', 'Troubleshooting',
        ),
        'retail_ecommerce' => array(
            'Customer Service', 'Sales', 'Merchandising', 'Inventory Management',
            'Point of Sale Systems', 'E-commerce Platforms', 'Product Knowledge',
            'Visual Merchandising', 'Cash Handling', 'Communication',
        ),
        'education' => array(
            'Curriculum Development', 'Classroom Management', 'Lesson Planning',
            'Student Assessment', 'Instructional Design', 'Educational Technology',
            'Mentoring', 'Public Speaking', 'Patience', 'Communication',
        ),
        'construction' => array(
            'Blueprint Reading', 'Project Management', 'Site Safety', 'Cost Estimation',
            'Quantity Surveying', 'AutoCAD', 'Structural Analysis', 'Equipment Operation',
            'Quality Inspection', 'Team Coordination',
        ),
        'hospitality' => array(
            'Customer Service', 'Food and Beverage Service', 'Housekeeping', 'Event Planning',
            'Reservation Management', 'Guest Relations', 'Culinary Skills', 'Multitasking',
            'Attention to Detail', 'Communication',
        ),
        'logistics' => array(
            'Supply Chain Management', 'Inventory Control', 'Fleet Management', 'Route Planning',
            'Warehouse Operations', 'Logistics Coordination', 'Procurement', 'Freight Forwarding',
            'Problem Solving', 'Time Management',
        ),
        'marketing_advertising' => array(
            'Digital Marketing', 'Content Creation', 'Search Engine Optimization',
            'Social Media Management', 'Brand Management', 'Market Research', 'Copywriting',
            'Graphic Design', 'Data Analysis', 'Communication',
        ),
        'legal' => array(
            'Legal Research', 'Contract Drafting', 'Litigation', 'Case Management',
            'Regulatory Compliance', 'Legal Writing', 'Negotiation', 'Client Counseling',
            'Attention to Detail', 'Analytical Thinking',
        ),
        'real_estate' => array(
            'Property Management', 'Sales Negotiation', 'Market Analysis', 'Property Valuation',
            'Client Relationship Management', 'Lease Administration', 'Marketing',
            'Customer Service', 'Communication',
        ),
        'telecommunications' => array(
            'Network Engineering', 'Telecommunications Systems', 'RF Engineering',
            'Troubleshooting', 'Fiber Optics', 'VoIP', 'Customer Support', 'Project Management',
            'Technical Documentation',
        ),
        'energy_utilities' => array(
            'Electrical Systems', 'Renewable Energy', 'Power Generation', 'Preventive Maintenance',
            'Environmental Compliance', 'Safety Management', 'Troubleshooting', 'Project Engineering',
            'Data Analysis',
        ),
        'media_entertainment' => array(
            'Video Production', 'Video Editing', 'Content Creation', 'Scriptwriting',
            'Photography', 'Sound Engineering', 'Storytelling', 'Social Media', 'Creativity',
            'Communication',
        ),
        'human_resources' => array(
            'Recruitment', 'Employee Relations', 'Talent Management', 'Compensation and Benefits',
            'HR Policies', 'Payroll Administration', 'Performance Management',
            'Training and Development', 'Conflict Resolution', 'Communication',
        ),
    );

    /**
     * Current, professional certifications per industry (kept up to date — not a
     * legacy vendor list).
     * @var array<string, string[]>
     */
    private static $industryCertifications = array(
        'software_it' => array(
            'AWS Certified Solutions Architect – Associate',
            'Microsoft Certified: Azure Administrator Associate',
            'Google Cloud Professional Cloud Architect',
            'Certified Kubernetes Administrator (CKA)',
            'Certified Information Systems Security Professional (CISSP)',
            'CompTIA Security+', 'Certified ScrumMaster (CSM)',
            'Cisco Certified Network Associate (CCNA)', 'Certified Ethical Hacker (CEH)',
            'HashiCorp Certified: Terraform Associate',
        ),
        'healthcare' => array(
            'Basic Life Support (BLS)', 'Advanced Cardiovascular Life Support (ACLS)',
            'Certified Nursing Assistant (CNA)', 'Certified Medical Assistant (CMA)',
            'Certified Professional Coder (CPC)', 'Pediatric Advanced Life Support (PALS)',
            'Certified Phlebotomy Technician (CPT)',
            'Certified Healthcare Financial Professional (CHFP)',
        ),
        'finance_banking' => array(
            'Certified Public Accountant (CPA)', 'Chartered Financial Analyst (CFA)',
            'Certified Management Accountant (CMA)', 'Financial Risk Manager (FRM)',
            'Certified Financial Planner (CFP)', 'Certified Internal Auditor (CIA)',
            'Certified Anti-Money Laundering Specialist (CAMS)',
            'Certified Treasury Professional (CTP)',
        ),
        'manufacturing' => array(
            'Six Sigma Green Belt', 'Six Sigma Black Belt',
            'Certified in Planning and Inventory Management (CPIM)',
            'Certified Quality Engineer (CQE)', 'ISO 9001 Lead Auditor',
            'OSHA 30-Hour General Industry',
            'Certified Maintenance and Reliability Professional (CMRP)',
            'Certified Supply Chain Professional (CSCP)',
        ),
        'retail_ecommerce' => array(
            'Google Analytics Certification', 'Google Ads Certification',
            'HubSpot Inbound Marketing Certification',
            'Certified Customer Experience Professional (CCXP)',
            'Certified Professional in Supply Management (CPSM)',
            'Meta Certified Digital Marketing Associate',
        ),
        'education' => array(
            'Teaching License / Certification', 'TESOL / TEFL Certification',
            'National Board Certification', 'Google Certified Educator Level 1',
            'Certified Instructional Designer', 'Child Development Associate (CDA)',
        ),
        'construction' => array(
            'Project Management Professional (PMP)', 'OSHA 30-Hour Construction',
            'LEED Accredited Professional', 'Certified Construction Manager (CCM)',
            'NEBOSH International General Certificate', 'Certified Safety Professional (CSP)',
            'Professional Engineer (PE) License',
        ),
        'hospitality' => array(
            'ServSafe Food Handler Certification', 'Certified Hotel Administrator (CHA)',
            'Certified Hospitality Supervisor (CHS)', 'Certified Meeting Professional (CMP)',
            'Certified Guest Service Professional (CGSP)', 'HACCP Food Safety Certification',
        ),
        'logistics' => array(
            'Certified Supply Chain Professional (CSCP)',
            'Certified in Logistics, Transportation and Distribution (CLTD)',
            'Certified in Planning and Inventory Management (CPIM)',
            'Certified Professional in Supply Management (CPSM)', 'Six Sigma Green Belt',
            'Project Management Professional (PMP)',
        ),
        'marketing_advertising' => array(
            'Google Ads Certification', 'Google Analytics Certification (GA4)',
            'HubSpot Content Marketing Certification',
            'Meta Certified Digital Marketing Associate',
            'Hootsuite Social Marketing Certification',
            'Google Digital Marketing and E-commerce Certificate',
        ),
        'legal' => array(
            'Certified Paralegal (CP)', 'Certified Legal Manager (CLM)',
            'Certified E-Discovery Specialist (CEDS)',
            'Certified Information Privacy Professional (CIPP)', 'Registered Paralegal (RP)',
            'Contract and Commercial Management Certification',
        ),
        'real_estate' => array(
            'Certified Residential Specialist (CRS)', "Accredited Buyer's Representative (ABR)",
            'Certified Commercial Investment Member (CCIM)', 'Certified Property Manager (CPM)',
            'Real Estate Broker License', 'Seniors Real Estate Specialist (SRES)',
        ),
        'telecommunications' => array(
            'Cisco Certified Network Associate (CCNA)',
            'Cisco Certified Network Professional (CCNP)', 'CompTIA Network+',
            'Certified Fiber Optic Technician (CFOT)',
            'Juniper Networks Certified Associate (JNCIA)',
            'Project Management Professional (PMP)',
        ),
        'energy_utilities' => array(
            'Certified Energy Manager (CEM)', 'NABCEP Solar Installation Professional',
            'Professional Engineer (PE) License', 'Certified Energy Auditor (CEA)',
            'LEED Accredited Professional', 'OSHA 30-Hour General Industry',
        ),
        'media_entertainment' => array(
            'Adobe Certified Professional', 'Avid Certified User',
            'Certified Broadcast Technologist (CBT)', 'Google Ads Video Certification',
            'Apple Certified Pro - Final Cut Pro',
        ),
        'human_resources' => array(
            'Professional in Human Resources (PHR)',
            'Senior Professional in Human Resources (SPHR)',
            'SHRM Certified Professional (SHRM-CP)',
            'SHRM Senior Certified Professional (SHRM-SCP)',
            'Certified Compensation Professional (CCP)',
            'Associate Professional in Talent Development (APTD)',
            'Certified Employee Benefits Specialist (CEBS)',
        ),
    );

    /** The full catalogue: key => { label, jobTitles }. */
    public static function getAll()
    {
        return self::$industries;
    }

    /** Just the industry keys + labels, for building dropdowns. */
    public static function getIndustries()
    {
        $out = array();
        foreach (self::$industries as $key => $industry) {
            $out[] = array('key' => $key, 'label' => $industry['label']);
        }
        return $out;
    }

    public static function hasIndustry($key)
    {
        return isset(self::$industries[$key]);
    }

    /** A single industry entry, or null. */
    public static function getIndustry($key)
    {
        return isset(self::$industries[$key]) ? self::$industries[$key] : null;
    }

    /** The representative job titles for an industry (empty array if unknown). */
    public static function getJobTitles($key)
    {
        return isset(self::$industries[$key]['jobTitles'])
            ? self::$industries[$key]['jobTitles']
            : array();
    }

    /** The representative skills for an industry (empty array if unknown). */
    public static function getSkills($key)
    {
        return isset(self::$industrySkills[$key]) ? self::$industrySkills[$key] : array();
    }

    /** Current professional certifications for an industry (empty if unknown). */
    public static function getCertifications($key)
    {
        return isset(self::$industryCertifications[$key])
            ? self::$industryCertifications[$key]
            : array();
    }
}
