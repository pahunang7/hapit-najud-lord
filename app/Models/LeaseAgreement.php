<?php

namespace App\Models;

use App\Models\PropertyForRent;
use App\Models\Renter;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
 
class LeaseAgreement extends Model
{
    protected $table = 'lease_agreement';
    protected $primaryKey = 'lease_no';
    public $incrementing = false;
 
    protected $fillable = [
        'lease_no', 'start_date', 'end_date', 'duration',
        'deposit', 'deposit_paid', 'payment_method',
        'property_no', 'renter_no', 'staff_no',
    ];
 
    public function property() { return $this->belongsTo(PropertyForRent::class, 'property_no', 'property_no'); }
    public function renter()   { return $this->belongsTo(Renter::class, 'renter_no', 'renter_no'); }
    public function staff()    { return $this->belongsTo(Staff::class, 'staff_no', 'staff_no'); }
}