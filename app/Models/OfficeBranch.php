<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeBranch extends Model
{
    use Auditable, HasFactory;

    public $table = 'office_branches';

    protected $fillable = [
        'branch_name',
        'pincode',
        'address_line',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'registration_detail',
        'gst_number',
        'pan_number',
        'legal_entity_name',
        'incharge_name',
        'incharge_phone',
        'incharge_email',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'office_branch_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'office_branch_id');
    }
}
