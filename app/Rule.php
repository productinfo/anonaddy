<?php

namespace App;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasUuid;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'conditions',
        'actions',
        'operator',
        'active',
        'order'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'active' => 'boolean',
        'conditions' => 'array',
        'actions' => 'array'
    ];

    /**
     * Get the user for the rule.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deactivate()
    {
        $this->update(['active' => false]);
    }

    public function activate()
    {
        $this->update(['active' => true]);
    }
}
