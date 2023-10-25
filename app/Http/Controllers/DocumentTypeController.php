<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use App\Models\DocumentType;
use App\Models\Company;
use App\Models\Year;
use App\Models\Document;
use Inertia\Inertia;

use Carbon\Carbon;
use Illuminate\Http\Request as Req;

class DocumentTypeController extends Controller
{
    public function index()
    {
        if(request()->has(
            // ['select', 'search']
            'search'
            ))
        {
            $obj_data = DocumentType::where(
                // $req->select
                'name'
                ,'LIKE', '%'.$req->search.'%')
            ->where('company_id', session('company_id'))
            ->get();
            $mapped_data = $obj_data->map(function($acc_group, $key) {
            return [
                    'id' => $doc_type->id,
                    'name' => $doc_type->name,
                    'prefix' => $doc_type->prefix,
                    'delete' => Document::where('type_id', $doc_type->id)->first() ? false : true,
                ];
            });
        }
        else{
            $obj_data = DocumentType::where('company_id', session('company_id'))->get();
            $mapped_data = $obj_data->map(function($doc_type, $key) {
            return [
                    'id' => $doc_type->id,
                    'name' => $doc_type->name,
                    'prefix' => $doc_type->prefix,
                    'delete' => Document::where('type_id', $doc_type->id)->first() ? false : true,
                ];
            });
        }

        $query = DocumentType::query();

        return Inertia::render('DocumentTypes/Index', [

            'balances' => $query->where('company_id', session('company_id'))
                // ->map(
                ->paginate(10)
                ->through(function ($doc_type) {
                    return [
                        'id' => $doc_type->id,
                        'name' => $doc_type->name,
                        'prefix' => $doc_type->prefix,
                        'delete' => Document::where('type_id', $doc_type->id)->first() ? false : true,
                    ];
                }),
            'mapped_data' => $mapped_data,
            'company' => companies_first(),
            'companies' => companies_get(),
            'years' => years_get(),
            'year' => years_first(),
            'can' => [
                'edit' => auth()->user()->can('edit'),
                'create' => auth()->user()->can('create'),
                'delete' => auth()->user()->can('delete'),
                'read' => auth()->user()->can('read'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('DocumentTypes/Create');
    }

    public function store()
    {
        Request::validate([
            'name' => ['required'],
        ]);
        //Generating Prefix from Voucher/Document Type name --START--
        $prefix = Request::input('name');
        $name = str_replace(["(", ")"], "", $prefix);
        $words = preg_split("/[\s,_-]+/", $name);
        $acronym = "";
        $count = 1;
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        //Generating Prefix from Voucher/Document Type name --END--

        DocumentType::create([
            'name' => Request::input('name'),
            'prefix' => $acronym,
            'company_id' => session('company_id'),
        ]);

        return Redirect::route('documenttypes')->with('success', 'Voucher created.');
    }

    // public function show(DocumentType $documenttype)
    // {
    // }

    public function edit(DocumentType $documenttype)
    {
        return Inertia::render('DocumentTypes/Edit', [
            'documenttype' => [
                'id' => $documenttype->id,
                'name' => $documenttype->name,
                'prefix' => $documenttype->prefix,
            ],
        ]);
    }

    public function update(DocumentType $documenttype)
    {
        Request::validate([
            'name' => ['required'],
        ]);

        //Generating Prefix from Voucher/Document Type name --START--
        $prefix = Request::input('name');
        $name = str_replace(["(", ")"], "", $prefix);
        $words = preg_split("/[\s,_-]+/", $name);
        $acronym = "";
        $count = 1;
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        //Generating Prefix from Voucher/Document Type name --END--

        $documenttype->name = Request::input('name');
        $documenttype->prefix = $acronym;
        $documenttype->company_id = session('company_id');
        $documenttype->save();

        return Redirect::route('documenttypes')->with('success', 'Voucher updated.');
    }

    public function destroy(DocumentType $documenttype)
    {
        $documenttype->delete();
        return Redirect::back()->with('success', 'Voucher deleted.');
    }
}
