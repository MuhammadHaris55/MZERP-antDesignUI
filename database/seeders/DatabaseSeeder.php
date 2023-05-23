<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // if(count(Company::all()) == 0){
        //     Company::create([
        //     'name' => 'abc',
        //     ]);
        // }

        $this->call([
            PermissionsDemoSeeder::class,
        ]);
        $this->call([
            TypeSeeder::class,
        ]);
        // $this->call([
        //     GroupSeeder::class,
        // ]);
        // $this->call([
        //     AccountSeeder::class,
        // ]);
        // $this->call([
        //     DocumentTypeSeeder::class,
        // ]);
    }
}
