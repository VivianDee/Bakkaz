<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'app_name',
        'percentage',
        'amount'
    ];

     /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "created_at",
        "updated_at",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

    ];


    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
