<?php

namespace Modules\Campaign\Entities;

use App\Models\BaseModel;
use App\Models\Category;

class CampaignCategory extends BaseModel
{
    protected $fillable = ['campaign_id', 'category_ids', 'created_by', 'modified_by'];

    protected $casts = [
        'product_ids' => 'array', // Automatically cast JSON to array
    ];

    public function getCategoryIdsAttribute($value)
    {
        return $value ? array_map('intval', json_decode($value, true)) : [];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function categories()
    {
        return Category::whereIn('id', $this->category_ids);
    }

    private function get_datatable_query()
    {
        $query = self::with('campaign')->groupBy('campaign_id');

        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        return $this->get_datatable_query()->count();
    }

    public function count_all()
    {
        return self::count();
    }
}
