<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class Owner extends Model
{
    protected $table = 'owner';
    protected $primaryKey = 'owner_no';
    public $incrementing = false;
    protected $fillable = ['owner_no','full_name','address','telephone_no'];
}
 