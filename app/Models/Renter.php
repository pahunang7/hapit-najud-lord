<?php

namespace App\Models;
use App\Models\Viewing;
use App\Models\LeaseAgreement; 
use Illuminate\Database\Eloquent\Model;
 
class Renter extends Model
{
    protected $table = 'renter';
    protected $primaryKey = 'renter_no';
    public $incrementing = false;
    protected $keyType = 'string';
 
    protected $fillable = [
        'renter_no', 'first_name', 'last_name', 'address',
        'telephone_no', 'preferred_type', 'preferred_location',
        'max_rent', 'staff_no', 'branch_no',
    ];
 
    public function viewings() { return $this->hasMany(Viewing::class, 'renter_no', 'renter_no'); }
    public function leases()   { return $this->hasMany(LeaseAgreement::class, 'renter_no', 'renter_no'); }
}