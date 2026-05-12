<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextOfKin extends Model
{
    protected $table      = 'next_of_kin';
    protected $primaryKey = 'staff_no';
    public    $incrementing = false;  // FK, not a serial
    protected $keyType    = 'int';
    public    $timestamps = false;

    protected $fillable = [
        'staff_no', 'full_name', 'relationship', 'address', 'telephone_no',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_no', 'staff_no');
    }
}