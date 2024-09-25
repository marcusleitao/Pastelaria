<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $this->validateProduct($request);

        $product = $product->create([
            'nome' => $request->nome,
            'preco' => $request->preco,
            'foto' => $request->foto
        ]);

        return response()->json($product, 201);
    }

    private function validateProduct(Request $request)
    {
        $rules = [
            'nome' => 'required|string',
            'preco' => 'required|numeric',
            'foto' => 'required|string'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'preco.required' => 'O campo preço é obrigatório.',
            'preco.numeric' => 'O campo preço deve ser um número.',
            'foto.required' => 'O campo foto é obrigatório.'
        ];

        $this->validate($request, $rules, $messages);
    }
}
