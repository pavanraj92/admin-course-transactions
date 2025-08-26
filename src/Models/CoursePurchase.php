<?php

namespace admin\course_transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Kyslik\ColumnSortable\Sortable;

class CoursePurchase extends Model
{
    use HasFactory, Sortable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $sortable = [
        'user',
        'course.title',
        'amount',
        'status',
        'created_at',
    ];

    public function userSortable($query, $direction)
    {
        return $query
            ->leftJoin('users', 'course_purchases.user_id', '=', 'users.id')
            ->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) {$direction}")
            ->select('course_purchases.*');
    }

    public function courseSortable($query, $direction)
    {
        return $query->join('courses', 'course_purchases.course_id', '=', 'courses.id')
            ->orderBy('courses.title', $direction)
            ->select('course_purchases.*');
    }

    // public function transactionSortable($query, $direction)
    // {
    //     return $query->join('transactions', 'course_purchases.transaction_id', '=', 'transactions.id')
    //         ->orderBy('transactions.title', $direction)
    //         ->select('course_purchases.*');
    // }

    public function user()
    {
        if (class_exists(\admin\users\Models\User::class)) {
            return $this->belongsTo(\admin\users\Models\User::class);
        }
    }

    public function course()
    {
        if (class_exists(\admin\courses\Models\Course::class)) {
            return $this->belongsTo(\admin\courses\Models\Course::class);
        }
    }

    public function transaction()
    {
        if (class_exists(\admin\course_transactions\Models\Transaction::class)) {
            return $this->belongsTo(\admin\course_transactions\Models\Transaction::class);
        }
    }

    // Filtering logic (like we discussed for transactions)
    public function scopeFilter($query, $filters)
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('user', function ($userQ) use ($filters) {
                    $userQ->where('name', 'like', "%{$filters['keyword']}%")
                        ->orWhere('email', 'like', "%{$filters['keyword']}%");
                })->orWhereHas('course', function ($courseQ) use ($filters) {
                    $courseQ->where('title', 'like', "%{$filters['keyword']}%");
                });
            });
        }

        return $query;
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
