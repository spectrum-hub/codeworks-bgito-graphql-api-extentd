<?php

namespace Webkul\GraphQLAPI\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CidsCustomer extends Model
{
    use HasFactory;

    protected $table = 'cids_customer';

    protected $fillable = [
        'user_id',
        'cid_value',
        'status',
        'log'
    ];
}





