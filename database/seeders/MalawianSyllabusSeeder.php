<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class MalawianSyllabusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Malawian Secondary School Subjects for Forms 1-4
        $subjects = [
            // Core Subjects
            ['name' => 'Mathematics', 'code' => 'MATH', 'description' => 'Mathematics for secondary school'],
            ['name' => 'English', 'code' => 'ENG', 'description' => 'English Language and Literature'],
            ['name' => 'Chichewa', 'code' => 'CHI', 'description' => 'Chichewa Language'],
            ['name' => 'Life Skills', 'code' => 'LIFE', 'description' => 'Life Skills Education'],

            // Sciences
            ['name' => 'Physical Science', 'code' => 'PHYS', 'description' => 'Physics and Chemistry combined'],
            ['name' => 'Biology', 'code' => 'BIO', 'description' => 'Biology and Life Sciences'],
            ['name' => 'Agriculture', 'code' => 'AGRI', 'description' => 'Agricultural Science'],

            // Social Sciences
            ['name' => 'Geography', 'code' => 'GEO', 'description' => 'Physical and Human Geography'],
            ['name' => 'History', 'code' => 'HIST', 'description' => 'Malawian and World History'],
            ['name' => 'Social Studies', 'code' => 'SOC', 'description' => 'Social and Development Studies'],
            ['name' => 'Bible Knowledge', 'code' => 'BK', 'description' => 'Religious and Moral Education'],

            // Technical/Practical
            ['name' => 'Computer Studies', 'code' => 'COMP', 'description' => 'Computer Science and ICT'],
            ['name' => 'Business Studies', 'code' => 'BUS', 'description' => 'Business and Commerce'],
            ['name' => 'Accounting', 'code' => 'ACC', 'description' => 'Principles of Accounting'],
            ['name' => 'Technical Drawing', 'code' => 'TD', 'description' => 'Technical Drawing and Design'],
            ['name' => 'Home Economics', 'code' => 'HE', 'description' => 'Home Economics and Nutrition'],
        ];

        // Create subjects for each form (1-4)
        foreach (['1', '2', '3', '4'] as $form) {
            foreach ($subjects as $index => $subject) {
                Subject::create([
                    'name' => $subject['name'],
                    'code' => $subject['code'] . '_F' . $form,
                    'form' => $form,
                    'description' => $subject['description'] . ' - Form ' . $form,
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }
        }

        // Add sample topics for Mathematics Form 1
        $mathForm1 = Subject::where('code', 'MATH_F1')->first();
        if ($mathForm1) {
            $topics = [
                ['name' => 'Number Systems', 'term' => 1, 'week' => 1, 'description' => 'Introduction to number systems'],
                ['name' => 'Whole Numbers', 'term' => 1, 'week' => 2, 'description' => 'Operations with whole numbers'],
                ['name' => 'Fractions', 'term' => 1, 'week' => 3, 'description' => 'Understanding and working with fractions'],
                ['name' => 'Decimals', 'term' => 1, 'week' => 4, 'description' => 'Decimal numbers and operations'],
                ['name' => 'Percentages', 'term' => 1, 'week' => 5, 'description' => 'Working with percentages'],
                ['name' => 'Ratios and Proportions', 'term' => 1, 'week' => 6, 'description' => 'Understanding ratios'],
                ['name' => 'Basic Algebra', 'term' => 2, 'week' => 1, 'description' => 'Introduction to algebraic expressions'],
                ['name' => 'Linear Equations', 'term' => 2, 'week' => 2, 'description' => 'Solving linear equations'],
                ['name' => 'Geometry Basics', 'term' => 2, 'week' => 3, 'description' => 'Points, lines, and angles'],
                ['name' => 'Triangles', 'term' => 2, 'week' => 4, 'description' => 'Properties of triangles'],
                ['name' => 'Quadrilaterals', 'term' => 2, 'week' => 5, 'description' => 'Types and properties of quadrilaterals'],
                ['name' => 'Perimeter and Area', 'term' => 3, 'week' => 1, 'description' => 'Calculating perimeter and area'],
                ['name' => 'Data Collection', 'term' => 3, 'week' => 2, 'description' => 'Methods of data collection'],
                ['name' => 'Data Representation', 'term' => 3, 'week' => 3, 'description' => 'Graphs and charts'],
            ];

            foreach ($topics as $index => $topic) {
                Topic::create([
                    'subject_id' => $mathForm1->id,
                    'name' => $topic['name'],
                    'term' => $topic['term'],
                    'week' => $topic['week'],
                    'description' => $topic['description'],
                    'estimated_hours' => 4,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        // Add sample topics for English Form 1
        $engForm1 = Subject::where('code', 'ENG_F1')->first();
        if ($engForm1) {
            $topics = [
                ['name' => 'Reading Comprehension', 'term' => 1, 'week' => 1, 'description' => 'Understanding written texts'],
                ['name' => 'Parts of Speech', 'term' => 1, 'week' => 2, 'description' => 'Nouns, verbs, adjectives, adverbs'],
                ['name' => 'Sentence Structure', 'term' => 1, 'week' => 3, 'description' => 'Building proper sentences'],
                ['name' => 'Vocabulary Building', 'term' => 1, 'week' => 4, 'description' => 'Expanding vocabulary'],
                ['name' => 'Narrative Writing', 'term' => 2, 'week' => 1, 'description' => 'Writing stories'],
                ['name' => 'Descriptive Writing', 'term' => 2, 'week' => 2, 'description' => 'Describing people, places, things'],
                ['name' => 'Poetry', 'term' => 2, 'week' => 3, 'description' => 'Understanding and writing poetry'],
                ['name' => 'Drama', 'term' => 3, 'week' => 1, 'description' => 'Introduction to drama'],
                ['name' => 'Letter Writing', 'term' => 3, 'week' => 2, 'description' => 'Formal and informal letters'],
            ];

            foreach ($topics as $index => $topic) {
                Topic::create([
                    'subject_id' => $engForm1->id,
                    'name' => $topic['name'],
                    'term' => $topic['term'],
                    'week' => $topic['week'],
                    'description' => $topic['description'],
                    'estimated_hours' => 4,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Malawian Syllabus seeded successfully!');
    }
}
