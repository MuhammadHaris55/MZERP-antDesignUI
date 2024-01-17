<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountGroup;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Year;
use App\Models\Setting;
use Egulias\EmailValidator\Warning\Warning;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


use App;
use App\Models\AccountType;
use App\Models\Document;
use App\Models\Entry;
use App\Models\DocumentType;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;


class DashboardController extends Controller
{

    public function index()
    {
        if(session('company_id') && session('year_id')){
            $acc_types = AccountType::all();
            $year = Year::find(session('year_id'));
            $startDate = Carbon::parse($year->begin);
            $endDate = Carbon::parse($year->end);
            $i = 0;
            $dashboard_variables = [];
            $expense = $revenue = 0;
            foreach($acc_types as $key => $type)
            {
                    if($type->name == 'Assets' || $type->name == 'Expenses')
                    {
                        $amount = $this->calculateDebitSumByAccountGroup($type->id, 'debit' , $startDate, $endDate);
                        $dashboard_variables[] = ['name' => $type->name, 'amount' => number_format($amount,2)];
                        if($type->name == 'Expenses') $expense = $amount;
                    } else {

                            $amount = $this->calculateDebitSumByAccountGroup($type->id, 'credit', $startDate, $endDate);
                            // dd($amount);
                            $dashboard_variables[] = ['name' => $type->name, 'amount' => number_format($amount,2)];
                            if($type->name == 'Revenue') $revenue = $amount;

                    }


            }

            // Profit and Lose value working
            $pnl = $revenue - $expense;
            if ($pnl >= 0)
            {
                $dashboard_variables[] = ['name' => 'Total Profit/loss', 'amount' => number_format($pnl,2)];
            } else {
                $dashboard_variables[] = ['name' => 'Total Profit/loss', 'amount' => '(' . number_format(abs($pnl),2) . ')'];
            }

            // dd($Assets, $Liabilities, $Capital, $Revenue, $Expenses);

            $revenue_id = AccountType::where('name', 'Revenue')->first();
            $monthly_revenue = $this->calculateMonthlyRevenue($revenue_id->id, $startDate, $endDate);
            $monthly_revenue_value =  (array_values($monthly_revenue));
            $monthly_revenue_month =  (array_keys($monthly_revenue));
            return Inertia::render('Dashboard', [
            'dashboard_variables' => $dashboard_variables,
            'monthly_revenue_value' =>$monthly_revenue_value,
            'monthly_revenue_month' =>$monthly_revenue_month,
            'all_companies' => Company::all(),
            'company' => companies_first(),
            'companies' => companies_get(),
            'years' => years_get(),
            'year' => years_first(),
            // 'roles' => $roles,
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
            //To make the role assigning part visible only for haris@gmail.com
            'user' => auth()->user(),
        ]);
        }else
        {
            return redirect()->route('companies');
        }



    }

    public function calculateMonthlyRevenue($id, $startDate, $endDate)
    {
        $accountGroups = AccountGroup::where('type_id', $id)
        ->where('company_id', session('company_id'))
        ->with('accounts.entries.document')
        ->get();

        $monthlySums = [];
        $currentMonth = $startDate->copy();

        while ($currentMonth->lte($endDate)) {
            $monthName = $currentMonth->format('M');
            $monthlySums[$monthName] = 0;

            $currentMonth->addMonth();
        }

        foreach ($accountGroups as $accountGroup) {
            foreach ($accountGroup->accounts as $account) {
                foreach ($account->entries as $entry) {
                    $entryDate = Carbon::parse($entry->document->date);
                    if ($entryDate->between($startDate, $endDate)) {
                        $monthName = $entryDate->format('M');
                        $monthlySums[$monthName] += $entry->credit;
                    }
                }
            }
        }
        return $monthlySums;

    }

    public function calculateDebitSumByAccountGroup($id, $amount_type  ,$startDate, $endDate)
    {
        $accountGroups = AccountGroup::where('type_id', $id)
            ->where('company_id', session('company_id'))
            ->with('accounts.entries')
            ->get();

        $sum_of_acc = [];

        foreach ($accountGroups as $accountGroup) {
        if ($amount_type == 'debit') {
            $debitSum = $accountGroup->accounts->sum(function ($account) use ($startDate, $endDate) {
                return $account->entries->sum(function ($entry) use ($startDate, $endDate) {
                    $entryDate = Carbon::parse($entry->document->date);
                    if ($entryDate->between($startDate, $endDate)) {
                        return intval($entry->debit);
                    } else {
                        return 0; // Return 0 for entries outside the specified date range
                    }
                });
            });
            $lessCreditSum = $accountGroup->accounts->sum(function ($account) use ($startDate, $endDate) {
                return $account->entries->sum(function ($entry) use ($startDate, $endDate) {
                    $entryDate = Carbon::parse($entry->document->date);
                    if ($entryDate->between($startDate, $endDate)) {
                        return intval($entry->credit);
                    } else {
                        return 0; // Return 0 for entries outside the specified date range
                    }
                });
            });

            $sum_of_acc[$accountGroup->name] = $debitSum - $lessCreditSum;
        } else if ($amount_type == 'credit') {
            $creditSum = $accountGroup->accounts->sum(function ($account) use ($startDate, $endDate) {
                return $account->entries->sum(function ($entry) use ($startDate, $endDate) {
                    $entryDate = Carbon::parse($entry->document->date);
                    if ($entryDate->between($startDate, $endDate)) {
                        return intval($entry->credit);
                    } else {
                        return 0; // Return 0 for entries outside the specified date range
                    }
                });

            });
             $lessDebitSum = $accountGroup->accounts->sum(function ($account) use ($startDate, $endDate) {
                return $account->entries->sum(function ($entry) use ($startDate, $endDate) {
                    $entryDate = Carbon::parse($entry->document->date);
                    if ($entryDate->between($startDate, $endDate)) {
                        return intval($entry->debit);
                    } else {
                        return 0; // Return 0 for entries outside the specified date range
                    }
                });
            });


            $sum_of_acc[$accountGroup->name] = $creditSum - $lessDebitSum;
        }
    }




        $final_sum = 0;
        foreach ($sum_of_acc as $acc)
        {
            $final_sum += $acc;
        }

        // return $sum_of_acc;
        return $final_sum;
    }


