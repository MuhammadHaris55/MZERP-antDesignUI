<?php

use App\Models\Company;
use App\Models\Year;
use Carbon\Carbon;


        function companies_get()
        {
            return auth()->user()->companies;

        }
         function companies_first()
        {
            return Company::where('id', session('company_id'))->first();

        }


        function years_get()
        {
            return Year::where('company_id', session('company_id'))->get()->map(function ($year) {
                        $begin = new Carbon($year->begin);
                        $end = new Carbon($year->end);
                        return [
                            'id' => $year->id,
                            'end' => $begin->format('M d, Y') . ' - ' . $end->format('M d, Y'),
                        ];
                    },
                );
        }

         function years_first()
        {
            return Year::where('company_id', session('company_id'))
                    ->where('id', session('year_id'))->get()
                    ->map(function ($year) {
                        $begin = new Carbon($year->begin);
                        $end = new Carbon($year->end);

                        return [
                            'id' => $year->id,
                            'name' => $begin->format('M d, Y') . ' - ' . $end->format('M d, Y'),
                        ];
                    },
                )->first();
        }

?>
