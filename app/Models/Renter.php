<?php
// FILE: app/Models/Renter.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Renter extends Model
{
    protected $table = 'renter';
    protected $primaryKey = 'renter_no';
    public $timestamps = false;

    protected $fillable = [
        'first_name', 'last_name', 'address', 'telephone_no',
        'preferred_type', 'preferred_location', 'max_rent',
        'staff_no', 'branch_no'
    ];

    public function branch()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_no', 'branch_no');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no', 'staff_no');
    }

    public function assignments()
    {
        return $this->hasMany(RenterStaffAssignment::class, 'renter_no', 'renter_no');
    }

    public function activityLogs()
    {
        return $this->hasMany(RenterActivityLog::class, 'renter_no', 'renter_no');
    }
}
