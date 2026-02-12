<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'key',
        'value',
        'group',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}
