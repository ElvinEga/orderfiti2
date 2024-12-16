<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Balance extends Model
{
    use HasFactory;
    protected $table = "balance";
    protected $fillable = ['user_id', 'branch_id', 'balance','order_id'];
    protected $casts = [
        'id'        => 'integer',
        'user_id'      => 'integer',
        'branch_id' => 'integer',
        'balance' => 'decimal:6',
        'order_id' => 'integer',
    ];

//    protected static function boot(): void
//    {
//        parent::boot();
//        static::addGlobalScope(new BranchScope());
//    }


//    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
//    {
//        return $this->belongsTo(Branch::class);
//    }

}
