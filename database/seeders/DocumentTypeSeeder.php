<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            if(count(DocumentType::where('company_id', session('company_id'))->get()) == 0)
            {
                DocumentType::create([
                    'name' => 'Journal Voucher',
                    'prefix' => 'JV',
                    'company_id' => session('company_id'),
                ]);
                DocumentType::create([
                    'name' => 'Bank Payment Voucher',
                    'prefix' => 'BPV',
                    'company_id' => session('company_id'),
                ]);
            }
        });
    }
}
