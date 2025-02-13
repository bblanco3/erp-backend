<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'estimates';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_estimate_id',
        'project_id',
        'scope_id',
        'charge_type',
        'final_price',
        'suggested_price',
        'contract_date',
        'notes_public',
        'notes_private',
        'created_by',
        'created_on',
        'approved_by',
        'approved_on',
        'accepted_by',
        'accepted_on',
        'is_active',
        'is_deleted',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'suggested_price' => 'float',
        'created_on' => 'datetime',
        'approved_on' => 'datetime',
        'accepted_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Relationships or custom methods can go here.
     */
    public function parent()
    {
        // If parent_estimate_id is null, then this is the parent estimate
        if (is_null($this->parent_estimate_id)) {
            return null;
        }

        // If parent_estimate_id is not null, then this is a child estimate
        return $this->belongsTo(Estimate::class, 'parent_estimate_id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function scope()
    {
        return $this->belongsTo(Scope::class, 'scope_id');
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function approved_by()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function accepted_by()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
