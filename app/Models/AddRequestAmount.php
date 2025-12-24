<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddRequestAmount extends Model
{
    use SoftDeletes, Auditable, HasFactory;

    public $table = 'add_request_amounts';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const STATUS_SELECT = [
        'pending' => 'Pending',
        'accept'  => 'Accept',
        'reject'  => 'Reject',
    ];

    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'status',
        'remark',
        'created_at',
        'updated_at',
        'deleted_at',
        'employee_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
{
    return $this->belongsTo(Employee::class, 'employee_id');
}

}
