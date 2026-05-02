<?php

namespace App\Models;
 
use App\Models\BranchOffice;
use App\Models\Owner;
use App\Models\Staff;
use App\Models\Viewing;
use App\Models\LeaseAgreement;
use Illuminate\Database\Eloquent\Model;
 
class PropertyForRent extends Model
{
    protected $table = 'property_for_rent';
    protected $primaryKey = 'property_no';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'property_no', 'street', 'area', 'city', 'postcode',
        'property_type', 'no_of_rooms', 'monthly_rent',
        'rental_status', 'owner_no', 'branch_no', 'staff_no',
    ];
 
    public function owner()      { return $this->belongsTo(Owner::class, 'owner_no', 'owner_no'); }
    public function branch()     { return $this->belongsTo(BranchOffice::class, 'branch_no', 'branch_no'); }
    public function staff()      { return $this->belongsTo(Staff::class, 'staff_no', 'staff_no'); }
    public function viewings()   { return $this->hasMany(Viewing::class, 'property_no', 'property_no'); }
    public function leases()     { return $this->hasMany(LeaseAgreement::class, 'property_no', 'property_no'); }
}

