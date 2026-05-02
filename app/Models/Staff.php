<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_no';
    public $incrementing = false;
    protected $fillable = [
        'staff_no','first_name','last_name','address','telephone_no',
        'sex','date_of_birth','NIN','position','salary','date_joined',
        'branch_no','supervisor_staff_no'
    ];
}
 