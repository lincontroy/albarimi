<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Information Technology',
                'description' => 'Software development, IT support, network administration, and cybersecurity roles.',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing & Sales',
                'description' => 'Digital marketing, sales, business development, and customer relationship management positions.',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Finance & Accounting',
                'description' => 'Financial analysis, accounting, auditing, and investment banking careers.',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'HR management, recruitment, talent acquisition, and employee relations roles.',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Engineering',
                'description' => 'Mechanical, electrical, civil, and software engineering positions.',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Healthcare',
                'description' => 'Medical, nursing, pharmaceutical, and healthcare administration jobs.',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Education',
                'description' => 'Teaching, academic administration, and educational consulting roles.',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Logistics & Supply Chain',
                'description' => 'Warehouse management, transportation, procurement, and supply chain coordination.',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Customer Service',
                'description' => 'Call center, customer support, and client service positions.',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Administration',
                'description' => 'Office administration, executive assistance, and clerical roles.',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Creative & Design',
                'description' => 'Graphic design, content creation, multimedia, and creative direction.',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Research & Development',
                'description' => 'Scientific research, product development, and innovation roles.',
                'sort_order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            JobCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'sort_order' => $category['sort_order'],
                'is_active' => $category['is_active'],
            ]);
        }

        $this->command->info('✅ Job categories seeded successfully!');
    }
}