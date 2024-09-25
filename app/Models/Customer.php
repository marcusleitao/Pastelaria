<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Order;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'email', 'nascimento', 'endereco',
        'complemento', 'bairro', 'cep'
    ];

    protected $table = 'customers';

    protected $dates = ['deleted_at'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
