<?php


namespace App\Builders;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class OBatchBuilder extends Builder
{
    public function active(): OBatchBuilder
    {
        return $this->whereActiveStatus(1);
    }

    public function orderAscending(): OBatchBuilder
    {
        return $this->orderBy('ID', 'asc');
    }

    public function notNullLastDateOfAdm(): OBatchBuilder
    {
        return $this->whereNotNull('last_date_of_adm');
    }

    public function lastDateOfAdmExpired(): OBatchBuilder
    {
        return $this->where('last_date_of_adm', '>=', Carbon::now()->format('Y-m-d h:m:s'));
    }
}
