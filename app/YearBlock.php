<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YearBlock extends Model
{
    protected $table = 'year_blocks';
    public $timestamps = false;
    protected $fillable = ['label'];
}
