<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table      = 'staff';
    protected $primaryKey = 'staff_no';
    public    $incrementing = true;   // ← SERIAL in DB, must be true
    protected $keyType    = 'int';

    protected $fillable = [
        'first_name', 'last_name', 'address', 'telephone_no',
        'sex', 'date_of_birth', 'nin', 'job_title',
        'salary', 'date_joined', 'branch_no', 'supervisor_staff_no',
        'date_start', 'car_allowance', 'bonus', 'typing_speed',
    ];

    // ── Relationships ──────────────────────────────────────

    public function branch()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_no', 'branch_no');
    }

    // The supervisor this staff reports to
    public function supervisor()
    {
        return $this->belongsTo(Staff::class, 'supervisor_staff_no', 'staff_no');
    }

    // Staff members this person supervises
    public function subordinates()
    {
        return $this->hasMany(Staff::class, 'supervisor_staff_no', 'staff_no');
    }

    // One next-of-kin per staff member
    public function nextOfKin()
    {
        return $this->hasOne(NextOfKin::class, 'staff_no', 'staff_no');
    }

    public function renters()
    {
        return $this->hasMany(Renter::class, 'staff_no', 'staff_no');
    }
}