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

    public function index(Request $request, Product $product)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'asc');
        $perPage = $request->query('per_page', 15);

        $validSortColumns = ['created_at', 'name', 'price'];
        $validSortDirections = ['asc', 'desc'];

        if (!in_array($sortBy, $validSortColumns)) {
            return response()->json(['error' => 'Coluna de ordenação inválida.'], 400);
        }

        if (!in_array($sortDirection, $validSortDirections)) {
            return response()->json(['error' => 'Direção de ordenação inválida.'], 400);
        }

        if (!is_numeric($perPage) || $perPage <= 0) {
            return response()->json(['error' => 'Número de itens por página inválido.'], 400);
        }

        $query = $product->query();
        $query->orderBy($sortBy, $sortDirection);

        $products = $query->paginate($perPage);

        return response()->json($products, 200);
    }

    public function show($id, Product $product)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $product = $product->find($id);

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado.'], 404);
        }

        return response()->json($product, 200);
    }

    public function update(Request $request, $id, Product $product)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $product = $product->find($id);

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado.'], 404);
        }

        $this->validateProduct($request);

        $product->update([
            'nome' => $request->nome,
            'preco' => $request->preco,
            'foto' => $request->foto
        ]);

        return response()->json($product, 200);
    }

    public function destroy($id, Product $product)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $product = $product->find($id);

        if (!$product) {
            return response()->json(['error' => 'Produto não encontrado.'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Produto removido com sucesso.'], 200);
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
