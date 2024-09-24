<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome', 'email', 'nascimento', 'endereco',
        'complemento', 'bairro', 'cep'
    ];

    protected $table = 'customers';
}
