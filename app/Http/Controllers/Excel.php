<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Models\Salary;
use App\Models\AccountType;
use App\Models\AccountGroup;
use App\Models\Account;
use App\Models\DocumentType;
use App\Models\Document;
use App\Models\Entry;
// use App\Models\Trial;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class Excel extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'avatar'=> 'required|mimes:xlsx, xls'
        ]);
        // dd($request);
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($request->file('avatar'));

        $array_count = 0;
        $xl_date_array = [];
        $description_array;
        $amount_array;
        $acc_id_array;

        foreach ($reader->getSheetIterator() as $sheet) {
            // only read data from 1st sheet
            if ($sheet->getIndex() === 0) { // index is 0-based
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if($rowIndex === 1) continue; // skip headers row

                    $col1 = $row->getCellAtIndex(0)->getValue();
                    $col2 = $row->getCellAtIndex(1)->getValue();
                    $col3 = $row->getCellAtIndex(2)->getValue();
                    $col4 = $row->getCellAtIndex(3)->getValue();
                    if($col1 || $col2 || $col3 || $col4)
                    {
                        if(!$col1)
                        {
                            return back()->with('error', 'Enter date of transaction in column A of the sheet. Column A is empty at some point, or you submitted the wrong format');
                        }
                        if(!$col2)
                        {
                            return back()->with('error', 'Enter description of transaction in column B of the sheet. Column B is empty at some point, or you submitted the wrong format');
                        }
                        if(!$col4)
                        {
                            return back()->with('error', 'Enter amount of transaction in column D of the sheet. Column D is empty at some point, or you submitted the wrong format');
                        }
                        // return back()->with('error', 'Enter data in right format');
                    }
                    if($col1 && $col2 && $col4)
                    {
                        $xl_date = $row->getCellAtIndex(0)->getValue();
                        $xl_date_array[$array_count] = $xl_date;

                        $description = $row->getCellAtIndex(1)->getValue();
                        $description_array[$array_count] = $description;

                        $amount = $row->getCellAtIndex(3)->getValue();
                        $amount_array[$array_count] = $amount;

                        $type = DocumentType::where('name', 'Journal Voucher')->
                            where('company_id', session('company_id'))->
                            first();
                        if($type)
                        {
                            $type_id = $type->id;
                        }else {
                            return back()->with('error', 'Voucher with name \'Journal Voucher\' doesn\'t exists or having a spelling mistake');
                        }
                        // $type_id_array[$array_count] = $type_id;

                        //Accounts
                        $acc_id = $row->getCellAtIndex(2)->getValue();
                        if($acc_id)
                        {
                            $acc_exist = Account::
                                where('company_id', session('company_id'))->
                                find($acc_id);
                            if(!$acc_exist)
                            {
                                return back()->with('error', 'Account Id on Row '. $rowIndex . ' is invalid or incorrect.');
                            } else {
                                $acc_id = $acc_exist->id;
                                $acc_id_array[$array_count] = $acc_id;
                            }
                        } else {
                            $default_acc = Account::where('name', 'Cash at Bank')->
                                where('company_id', session('company_id'))->
                                first();
                            if($default_acc)
                            {
                                $acc_id = $default_acc->id;
                                $acc_id_array[$array_count] = $acc_id;
                            }else {
                                return back()->with('error', 'Account with name \'Cash at Bank\' account doesn\'t exists or having a spelling mistake.');
                            }
                        }

                        if($acc_id && $type_id && $xl_date && $description && $amount)
                        {
                            // here we have to create transactions

                        } else {
                            return back()->with('error', 'Check excel you may enter some wrong data');
                            //return with error in xl sheet
                        }
                    } else {
                        return back()->with('error', 'You may enter some data in wrong format');
                    }






                    // $col1 = $row->getCellAtIndex(0); // id
                    // $col2 = $row->getCellAtIndex(1)->getValue(); // joining date - using getValue() method of Cell object to get actual date value
                    // $col3 = $row->getCellAtIndex(2); // name
                    // $col4 = $row->getCellAtIndex(3); // department
                    // $col5 = $row->getCellAtIndex(4); // designation
                    // $col6 = $row->getCellAtIndex(5); // gross salary
                    // $col7 = $row->getCellAtIndex(6); // medical
                    // $col8 = $row->getCellAtIndex(7); // conveyance
                    // Salary::create([
                    //     // no need to insert $col1 as the id auto-generates
                    //     'joining_date' => $col2,
                    //     'name' => $col3,
                    //     'department' => $col4,
                    //     'designation' => $col5,
                    //     'gross_salary' => $col6,
                    //     'medical' => $col7,
                    //     'conveyance' => $col8,
                    // ]);
                    $array_count++;
                }
                break; // no need to read more sheets
            }
            $reader->close();
        }

        if($array_count >> 0 && count($xl_date_array) >> 0
            && count($description_array) >> 0
            && count($amount_array) >> 0
            && count($acc_id_array) >> 0
        )
        {
                // DB::transaction(function () use ($acc_id, $type_id, $xl_date, $description, $amount) {
                DB::transaction(function () use ($acc_id_array, $type_id, $xl_date_array, $description_array, $amount_array, $array_count) {
                // DB::transaction(function () use ($acc_id_array[$i], $type_id, $xl_date_array[$i], $description_array[$i], $amount_array[$i]) {
                    for($i = 0; $i < $array_count; $i++)
                    {
                        $acc_id = $acc_id_array[$i];
                        $xl_date = $xl_date_array[$i];
                        $description = $description_array[$i];
                        $amount = $amount_array[$i];

                        $date = new Carbon($xl_date);
                        try {
                            $prefix = DocumentType::where('company_id', session('company_id'))->where('id', $type_id)->first()->prefix;
                            $date = $date->format('Y-m-d');
                            $ref_date_parts = explode("-", $date);

                            //serial number
                            $latest_doc = Document::where('ref', 'LIKE', $prefix . '%')->where('year_id', session('year_id'))->latest('id')->first();
                            if ($latest_doc) {
                                $pre_refe = $latest_doc->ref;
                                $pre_ref_serial = explode("/", $pre_refe);
                                $serial = (int)$pre_ref_serial[3] + (int)1;
                            } else {
                                $serial = 1;
                            }
                            //serial number

                            // $reference = $prefix . "/" . $ref_date_parts[0] . "/" . $ref_date_parts[1] . "/" . $ref_date_parts[2];
                            // $reference = $prefix . "/" . $ref_date_parts[0] . "/" . $ref_date_parts[1] . "/" . $ref_date_parts[2] . "/" . $serial;
                            $reference = $prefix . "/" . $ref_date_parts[0] . "/" . $ref_date_parts[1] . "/" . $serial;

                            $doc = Document::create([
                                'type_id' => $type_id,
                                'company_id' => session('company_id'),
                                'description' => $description,
                                'ref' => $reference,
                                'date' => $date,
                                'year_id' => session('year_id'),
                            ]);
                            // $data = $request->entries;
                                Entry::create([
                                    'company_id' => $doc->company_id,
                                    'account_id' => $acc_id,
                                    'year_id' => $doc->year_id,
                                    'document_id' => $doc->id,
                                    'debit' => $amount,
                                    'credit' => 0,
                                ]);
                                $sales_acc = Account::where('name', 'Sales - Local')
                                    ->where('company_id', session('company_id'))
                                    ->first();
                                $sales_acc_id = $sales_acc->id;
                                Entry::create([
                                    'company_id' => $doc->company_id,
                                    'account_id' => $sales_acc_id,
                                    'year_id' => $doc->year_id,
                                    'document_id' => $doc->id,
                                    'debit' => 0,
                                    'credit' => $amount,
                                ]);

                        } catch (Exception $e) {
                            return back()->with('error', $e);
                            return $e;
                        }
                    }
                });
        }else {
            return back()->with('error', 'You may upload an empty file');
        }
        return Redirect::route('documents')->with('success', 'Transactions created.');
        // return Redirect::route('companies');
    }
}
