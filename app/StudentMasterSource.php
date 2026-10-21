<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentMasterSource extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'source_data' => 'array'];
}
