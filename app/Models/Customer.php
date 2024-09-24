<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'nome', 'email', 'nascimento', 'endereco',
        'complemento', 'bairro', 'cep'
    ];

    protected $table = 'customers';
}
