<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billable extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'billable';

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
        'estimate_id',
        'billable_item_id',
        'quantity',
        'cost',
        'price',
        'metric_cost',
        'created_by',
        'created_on',
        'is_active',
        'is_deleted',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'float',
        'cost' => 'float',
        'price' => 'float',
        'created_on' => 'datetime',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Relationships or custom methods.
     */

    // Relationship with the Estimate model
    public function estimate()
    {
        return $this->belongsTo(Estimate::class, 'estimate_id');
    }

    // Relationship with the BillableItem model
    public function item()
    {
        return $this->belongsTo(BillableItem::class, 'billable_item_id');
    }
    

    // Relationship with the User model for the creator
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
