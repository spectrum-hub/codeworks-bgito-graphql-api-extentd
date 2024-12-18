<?php

namespace Webkul\GraphQLAPI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CidsCartShifts extends Model
{
    use HasFactory;

    protected $table = 'cids_cart_shifts';

    protected $fillable = [
        'user_id',
        'cid_value',
        'cart_id',
        'cid_id',
        'status',
        'log'
    ];
}