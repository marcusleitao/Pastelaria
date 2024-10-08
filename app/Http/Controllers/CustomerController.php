<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $this->validateCustomer($request);

        // Verifica se o e-mail já está cadastrado
        $existsEmail = $customer->where('email', $request->email)->whereNull('deleted_at')->exists();

        if ($existsEmail) {
            return response()->json(['error' => 'E-mail já cadastrado.'], 400);
        }

        $customer = $customer->create([
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

    public function index(Request $request, Customer $customer)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'asc');
        $perPage = $request->query('per_page', 15);

        $validSortColumns = ['created_at', 'name', 'email'];
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

        $query = $customer->query();
        $query->orderBy($sortBy, $sortDirection);

        $customers = $query->paginate($perPage);

        return response()->json($customers, 200);
    }

    public function show($id, Customer $customer)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $customer = $customer->find($id);

        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado.'], 404);
        }

        return response()->json($customer, 200);
    }

    public function update(Request $request, $id, Customer $customer)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $customer = $customer->find($id);

        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado.'], 404);
        }

        // Verifica se o e-mail já está cadastrado para outro cliente
        $existsEmail = $customer->where('email', $request->email)->where('id', '!=', $id)->exists();

        if ($existsEmail) {
            return response()->json(['error' => 'E-mail já cadastrado.'], 400);
        }

        $this->validateCustomer($request, true);

        $customer->update([
            'nome' => $request->nome,
            'email' => $request->email,
            'nascimento' => $request->nascimento,
            'endereco' => $request->endereco,
            'complemento' => $request->complemento,
            'bairro' => $request->bairro,
            'cep' => $request->cep
        ]);

        return response()->json($customer, 200);
    }

    public function destroy($id, Customer $customer)
    {
        if (!is_numeric($id) || $id <= 0) {
            return response()->json(['error' => 'ID inválido.'], 400);
        }

        $customer = $customer->find($id);

        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado.'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Cliente deletado com sucesso.'], 200);
    }

    private function validateCustomer(Request $request)
    {
        /*
         * como estamos utilizando a abordagem softdeleting, 
         * não é possível utilizar o unique, 
         * pois na criação de um novo registro, 
         * o eloquent não leva em conta o campo deleted_at
         */

         $rules = [
            'nome' => 'required',
            'email' => 'required|email',
            'nascimento' => 'required|date',
            'endereco' => 'required',
            'complemento' => 'required',
            'bairro' => 'required',
            'cep' => 'required'
        ];

        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'O campo e-mail deve ser um e-mail válido.',
            'nascimento.required' => 'O campo nascimento é obrigatório.',
            'nascimento.date' => 'O campo nascimento deve ser uma data válida.',
            'endereco.required' => 'O campo endereço é obrigatório.',
            'complemento.required' => 'O campo complemento é obrigatório.',
            'bairro.required' => 'O campo bairro é obrigatório.',
            'cep.required' => 'O campo CEP é obrigatório.'
        ];

        $this->validate($request, $rules, $messages);
    }
}
