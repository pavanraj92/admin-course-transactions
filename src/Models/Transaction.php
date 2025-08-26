<?php

namespace admin\course_transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use Sortable, SoftDeletes;
    protected $fillable = [
        'user_id',
        'payment_gateway',
        'transaction_reference',
        'amount',
        'currency',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public $sortable = ['user', 'transaction_reference', 'amount', 'status', 'created_at'];

    const STATUSES = ['pending', 'success', 'failed'];

    public function userSortable($query, $direction)
    {
        return $query
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) {$direction}")
            ->select('transactions.*');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($filters['keyword'] ?? null, function ($q, $keyword) {
            $q->where(function ($sub) use ($keyword) {
                $sub->where('transaction_reference', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        });
    }

    public function user()
    {
        if (class_exists(\admin\users\Models\User::class)) {
            return $this->belongsTo(\admin\users\Models\User::class, 'user_id');
        }
    }

    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
