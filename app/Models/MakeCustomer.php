<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MakeCustomer extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasFactory;

    public $table = 'make_customers';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'shop_image',
        'id_proof',
        'gst_certificate',
    ];

    public const STATUS_SELECT = [
        'Enable'  => 'Enable',
        'Disable' => 'Disable',
    ];

    public const PREFERRED_PAYMENT_METHOD_SELECT = [
        'Cash'   => 'Cash',
        'UPI'    => 'UPI',
        'Bank'   => 'Bank',
        'Cheque' => 'Cheque',
    ];

    public const BUSINESS_TYPE_SELECT = [
        'Retailer'    => 'Retailer',
        'Distributor' => 'Distributor',
        'Dealer'      => 'Dealer',
        'Wholesaler'  => 'Wholesaler',
        'Franchise'   => 'Franchise',
    ];

    protected $fillable = [
        'customer_code',
        'shop_name',
        'owner_name',
        'phone_number',
        'email',
        'pincode',
        'address_line_1',
        'address_line_2',
        'area',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'business_type',
        'shop_category_id',
        'gst_number',
        'license_no',
        'payment_terms',
        'preferred_payment_method',
        'bank_name',
        'ifsc_code',
        'account_no',
        'notes',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by_id',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit('crop', 50, 50);
        $this->addMediaConversion('preview')->fit('crop', 120, 120);
    }

    public function shop_category()
    {
        return $this->belongsTo(ProductCategory::class, 'shop_category_id');
    }

    public function getShopImageAttribute()
    {
        $file = $this->getMedia('shop_image')->last();
        if ($file) {
            $file->url       = $file->getUrl();
            $file->thumbnail = $file->getUrl('thumb');
            $file->preview   = $file->getUrl('preview');
        }

        return $file;
    }

    public function getIdProofAttribute()
    {
        return $this->getMedia('id_proof')->last();
    }

    public function getGstCertificateAttribute()
    {
        return $this->getMedia('gst_certificate')->last();
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}