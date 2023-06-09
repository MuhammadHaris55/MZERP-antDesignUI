<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','enabled'
    ];

    public function accountGroups()
    {
        return $this->hasMany('App\Models\AccountGroup','type_id');
    }

    //to get revenue for dashboard according to months
    public function entries()
    {
        return $this->hasManyThrough(Entry::class, Account::class, 'type_id', 'account_id');
    }
}
