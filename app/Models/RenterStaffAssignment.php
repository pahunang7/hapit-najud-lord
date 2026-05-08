<?php
// FILE: app/Models/RenterStaffAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenterStaffAssignment extends Model
{
    protected $table = 'renter_staff_assignment';
    protected $primaryKey = 'assignment_id';
    public $timestamps = false;

    protected $fillable = ['renter_no', 'staff_no', 'assigned_by'];

    public function renter()
    {
        return $this->belongsTo(Renter::class, 'renter_no', 'renter_no');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no', 'staff_no');
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// FILE: app/Models/RenterActivityLog.php
// (Create this as a separate file in app/Models/)
// ─────────────────────────────────────────────────────────────────────────────
// namespace App\Models;
//
// use Illuminate\Database\Eloquent\Model;
//
// class RenterActivityLog extends Model
// {
//     protected $table = 'renter_activity_log';
//     protected $primaryKey = 'log_id';
//     public $timestamps = false;
//     protected $fillable = ['renter_no', 'action', 'details'];
// }
