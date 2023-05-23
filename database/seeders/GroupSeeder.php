<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;


class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {

            // ==============================================================================================================================
            $type_id = \App\Models\AccountType::where('name', 'Assets')->first()->id;
            $ass_fix = AccountGroup::create([
                'name' => 'Fixed Assets',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);

            $ass_curr = AccountGroup::create([
                'name' => 'Current Assets',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);

            AccountGroup::create([
                'name' => 'Stock-in-Trade',
                'type_id' => $type_id,
                'parent_id' => $ass_curr->id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Accounts Receivables',
                'type_id' => $type_id,
                'parent_id' => $ass_curr->id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Loans & Advances',
                'parent_id' => $ass_curr->id,
                'type_id' => $type_id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Deposits, Prepayments & Other Receivables',
                'parent_id' => $ass_curr->id,
                'type_id' => $type_id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'parent_id' => $ass_curr->id,
                'name' => 'Cash & Bank',
                'type_id' => $type_id,
                'company_id' => session('company_id'),
            ]);

            // ==============================================================================================================================
            $type_id = \App\Models\AccountType::where('name', 'Capital')->first()->id;
            $equity = AccountGroup::create([
                'name' => 'Equity',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Reserves',
                'type_id' => $type_id,
                'parent_id' => $equity->id,
                'company_id' => session('company_id'),
            ]);

            // ==============================================================================================================================
            $type_id = \App\Models\AccountType::where('name', 'Liabilities')->first()->id;
            $acc_l_t_liab = AccountGroup::create([
                'name' => 'Long Term Liabilities',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);

            $acc_s_t_liab =  AccountGroup::create([
                'name' => 'Short Term Liabilities',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Short Term Loans',
                'type_id' => $type_id,
                'parent_id' => $acc_s_t_liab->id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Advances, Deposits & Other Liabilities',
                'parent_id' => $acc_s_t_liab->id,
                'type_id' => $type_id,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Accounts Payables',
                'parent_id' => $acc_s_t_liab->id,
                'type_id' => $type_id,
                'company_id' => session('company_id'),
            ]);

            // ==============================================================================================================================
            $type_id = \App\Models\AccountType::where('name', 'Revenue')->first()->id;
            AccountGroup::create([
                'name' => 'Sales & Service',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Other Income',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);

            // ==============================================================================================================================
            $type_id = \App\Models\AccountType::where('name', 'Expenses')->first()->id;
            AccountGroup::create([
                'name' => 'Operating Expenses',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Administrative Expenses',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
            AccountGroup::create([
                'name' => 'Taxes',
                'type_id' => $type_id,
                'parent_id' => null,
                'company_id' => session('company_id'),
            ]);
        });

        $this->call([
            AccountSeeder::class,
        ]);

        return Redirect::back();
    }
}
