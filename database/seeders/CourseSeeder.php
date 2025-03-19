<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('wbscte_other_diploma_course_master')->insert([
            [
                'course_code' => 'HMCT',
                'course_name' => 'Diploma in Hotel Management and Catering Technology',
                'inst_id' => '1',
                'course_duration' => '3 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'HMCT',
                'course_name' => 'Diploma in Hotel Management and Catering Technology',
                'inst_id' => '2',
                'course_duration' => '3 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'DVOC',
                'course_name' => 'Refrigeration & Air Conditioning',
                'inst_id' => '3',
                'course_duration' => '6 months',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'DVOC',
                'course_name' => 'Automobile Servicing',
                'inst_id' => '4',
                'course_duration' => '6 months',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'DVOC',
                'course_name' => 'Electronics Manufacturing Service',
                'inst_id' => '4',
                'course_duration' => '6 Months',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'DVOC',
                'course_name' => 'Cyber Security',
                'inst_id' => '5',
                'course_duration' => '1.5 Years',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'DVOC',
                'course_name' => 'AI & Robotics',
                'inst_id' => '5',
                'course_duration' => '6 Months',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '6',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '6',
                'course_duration' => '1.5 Years',
                'course_type' => 'Half Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '7',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '8',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '8',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '9',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '10',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '11',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '12',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '13',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '14',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '15',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (F)',
                'course_name' => 'Advance Diploma in Industrial Safety (Full Time)',
                'inst_id' => '16',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADIS (P)',
                'course_name' => 'Advance Diploma in Industrial Safety (Part Time)',
                'inst_id' => '17',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADFSM',
                'course_name' => 'Advanced Diploma in Fire Safety
                Management (Full Time)',
                'inst_id' => '6',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADFSM',
                'course_name' => 'Advanced Diploma in Fire Safety
                Management (Full Time)',
                'inst_id' => '8',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADFSM',
                'course_name' => 'Advanced Diploma in Fire Safety
                Management (Full Time)',
                'inst_id' => '16',
                'course_duration' => '1 Year',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],
            [
                'course_code' => 'ADFSM',
                'course_name' => 'Advanced Diploma in Fire Safety Management (Part Time)',
                'inst_id' => '18',
                'course_duration' => '1.5 Years',
                'course_type' => 'Full Paper',
                'course_affiliation_year' => '2023-24',
            ],

        ]);
    }
}
