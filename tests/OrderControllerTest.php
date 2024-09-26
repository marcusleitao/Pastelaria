<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use Laravel\Lumen\Testing\DatabaseMigrations;

class OrderControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoreOrderSuccessfully()
    {
        Mail::fake();

        $customer = Customer::factory()->create();
        $products = Product::factory()->count(3)->create();

        $orderData = [
            'customer_id' => $customer->id,
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'quantity' => rand(1, 5),
                ];
            })->toArray(),
        ];

        $response = $this->post('/orders', $orderData);

        $response->seeStatusCode(201);
        $response->seeJsonStructure([
            'id',
            'customer_id',
            'created_at',
            'updated_at',
            'products' => [
                '*' => [
                    'id',
                    'nome',
                    'preco',
                    'foto',
                    'created_at',
                    'updated_at',
                    'pivot' => [
                        'order_id',
                        'product_id',
                        'quantity',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]
        ]);
        
        $response->seeInDatabase('orders', ['customer_id' => $customer->id]);

        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) use ($customer) {
            return $mail->hasTo($customer->email);
        });
    }

    public function testStoreOrderNotFoundProduct()
    {
        $notFoundProductId = 999999;

        $customer = Customer::factory()->create();
        Product::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $notFoundProductId, 'quantity' => 2]
            ]
        ];

        $response = $this->post('/orders', $orderData);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'products.0.id' => ["O produto informado não existe."]
        ]);
    }

    public function testStoreOrderInvalidQuantity()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 0]
            ]
        ];

        $response = $this->post('/orders', $orderData);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'products.0.quantity' => ["O campo quantidade do produto deve ser no mínimo 1."]
        ]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 'asd']
            ]
        ];
        $response = $this->post('/orders', $orderData);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'products.0.quantity' => ["O campo quantidade do produto deve ser um número inteiro."]
        ]);
    }

    public function testIndexOrderSuccessfully()
    {
        $customer = Customer::factory()->create();

        $products = Product::factory()->count(2)->create();

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $products->map(function ($product) use ($order) {
            $order->products()->attach($product->id, ['quantity' => rand(1, 5)]);
        });

        $response = $this->get('/orders');

        $response->seeStatusCode(200);

        $response->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'customer_id',
                    'created_at',
                    'updated_at',
                    'products' => [
                        '*' => [
                            'id',
                            'nome',
                            'preco'
                        ]
                    ]
                ]
            ],
            'links'
        ]);
    }

    public function testShowOrderFound()
    {
        $customer = Customer::factory()->create();

        $products = Product::factory()->count(2)->create();

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $products->map(function ($product) use ($order) {
            $order->products()->attach($product->id, ['quantity' => rand(1, 5)]);
        });

        $response = $this->get("/orders/{$order->id}");

        $response->seeStatusCode(200);

        $response->seeJsonStructure([
            'id',
            'customer_id',
            'created_at',
            'updated_at',
            'products' => [
                '*' => [
                    'id',
                    'nome',
                    'preco'
                ]
            ]
        ]);
    }

    public function testShowOrderNotFound()
    {
        $response = $this->get('/orders/999999');

        $response->seeStatusCode(404);

        $response->seeJsonEquals([
            'error' => 'Pedido não encontrado'
        ]);
    }

    public function testUpdateOrder()
    {
        $customer = Customer::factory()->create();

        $products = Product::factory()->count(3)->create();

        $order = Order::create([
            'customer_id' => $customer->id,
        ]);

        $products->map(function ($product) use ($order) {
            $order->products()->attach($product->id, ['quantity' => rand(1, 5)]);
        });

        $newProducts = Product::factory()->count(2)->create();

        $updateData = [
            'customer_id' => $customer->id,
            'products' => $newProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'quantity' => rand(1, 5)
                ];
            })->toArray()
        ];

        $response = $this->put("/orders/{$order->id}", $updateData);

        $response->seeStatusCode(200);

        $response->seeJsonStructure([
            'id',
            'customer_id',
            'created_at',
            'updated_at',
            'products' => [
                '*' => [
                    'id',
                    'nome',
                    'preco',
                    'pivot' => [
                        'quantity'
                    ]
                ]
            ]
        ]);
    }

    public function testDestroyOrder()
    {
        $customer = Customer::factory()->create();

        $products = Product::factory()->count(2)->create();

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $products->map(function ($product) use ($order) {
            $order->products()->attach($product->id, ['quantity' => rand(1, 5)]);
        });

        $response = $this->delete("/orders/{$order->id}");

        $response->seeStatusCode(200);

        $response->seeJsonEquals([
            'message' => 'Pedido removido com sucesso.'
        ]);

        $this->SeeInDatabase('orders', ['id' => $order->id, 'deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function testDestroyOrderNotFound()
    {
        $response = $this->delete('/orders/999999');

        $response->seeStatusCode(404);

        $response->seeJsonEquals([
            'error' => 'Pedido não encontrado'
        ]);
    }
}