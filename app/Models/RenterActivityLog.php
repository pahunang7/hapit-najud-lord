<?php
// FILE: app/Models/RenterActivityLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RenterActivityLog extends Model
{
    protected $table = 'renter_activity_log';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = ['renter_no', 'action', 'details'];

    public function renter()
    {
        return $this->belongsTo(Renter::class, 'renter_no', 'renter_no');
    }
}
