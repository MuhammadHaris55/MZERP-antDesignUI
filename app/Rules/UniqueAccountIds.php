<?php

namespace App\Rules;


use Illuminate\Contracts\Validation\Rule;

class UniqueAccountIds implements Rule
{
    public function passes($attribute, $value)
    {
        $accountIds = array_column($value, 'account_id');
        return count($accountIds) === count(array_unique($accountIds));
    }

    public function message()
    {
        return 'Duplicate accounts are not allowed.';
    }
}
