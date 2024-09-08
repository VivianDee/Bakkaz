<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',

        // Defaults to NGN
        'currency',
        'status',
        'service_ref',
        'payment_reference',
        'verified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        "deleted_at"

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

    
    public function paymentUrl()
    {
        return $this->hasMany(PaymentUrl::class);
    }

    public function authorization()
    {
        return $this->hasOne(Authorization::class);
    }

    public function refund() {
        return $this->hasMany(Refund::class);
    }

    public function splits()
    {
        return $this->hasMany(PaymentSplit::class);
    }

}