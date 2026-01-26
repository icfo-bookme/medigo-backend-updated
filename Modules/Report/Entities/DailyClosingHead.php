<?php

namespace Modules\Report\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Account\Entities\ClosingHead;

class DailyClosingHead extends Model
{
    protected $fillable = ['daily_closing_id', 'closing_head_id', 'amount'];
    protected $table = 'daily_closing_heads';

    public function dailyClosing()
    {
        return $this->belongsTo(DailyClosing::class, 'daily_closing_id', 'id');
    }

    public function closingHead()
    {
        return $this->belongsTo(ClosingHead::class, 'closing_head_id', 'id');
    }
}
