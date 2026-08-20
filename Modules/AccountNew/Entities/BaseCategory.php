<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseCategory extends Model
{
    protected $table = 'base_category';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'soft_delete',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    /*
    |----------------------------------------
    | Relationships
    |----------------------------------------
    */

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /*
    |----------------------------------------
    | Accessors (for DataTable)
    |----------------------------------------
    */

    // show created_by name instead of id
    public function getCreatedByNameAttribute()
    {
        return $this->createdBy ? $this->createdBy->name : '';
    }

    public function getUpdatedByNameAttribute()
    {
        return $this->updatedBy ? $this->updatedBy->name : '';
    }
}