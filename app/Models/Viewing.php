<?php

namespace App\Models;
use App\Models\PropertyForRent;
use App\Models\Renter;

use Illuminate\Database\Eloquent\Model;
 
class Viewing extends Model
{
    protected $table = 'viewing';
    protected $primaryKey = null;
    public $incrementing = false;
    
 
    protected $fillable = ['property_no', 'renter_no', 'viewing_date', 'comments'];
 
    public function property() { return $this->belongsTo(PropertyForRent::class, 'property_no', 'property_no'); }
    public function renter()   { return $this->belongsTo(Renter::class, 'renter_no', 'renter_no'); }
}