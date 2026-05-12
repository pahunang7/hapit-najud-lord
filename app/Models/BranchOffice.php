<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchOffice extends Model
{
    protected $table      = 'branch_office';
    protected $primaryKey = 'branch_no';
    public    $timestamps = false;
    public    $incrementing = true;
    protected $keyType    = 'int';

    protected $fillable = [
        'street',
        'area',
        'city',
        'postcode',
        'telephone_no',
        'fax_no',
    ];

    // Branch has many staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'branch_no', 'branch_no');
    }

    // Branch manager (only one allowed — enforced by DB trigger)
    public function manager()
    {
        return $this->hasOne(Staff::class, 'branch_no', 'branch_no')
                    ->where('job_title', 'Manager');
    }

    // Accessor: formatted branch number e.g. B001
    public function getFormattedBranchNoAttribute()
    {
        return 'B' . str_pad($this->branch_no, 3, '0', STR_PAD_LEFT);
    }
}