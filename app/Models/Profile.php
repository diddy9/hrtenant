<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'user_id', 'emp_id', 'avatar', 'gender', 'start_date', 'nhf_no', 'pfa_id', 'rsa_pin_no',
        'grade', 'r_address', 'p_address', 'title', 'phone', 'd_o_b', 'p_o_b', 'nationality', 'state_of_origin',
        'home_town', 'local_govt', 'marital_status', 'religion', 'name_of_spouse', 'maiden_name', 'spouse_phone',
        'address_of_spouse', 'next_of_kin_ben', 'relationship_ben', 'address_ben', 'tel_ben', 'next_of_kin_em',
        'relationship_em', 'address_em', 'tel_em', 'disability', 'height', 'weight', 'blood_group', 'genotype',
        'hobbies', 'languages', 'indebted', 'debt_details', 'intention', 'convict', 'crime_details', 'bank_name',
        'account_no', 'sort_code', 'salary_basis', 'salary', 'payment_type', 'cv', 'contract_letter'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope); 
    }
}
