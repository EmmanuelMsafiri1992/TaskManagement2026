<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teachers = [
            // Mathematics Teachers
            [
                'name' => 'John Banda',
                'email' => 'john.banda@example.com',
                'phone' => '+265 999 123 456',
                'national_id' => 'MW123456789',
                'address' => 'Area 47, Lilongwe',
                'specialty' => 'Mathematics',
                'qualification' => "Bachelor's in Mathematics Education",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5000.00,
                'bio' => 'Experienced mathematics teacher with 10 years of teaching secondary school students. Passionate about making mathematics accessible and enjoyable for all learners.',
            ],
            [
                'name' => 'Grace Phiri',
                'email' => 'grace.phiri@example.com',
                'phone' => '+265 888 234 567',
                'national_id' => 'MW234567890',
                'address' => 'Blantyre City',
                'specialty' => 'Mathematics',
                'qualification' => "Master's in Applied Mathematics",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 6500.00,
                'bio' => 'Mathematics educator specializing in advanced topics for Forms 3 and 4. Published author of mathematics study guides.',
            ],

            // English Teachers
            [
                'name' => 'Mary Chirwa',
                'email' => 'mary.chirwa@example.com',
                'phone' => '+265 991 345 678',
                'national_id' => 'MW345678901',
                'address' => 'Zomba',
                'specialty' => 'English',
                'qualification' => "Bachelor's in English Literature",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5000.00,
                'bio' => 'English language and literature specialist. Experienced in preparing students for MSCE examinations.',
            ],
            [
                'name' => 'Peter Mwale',
                'email' => 'peter.mwale@example.com',
                'phone' => '+265 995 456 789',
                'national_id' => 'MW456789012',
                'address' => 'Mzuzu City',
                'specialty' => 'English',
                'qualification' => "Master's in English Education",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 6000.00,
                'bio' => 'Creative writing coach and grammar specialist. 12 years of teaching experience across all secondary school forms.',
            ],

            // Science Teachers
            [
                'name' => 'James Kamwendo',
                'email' => 'james.kamwendo@example.com',
                'phone' => '+265 884 567 890',
                'national_id' => 'MW567890123',
                'address' => 'Area 25, Lilongwe',
                'specialty' => 'Physical Science',
                'qualification' => "Bachelor's in Physics",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5500.00,
                'bio' => 'Physics and chemistry teacher with hands-on approach to teaching. Expert in practical demonstrations and experiments.',
            ],
            [
                'name' => 'Esther Gondwe',
                'email' => 'esther.gondwe@example.com',
                'phone' => '+265 999 678 901',
                'national_id' => 'MW678901234',
                'address' => 'Limbe, Blantyre',
                'specialty' => 'Biology',
                'qualification' => "Master's in Biological Sciences",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 6000.00,
                'bio' => 'Biology educator with research background. Passionate about environmental education and conservation.',
            ],

            // Chichewa Teacher
            [
                'name' => 'Tamara Mbewe',
                'email' => 'tamara.mbewe@example.com',
                'phone' => '+265 888 789 012',
                'national_id' => 'MW789012345',
                'address' => 'Dedza',
                'specialty' => 'Chichewa',
                'qualification' => "Bachelor's in African Languages",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 4500.00,
                'bio' => 'Native Chichewa speaker with expertise in grammar and literature. Published Chichewa poetry author.',
            ],

            // Geography Teacher
            [
                'name' => 'Robert Nyirenda',
                'email' => 'robert.nyirenda@example.com',
                'phone' => '+265 991 890 123',
                'national_id' => 'MW890123456',
                'address' => 'Kasungu',
                'specialty' => 'Geography',
                'qualification' => "Bachelor's in Geography",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5000.00,
                'bio' => 'Geography teacher specializing in physical and human geography. Expert in map work and field studies.',
            ],

            // History Teacher
            [
                'name' => 'Florence Msiska',
                'email' => 'florence.msiska@example.com',
                'phone' => '+265 995 901 234',
                'national_id' => 'MW901234567',
                'address' => 'Nkhata Bay',
                'specialty' => 'History',
                'qualification' => "Master's in African History",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5500.00,
                'bio' => 'History educator with deep knowledge of Malawian and African history. Research focus on colonial and post-colonial periods.',
            ],

            // Computer Studies Teacher
            [
                'name' => 'Daniel Tembo',
                'email' => 'daniel.tembo@example.com',
                'phone' => '+265 884 012 345',
                'national_id' => 'MW012345678',
                'address' => 'Area 49, Lilongwe',
                'specialty' => 'Computer Studies',
                'qualification' => "Bachelor's in Computer Science",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 7000.00,
                'bio' => 'ICT educator with industry experience. Teaches programming, digital literacy, and computer applications.',
            ],

            // Business Studies Teacher
            [
                'name' => 'Catherine Jere',
                'email' => 'catherine.jere@example.com',
                'phone' => '+265 999 112 233',
                'national_id' => 'MW112233445',
                'address' => 'Mangochi',
                'specialty' => 'Business Studies',
                'qualification' => "Bachelor's in Business Administration",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5500.00,
                'bio' => 'Business studies teacher with entrepreneurship focus. Former business owner turned educator.',
            ],

            // Agriculture Teacher
            [
                'name' => 'Patrick Kachingwe',
                'email' => 'patrick.kachingwe@example.com',
                'phone' => '+265 888 223 344',
                'national_id' => 'MW223344556',
                'address' => 'Salima',
                'specialty' => 'Agriculture',
                'qualification' => "Bachelor's in Agricultural Science",
                'status' => 'active',
                'agreement_signed' => true,
                'hourly_rate' => 5000.00,
                'bio' => 'Agricultural science teacher with practical farming experience. Specializes in sustainable agriculture practices.',
            ],

            // Pending Teachers (not yet active)
            [
                'name' => 'Rachel Ng\'oma',
                'email' => 'rachel.ngoma@example.com',
                'phone' => '+265 991 334 455',
                'national_id' => 'MW334455667',
                'address' => 'Karonga',
                'specialty' => 'Life Skills',
                'qualification' => "Bachelor's in Psychology",
                'status' => 'pending',
                'agreement_signed' => false,
                'hourly_rate' => 4500.00,
                'bio' => 'Counselor and life skills educator. Focus on adolescent development and career guidance.',
            ],
            [
                'name' => 'Samuel Kalua',
                'email' => 'samuel.kalua@example.com',
                'phone' => '+265 995 445 566',
                'national_id' => 'MW445566778',
                'address' => 'Thyolo',
                'specialty' => 'Accounting',
                'qualification' => "Bachelor's in Accounting",
                'status' => 'pending',
                'agreement_signed' => false,
                'hourly_rate' => 5500.00,
                'bio' => 'Chartered accountant transitioning to education. Strong focus on practical accounting skills.',
            ],

            // Suspended Teacher
            [
                'name' => 'Michael Banda',
                'email' => 'michael.banda@example.com',
                'phone' => '+265 884 556 677',
                'national_id' => 'MW556677889',
                'address' => 'Ntcheu',
                'specialty' => 'Technical Drawing',
                'qualification' => "Diploma in Technical Education",
                'status' => 'suspended',
                'agreement_signed' => true,
                'hourly_rate' => 4500.00,
                'bio' => 'Technical drawing and design teacher.',
            ],
        ];

        foreach ($teachers as $teacher) {
            ServiceProvider::create([
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'phone' => $teacher['phone'],
                'password' => Hash::make('password123'),
                'national_id' => $teacher['national_id'],
                'address' => $teacher['address'],
                'specialty' => $teacher['specialty'],
                'qualification' => $teacher['qualification'],
                'status' => $teacher['status'],
                'agreement_signed' => $teacher['agreement_signed'],
                'agreement_signed_at' => $teacher['agreement_signed'] ? now() : null,
                'hourly_rate' => $teacher['hourly_rate'],
                'bio' => $teacher['bio'],
            ]);
        }

        $this->command->info('Teachers seeded successfully! Created ' . count($teachers) . ' teacher records.');
    }
}
