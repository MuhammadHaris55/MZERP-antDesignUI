<?php

namespace App\Exports;

use App\Models\Entry;
use App\Models\Year;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EntryExport implements FromCollection, WithHeadings
{
    protected $search_word;
    protected $ReqStart;
    protected $ReqEnd;

    public function __construct($search_word = null, $ReqStart = null, $ReqEnd = null) 
    {
        $this->search_word = $search_word;
        $this->ReqStart = $ReqStart;
        $this->ReqEnd = $ReqEnd;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $date_range = Year::where('id', session('year_id'))->first();
            $start = new Carbon($date_range->begin);
            if ($this->ReqStart != null && $this->ReqEnd != null ) {
                $start = Carbon::create($start);
                $ReqStart = Carbon::create($this->ReqStart);
                $ReqEnd = Carbon::create($this->ReqEnd);
                if($start->greaterThan($ReqStart) || $start->greaterThan($ReqEnd)){
                    return Redirect::route('documents_detail')->with('warning', 'Dates Not Correct');
               }
                $ReqStart = $ReqStart->format('Y-m-d');
                $ReqEnd = $ReqEnd->format('Y-m-d');
                $search_word = $this->search_word;
                

                $obj_data = Entry::orderBy('id','Asc')
                    ->where(function ($query) use ($search_word , $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                            ->whereHas('document',function($r) use ($search_word , $ReqStart , $ReqEnd)  {
                                $r->where('description', 'LIKE', '%' . $search_word . '%');
                                $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                            });
                    })->orWhere(function ($query) use ($search_word, $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                             ->whereHas('document',function($r) use ($search_word, $ReqStart , $ReqEnd){
                                $r->where('ref', 'LIKE', '%' . $search_word . '%');
                                    $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                                });
                    })->orWhere(function ($query) use ($search_word, $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                            ->whereHas('document',function($r) use ($search_word, $ReqStart , $ReqEnd) {
                            // $search_word = Carbon::create($search_word)->toDateString();
                                $r->where('date', 'LIKE', '%' . $search_word . '%');
                                    $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                            });
                    })->orWhere(function ($query) use ($search_word, $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                            ->where('debit', 'LIKE', '%' . $search_word . '%')
                            ->whereHas('document',function($r) use ($search_word, $ReqStart , $ReqEnd) {
                                    $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                            });


                    })
                    ->orWhere(function ($query) use ($search_word, $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                            ->where('credit', 'LIKE', '%' . $search_word . '%')
                            ->whereHas('document',function($r) use ($search_word, $ReqStart , $ReqEnd) {
                                    $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                            });

                    })
                    ->orWhere(function ($query) use ($search_word, $ReqStart , $ReqEnd) {
                        $query
                            ->where('company_id', session('company_id'))
                            ->where('year_id', session('year_id'))
                             ->whereHas('document',function($r) use ($search_word, $ReqStart , $ReqEnd) {
                                    $r->where('date', '>=', $ReqStart )->where('date', '<=', $ReqEnd );

                            })
                            ->whereHas('account',function($r) use ($search_word){
                                $r->where('name', 'LIKE', '%' . $search_word . '%');
                           });

                    });

            
            } else {
                $obj_data = Entry::where('company_id', session('company_id'))
                    ->where('year_id', session('year_id'));
            }

           
        $obj_data = $obj_data->orderBy('created_at', 'desc')
            ->get();

        return $obj_data->map(function ($entry) {
            return [
                'ID' => optional($entry->document)->id ?? '-',
                'Date' => optional($entry->document)->date ?? '-',
                'Reference' => optional($entry->document)->ref ?? '-',
                'Description' => optional($entry->document)->description ?? '-',
                'Accounts' => optional($entry->account)->name 
                             ? optional($entry->account)->name . ' - ' . optional(optional($entry->account)->accountGroup)->name 
                             : '-',
                'Debit' => $entry->debit ? number_format($entry->debit, 2) : '0.00',
                'Credit' => $entry->credit ? number_format($entry->credit, 2) : '0.00',
            ];
        });
    }

    public function headings(): array
    {
        return ["ID", "Date", "Reference", "Description", "Accounts", "Debit", "Credit"];
    }
}