    public function roleassign()
    {
        $data['email'] = Request::input('email');
        $data['role'] = Request::input('role');
        $data['company'] = Request::input('company_id')['id'];

        Request::validate([
            'email' => ['required'],
            'role' => ['required'],
            'company_id' => ['required'],
        ]);

        $userexist = User::where('email',$data['email'])->first('id');
        if(auth()->user()->email != $data['email'])
        {
            if($userexist){
                $userexist->roles()->detach();
                $userexist->assignRole($data['role']);

                $company = Company::where('id', $data['company'])->with('users')->first();
                $year = Year::where('company_id', $company->id)->first();
                $check = true;
                foreach($company->users as $comp_user)
                {
                    if($comp_user->email == $data['email'])
                    {

                        $check = false;
                        break;
                    }
                }
                if($check)
                {


                    $company->users()->attach($userexist->id);


                }
            } else {
                return Redirect::back()->with('warning', 'User email doesn\'t exists');
            }
        } else {
                return Redirect::back()->with('warning', 'You can\'t change your own role');
        }
                    $set_comp = Setting::where('user_id', $userexist->id)->where('key', 'active_company')->first();
                    $set_year = Setting::where('user_id', $userexist->id)->where('key', 'active_year')->first();

                    if ($set_comp) {
                        $set_comp->value = $company->id;
                        $set_comp->save();
                    } else {
                        // Create Active Company Setting
                        Setting::create([
                            'key' => 'active_company',
                            'value' => $company->id,
                            'user_id' => $userexist->id,
                        ]);
                    }
                    if ($set_year) {
                        $set_year->value = $year->id;
                        $set_year->save();
                    } else {
                        // Create Active Year Setting
                        Setting::create([
                            'key' => 'active_year',
                            'value' => $year->id,
                            'user_id' => $userexist->id,
                        ]);
                    }

        return Redirect::back()->with('success', 'Role assigned.');
    }

    public function create()
    {

    }

    public function store()
    {
        Request::validate([
            'email' => ['required'],
            'company_id' => ['required'],
            'role' => ['required'],
        ]);

        return Redirect::route('companies')->with('success', 'Company created');
    }

    public function edit(Company $company)
    {
        return Inertia::render('Company/Edit', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'address' => $company->address,
                'email' => $company->email,
                'web' => $company->web,
                'phone' => $company->phone,
                'fiscal' => $company->fiscal,
                'incorp' => $company->incorp,
            ],
        ]);
    }

    public function update(Company $company)
    {
        Request::validate([
            'name' => ['required'],
            'address' => ['nullable'],
            'email' => ['nullable'],
            'web' => ['nullable'],
            'phone' => ['nullable'],
            'fiscal' => ['required'],
            'incorp' => ['nullable'],
        ]);

        $company->name = Request::input('name');
        $company->address = Request::input('address');
        $company->email = Request::input('email');
        $company->web = Request::input('web');
        $company->phone = Request::input('phone');
        $company->fiscal = Request::input('fiscal');

        $incorp = new carbon(Request::input('incorp'));
        $company->incorp = $incorp->format('Y-m-d');

        $company->save();

        return Redirect::route('companies')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return Redirect::back()->with('success', 'Company deleted.');
    }

    //TO CHANGE THE COMPANY IN SESSION FROM DROPDOWN
    public function coch($id)
    {
        $active_co = Setting::where('user_id', Auth::user()->id)->where('key', 'active_company')->first();

        $active_co->value = $id;

        $active_co->save();
        session(['company_id' => $id]);

        if (Year::where('company_id', $id)->latest()->first()) {
            $active_yr = Setting::where('user_id', Auth::user()->id)->where('key', 'active_year')->first();
            $active_yr->value = Year::where('company_id', $id)->latest()->first()->id;
            $active_yr->save();
            session(['year_id' => $active_yr->value]);
            // $active_co->save();
            // session(['company_id' => $id]);
            return Redirect::back();
        } else {
            session(['year_id' => null]);
            return Redirect::route('years.create')->with('success', 'YEAR NOT FOUND. Please create an Year for selected Company.');
        }
    }
}
