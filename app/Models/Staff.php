<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    protected $table = 'staff';

    protected $primaryKey = 'staff_no';

    public $incrementing = true;

    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'telephone_no',
        'sex',
        'date_of_birth',
        'nin',
        'job_title',
        'salary',
        'date_joined',
        'branch_no',
        'supervisor_staff_no',
        'date_start',
        'car_allowance',
        'bonus',
        'typing_speed',
        'password',
    ];

    /**
     * Hide password from JSON responses.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Automatically hash password when saving.
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Tell Laravel to use password column for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relationships
     */

    public function branch()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_no', 'branch_no');
    }

    public function supervisor()
    {
        return $this->belongsTo(Staff::class, 'supervisor_staff_no', 'staff_no');
    }

    public function subordinates()
    {
        return $this->hasMany(Staff::class, 'supervisor_staff_no', 'staff_no');
    }

    public function nextOfKin()
    {
        return $this->hasOne(NextOfKin::class, 'staff_no', 'staff_no');
    }

    public function renters()
    {
        return $this->hasMany(Renter::class, 'staff_no', 'staff_no');
    }
}