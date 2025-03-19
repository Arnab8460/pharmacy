<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InstituteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('wbscte_other_diploma_institute_master')->insert([
            [
                'institute_code' => 'IAM',
                'institute_name' => 'INSTITUTE OF ADVANCED MANAGEMENT',
                'institute_address' => 'AN1,NAYAPATTI,SALT LAKE SECTOR V,KOLKATA-700102',
            ],
            [
                'institute_code' => 'IIHM',
                'institute_name' => 'INTERNATIONAL INSTITUTE OF HOTEL MAMAGEMENT',
                'institute_address' => 'INTERNATIONAL TOWER,X-1,8/3,BLOCK EP, SALTLAKE ELECTRONICS COMPLEX, SECTOR V',
            ],
            [
                'institute_code' => 'OMD',
                'institute_name' => 'Om Dayal Group of Institutions',
                'institute_address' => 'Uluberia Industrial Growth Centre, Uluberia, Howrah-711316',
            ],
            [
                'institute_code' => 'SRS',
                'institute_name' => 'Shree Ramkrishna Institute of Science & Technology',
                'institute_address' => 'DAKSHIN GOBINDAPUR, P.S- SONARPUR KOLKATA – 700145, DIST – 24 PGS (S)',
            ],
            [
                'institute_code' => 'LPI',
                'institute_name' => 'Luthfaa Polytechnic Institute',
                'institute_address' => 'P.O: Molandighi,P.S: Kanksa Durgapur-713212,West Bengal.',
            ],
            [
                'institute_code' => 'DIFSM',
                'institute_name' => 'DURGAPUR INSTITUTE FOR FIRE SAFETY & MANAGEMENT',
                'institute_address' => 'Vill & PO- Rajbandh, Lalbari, Beside NH2, PS- Kanksa, Pin Code- 713212, Dist- Paschim Bardhaman, WB',
            ],
            [
                'institute_code' => 'DSSPE',
                'institute_name' => 'DURGPUR SOCIETY FOR SAFETY AND PROFESSIONAL EDUCATION',
                'institute_address' => 'ASHOK AVENUE, A-ZONE, BESIDE A-ZONE STATE BANK, DURGAPUR-713204',
            ],
            [
                'institute_code' => 'IFSE',
                'institute_name' => 'INSTITUTE OF FIRE AND SAFETY ENGINEERING',
                'institute_address' => 'Vill - Brajalalchak, PO - Dakshinchak, Haldia - 721654, Purba Medinipur',
            ],
            [
                'institute_code' => 'SVDET',
                'institute_name' => 'SWAMI VIVEKANANDA DEVELOPMENT & EDUCATIONAL TRUST',
                'institute_address' => 'Bankura Private ITI, Vill. Salgara, P.O. & P.S. Barjora, Dist. Bankura, PIN-722202, W.B.',
            ],
            [
                'institute_code' => 'AEI',
                'institute_name' => 'THE ASSOCIATION OF ENGINEERS, INDIA',
                'institute_address' => 'IA -11, SALT LAKE CITY, SECTOR - III, KOLKATA - 700097.',
            ],
            [
                'institute_code' => 'RLI',
                'institute_name' => 'REGIONAL LABOUR INSTITUTE KOLKATA',
                'institute_address' => 'Regional Labour Institute, 
                Government of India,
                Ministry of Labour & Employment
                Lake Town, Kolkata - 700089',
            ],
            [
                'institute_code' => 'SPC',
                'institute_name' => 'STATE PRODUCTIVITY COUNCIL',
                'institute_address' => '9, syed amir ali avenue Kolkata-700017',
            ],
            [
                'institute_code' => 'AOHS',
                'institute_name' => 'ACADEMY OF OCCUPATIONAL HEALTH & SAFETY',
                'institute_address' => 'C/O AMIK, 3&4, KABIGURU SARANI. CITY CENTRE, DURGAPUR-713216, PASCHIM BARDHAMAN, W.B.',
            ],
            [
                'institute_code' => 'VISF',
                'institute_name' => 'VIDYASAGAR INSTITUTE OF SAFETY & FIRE TECHNOLOGY',
                'institute_address' => 'Vidyasagar Institute of Safety & Fire Technology, Mahishadal, Garh Kamalpur, Dist- Purba Medinipur, Pin- 721628',
            ],
            [
                'institute_code' => 'ESA',
                'institute_name' => 'EDUVISTA SKILLPOWER ACADEMY',
                'institute_address' => '130/7 Dumdum Road . Near Indira Maidan. First floor . Kolkata-700 074',
            ],
            [
                'institute_code' => 'IISM',
                'institute_name' => 'INDIAN INSTITUTE OF SAFETY MANAGEMENT',
                'institute_address' => 'Hindustan Gas Co.  Road, Parbangla (Nangi), P.O - Batanagar, P. S - Maheshtala, Dist - South 24 Paraganas, Kolkata - 700140.',
            ],
            [
                'institute_code' => 'ACTC',
                'institute_name' => 'ASUTOSH COLLEGE TRAINING CENTRE',
                'institute_address' => '10 BASANTA BOSE ROAD.KOL-26',
            ],
            [
                'institute_code' => 'SISD',
                'institute_name' => 'SUSRIJO INSTITUTE OF SKILL DEVELOPMENT',
                'institute_address' => 'WEBEL IT PARK,
                DEYPARA, P.S-KOTWALI,
                P.O- KRISHNAGAR, DIST-
                NADIA, PIN- 741101',
            ],
        ]);
    }
}
