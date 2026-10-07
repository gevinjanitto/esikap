<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Satuan extends Model
{
    use Auditable, SoftDeletes;

    protected $guarded = ['id'];

}
