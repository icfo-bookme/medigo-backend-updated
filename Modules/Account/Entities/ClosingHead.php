<?php

namespace Modules\Account\Entities;

use Illuminate\Database\Eloquent\Model;

class ClosingHead extends Model
{
    protected $fillable = ['label_name', 'created_by', 'modified_by'];
    protected $table = 'closing_heads';
}
