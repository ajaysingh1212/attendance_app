<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'orders';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'select_customer_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_id',
        'order_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function select_products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function select_customer()
    {
        return $this->belongsTo(MakeCustomer::class, 'select_customer_id');
    }
    public function products()
{
    return $this->belongsToMany(Product::class, 'order_product')
        ->withPivot(['quantity', 'price', 'discount', 'discount_type', 'total'])
        ->withTimestamps();
}
   public function created_by()
{
    return $this->belongsTo(User::class, 'created_by_id');
}


}