<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CreateAcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $year  = SchoolYear::create([
            'year'=> 2020,
            'active'=> true
        ]);

    }
}
