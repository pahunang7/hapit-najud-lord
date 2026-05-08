<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BranchOffice extends Model
{
    protected $table = 'branch_office';
    protected $primaryKey = 'branch_no';
    public $incrementing = false;
    protected $fillable = ['branch_no','street','area','city','postcode','telephone_no','fax_no'];


      public function staff()
    {
        return $this->hasMany(Staff::class, 'branch_no', 'branch_no');
    }

    public function renters()
    {
        return $this->hasMany(Renter::class, 'branch_no', 'branch_no');
    }
}