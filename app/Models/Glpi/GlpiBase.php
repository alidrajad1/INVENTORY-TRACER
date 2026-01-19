<?php

namespace App\Models\Glpi;

use Illuminate\Database\Eloquent\Model;

class GlpiBase extends Model
{
    protected $connection = 'glpi';
    public $timestamps = false;
    protected $guarded = [];
}
