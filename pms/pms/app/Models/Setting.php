<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'value', 'type', 'group', 'options',
        'label', 'description', 'order', 'is_public'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean'
    ];

    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? $value : 0;
            case 'json':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }

    public function setValueAttribute($value)
    {
        if ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
