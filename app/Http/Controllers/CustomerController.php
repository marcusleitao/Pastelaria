<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nome' => 'required',
            'email' => 'required|email',
            'nascimento' => 'required|date',
            'endereco' => 'required',
            'complemento' => 'required',
            'bairro' => 'required',
            'cep' => 'required'
        ]);

        $existingCustomer = $this->customer->where('email', $request->email)->first();
        if ($existingCustomer) {
            return response()->json([
                'error' => 'Email já está registrado.'
            ], 400);
        }

        $customer = $this->customer->create([
            'nome' => $request->nome,
            'email' => $request->email,
            'nascimento' => $request->nascimento,
            'endereco' => $request->endereco,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
            'cep' => $request->cep
        ]);

        return response()->json($customer, 201);
    }
}
