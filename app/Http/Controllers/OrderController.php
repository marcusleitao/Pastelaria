<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Mail\OrderPlaced;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $this->validateOrder($request);

        DB::beginTransaction();

        try {
            $order = Order::create([
                'customer_id' => $request->customer_id
            ]);

            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);

                if (!$product) {
                    throw new \Exception("Produto com ID {$productData['id']} não encontrado.");
                }

                if (!isset($productData['quantity']) || $productData['quantity'] === null) {
                    throw new \Exception("Quantidade não fornecida para o produto com ID {$productData['id']}.");
                }

                $order->products()->attach($product['id'], ['quantity' => $productData['quantity']]);
            }

            DB::commit();
            
            $order->load('products');

            $customer = Customer::find($request->customer_id);

            Mail::to($customer->email)->send(new OrderPlaced($order));
            
            return response()->json($order, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function index()
    {
        $orders = Order::with('products:id,nome,preco')
            ->select('id', 'customer_id', 'created_at', 'updated_at')
            ->paginate(10);

        return response()->json($orders);
    }

    public function show($id)
    {
        $order = Order::with('products')->find($id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $this->validateOrder($request);

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            $order->update([
                'customer_id' => $request->customer_id
            ]);

            $order->products()->detach();

            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);

                if (!$product) {
                    throw new \Exception("Produto com ID {$productData['id']} não encontrado.");
                }

                if (!isset($productData['quantity']) || $productData['quantity'] === null) {
                    throw new \Exception("Quantidade não fornecida para o produto com ID {$productData['id']}.");
                }

                $order->products()->attach($product['id'], ['quantity' => $productData['quantity']]);
            }

            DB::commit();
            
            $order->load('products');
            
            return response()->json($order, 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Pedido removido com sucesso.'], 200);
    }

    private function validateOrder(Request $request) 
    {
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];

        $messages = [
            'customer_id.required' => 'O campo customer_id é obrigatório.',
            'customer_id.exists' => 'O customer_id informado não existe.',
            'products.required' => 'O campo products é obrigatório.',
            'products.array' => 'O campo products deve ser um array.',
            'products.*.id.required' => 'O campo id do produto é obrigatório.',
            'products.*.id.exists' => 'O produto informado não existe.',
            'products.*.quantity.required' => 'O campo quantidade do produto é obrigatório.',
            'products.*.quantity.integer' => 'O campo quantidade do produto deve ser um número inteiro.',
            'products.*.quantity.min' => 'O campo quantidade do produto deve ser no mínimo 1.',
        ];

        $this->validate($request, $rules, $messages);
    }
}