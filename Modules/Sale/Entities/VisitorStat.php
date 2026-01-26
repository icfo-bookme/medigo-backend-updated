<?php

namespace Modules\Sale\Entities;

use Illuminate\Database\Eloquent\Model;

class VisitorStat extends Model
{

        protected $fillable = [
        'ip_address',
        'user_agent',
        'visited_at',
        'visited_page'];

}
