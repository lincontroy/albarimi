<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JobsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all categories
        $categories = JobCategory::all();
        
        if ($categories->isEmpty()) {
            $this->command->error('⚠️ No job categories found! Please run JobCategorySeeder first.');
            return;
        }

        $jobs = [
            // IT Jobs
            [
                'title' => 'Senior Laravel Developer',
                'description' => 'We are looking for an experienced Laravel developer to join our team. You will be responsible for developing and maintaining web applications using Laravel framework.',
                'requirements' => '- Minimum 3 years experience with Laravel
- Strong knowledge of PHP, MySQL, and JavaScript
- Experience with RESTful APIs
- Familiarity with Vue.js or React is a plus
- Good understanding of MVC design patterns',
                'benefits' => '- Competitive salary
- Health insurance
- Flexible working hours
- Remote work options
- Professional development allowance',
                'salary' => 80000,
                'salary_type' => 'yearly',
                'location' => 'New York, NY',
                'job_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 2,
                'is_remote' => true,
                'is_featured' => true,
                'is_active' => true,
                'view_count' => 150,
                'application_count' => 25,
                'category_id' => $categories->where('name', 'Information Technology')->first()->id,
            ],
            [
                'title' => 'Frontend Developer (React)',
                'description' => 'Join our frontend team to build beautiful, responsive web applications using React.js and modern frontend technologies.',
                'requirements' => '- 2+ years experience with React.js
- Proficient in HTML5, CSS3, JavaScript ES6+
- Experience with Redux or Context API
- Knowledge of responsive design principles
- Familiarity with Git version control',
                'benefits' => '- Stock options
- 4 weeks paid vacation
- Gym membership
- Learning budget
- Team building events',
                'salary' => 75000,
                'salary_type' => 'yearly',
                'location' => 'San Francisco, CA',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 3,
                'is_remote' => true,
                'is_featured' => true,
                'is_active' => true,
                'view_count' => 200,
                'application_count' => 45,
                'category_id' => $categories->where('name', 'Information Technology')->first()->id,
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'We need a DevOps Engineer to help us build and maintain our cloud infrastructure and CI/CD pipelines.',
                'requirements' => '- Experience with AWS/Azure/GCP
- Knowledge of Docker and Kubernetes
- Experience with CI/CD tools (Jenkins, GitLab CI)
- Scripting skills (Bash, Python)
- Understanding of networking and security',
                'benefits' => '- Competitive compensation
- 100% remote work
- Equipment allowance
- Conference budget
- Flexible PTO',
                'salary' => 95000,
                'salary_type' => 'yearly',
                'location' => 'Remote',
                'job_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 1,
                'is_remote' => true,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 180,
                'application_count' => 30,
                'category_id' => $categories->where('name', 'Information Technology')->first()->id,
            ],

            // Marketing Jobs
            [
                'title' => 'Digital Marketing Manager',
                'description' => 'Lead our digital marketing efforts including SEO, SEM, social media, and content marketing strategies.',
                'requirements' => '- 5+ years digital marketing experience
- Proven track record in SEO and SEM
- Experience with Google Analytics and AdWords
- Strong analytical skills
- Excellent communication abilities',
                'benefits' => '- Performance bonuses
- Marketing budget
- Creative freedom
- Company car
- International travel opportunities',
                'salary' => 70000,
                'salary_type' => 'yearly',
                'location' => 'Chicago, IL',
                'job_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 1,
                'is_remote' => false,
                'is_featured' => true,
                'is_active' => true,
                'view_count' => 120,
                'application_count' => 35,
                'category_id' => $categories->where('name', 'Marketing & Sales')->first()->id,
            ],

            // Finance Jobs
            [
                'title' => 'Financial Analyst',
                'description' => 'Analyze financial data, prepare reports, and provide insights to support business decisions.',
                'requirements' => '- Bachelor\'s in Finance or related field
- 2+ years financial analysis experience
- Proficiency in Excel and financial modeling
- Strong analytical and quantitative skills
- Attention to detail',
                'benefits' => '- Annual bonus
- Retirement plan matching
- Professional certification support
- Health and wellness program
- Tuition reimbursement',
                'salary' => 65000,
                'salary_type' => 'yearly',
                'location' => 'Boston, MA',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 2,
                'is_remote' => false,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 90,
                'application_count' => 20,
                'category_id' => $categories->where('name', 'Finance & Accounting')->first()->id,
            ],

            // HR Jobs
            [
                'title' => 'HR Business Partner',
                'description' => 'Partner with business leaders to develop and implement HR strategies that support organizational goals.',
                'requirements' => '- 4+ years HR experience
- Knowledge of employment laws and regulations
- Strong interpersonal and communication skills
- Experience with HRIS systems
- Problem-solving abilities',
                'benefits' => '- Comprehensive benefits package
- Work-life balance
- Professional development
- Employee recognition programs
- Hybrid work model',
                'salary' => 75000,
                'salary_type' => 'yearly',
                'location' => 'Austin, TX',
                'job_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 1,
                'is_remote' => false,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 85,
                'application_count' => 18,
                'category_id' => $categories->where('name', 'Human Resources')->first()->id,
            ],

            // Engineering Jobs
            [
                'title' => 'Civil Engineer',
                'description' => 'Design and oversee construction projects, ensuring they meet safety and environmental standards.',
                'requirements' => '- Bachelor\'s in Civil Engineering
- Professional Engineer (PE) license preferred
- 3+ years construction experience
- Proficiency with AutoCAD
- Strong project management skills',
                'benefits' => '- Company vehicle
- Field work allowances
- Continuing education
- Safety bonuses
- Relocation assistance',
                'salary' => 85000,
                'salary_type' => 'yearly',
                'location' => 'Denver, CO',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 1,
                'is_remote' => false,
                'is_featured' => true,
                'is_active' => true,
                'view_count' => 110,
                'application_count' => 22,
                'category_id' => $categories->where('name', 'Engineering')->first()->id,
            ],

            // Healthcare Jobs
            [
                'title' => 'Registered Nurse',
                'description' => 'Provide high-quality patient care in a fast-paced hospital environment.',
                'requirements' => '- Valid RN license
- BLS/ACLS certification
- 2+ years nursing experience
- Strong clinical skills
- Compassionate patient care',
                'benefits' => '- Sign-on bonus
- Shift differentials
- Tuition reimbursement
- Health insurance
- Retirement plan',
                'salary' => 68000,
                'salary_type' => 'yearly',
                'location' => 'Miami, FL',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'Associate\'s Degree',
                'vacancies' => 5,
                'is_remote' => false,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 300,
                'application_count' => 60,
                'category_id' => $categories->where('name', 'Healthcare')->first()->id,
            ],

            // Education Jobs
            [
                'title' => 'High School Math Teacher',
                'description' => 'Teach mathematics to high school students and contribute to curriculum development.',
                'requirements' => '- State teaching certification
- Bachelor\'s in Mathematics or Education
- 2+ years teaching experience
- Strong communication skills
- Patience and creativity',
                'benefits' => '- Summers off
- Professional development days
- Classroom budget
- Pension plan
- Health benefits',
                'salary' => 55000,
                'salary_type' => 'yearly',
                'location' => 'Seattle, WA',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 2,
                'is_remote' => false,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 95,
                'application_count' => 15,
                'category_id' => $categories->where('name', 'Education')->first()->id,
            ],

            // Part-time and Contract Jobs
            [
                'title' => 'Part-time Data Entry Clerk',
                'description' => 'Entry-level data entry position with flexible hours. Perfect for students or those seeking part-time work.',
                'requirements' => '- Fast and accurate typing skills
- Attention to detail
- Basic computer skills
- High school diploma
- Reliable and punctual',
                'benefits' => '- Flexible schedule
- Work from home options
- Weekly pay
- No experience required
- Training provided',
                'salary' => 18,
                'salary_type' => 'hourly',
                'location' => 'Remote',
                'job_type' => 'part_time',
                'experience_level' => 'entry',
                'education_level' => 'High School',
                'vacancies' => 3,
                'is_remote' => true,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 250,
                'application_count' => 80,
                'category_id' => $categories->where('name', 'Administration')->first()->id,
            ],

            [
                'title' => 'Freelance Graphic Designer',
                'description' => 'Create compelling visual designs for various marketing materials and digital platforms.',
                'requirements' => '- Portfolio of previous work
- Proficiency in Adobe Creative Suite
- Strong understanding of design principles
- Ability to meet deadlines
- Good communication skills',
                'benefits' => '- Flexible work hours
- Creative projects
- Remote work
- Competitive rates
- Diverse client base',
                'salary' => 45,
                'salary_type' => 'hourly',
                'location' => 'Remote',
                'job_type' => 'freelance',
                'experience_level' => 'mid',
                'education_level' => 'Bachelor\'s Degree',
                'vacancies' => 1,
                'is_remote' => true,
                'is_featured' => false,
                'is_active' => true,
                'view_count' => 180,
                'application_count' => 40,
                'category_id' => $categories->where('name', 'Creative & Design')->first()->id,
            ],
        ];

        // Add application deadline (30-90 days from now)
        foreach ($jobs as &$jobData) {
            $jobData['slug'] = Str::slug($jobData['title']);
            $jobData['application_deadline'] = Carbon::now()->addDays(rand(30, 90));
            $jobData['published_at'] = Carbon::now()->subDays(rand(0, 30));
        }

        // Create jobs
        foreach ($jobs as $job) {
            Job::create($job);
        }

        $this->command->info('✅ Jobs seeded successfully!');
        $this->command->info('📊 Total jobs created: ' . count($jobs));
    }
}