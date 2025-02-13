<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billable_item';

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
        'billable_category_id',
        'rank',
        'name',
        'description',
        'company_cost',
        'profit_margin',
        'metric_cost',
        'created_by',
        'created_on',
        'is_active',
        'metric_cost_options',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'company_cost' => 'float',
        'profit_margin' => 'float',
        'created_on' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relationships or custom methods.
     */

    // Relationship with the BillableCategory model
    public function category()
    {
        return $this->belongsTo(BillableCategory::class, 'billable_category_id');
    }

    // Relationship with the User model for the creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
