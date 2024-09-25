<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class OrderControllerTest extends BaseTestCase
{
    use DatabaseMigrations;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function testStoreOrderSuccessfully()
    {
        $customer = Customer::create([
            'nome' => 'Nome Antigo',
            'email' => 'mavibole@gmail.com', //email valido
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Antiga, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Antigo Bairro',
            'cep' => '12345-678'
        ]);

        $product1 = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $product2 = Product::create([
            'nome' => 'Produto 2',
            'preco' => 20.5,
            'foto' => 'foto2.jpg'
        ]);

        $product3 = Product::create([
            'nome' => 'Produto 3',
            'preco' => 30.5,
            'foto' => 'foto3.jpg'
        ]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product1->id, 'quantity' => 2],
                ['id' => $product2->id, 'quantity' => 3],
                ['id' => $product3->id, 'quantity' => 1],
            ]
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
    }

    public function testStoreOrderNotFoundProduct()
    {
        $customer = Customer::create([
            'nome' => 'Nome Antigo',
            'email' => 'mavibole@gmail.com', //email valido
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Antiga, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Antigo Bairro',
            'cep' => '12345-678'
        ]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => 99, 'quantity' => 2]
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
        $customer = Customer::create([
            'nome' => 'Nome Antigo',
            'email' => 'mavibole@gmail.com', //email valido
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Antiga, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Antigo Bairro',
            'cep' => '12345-678'
        ]);

        
        $product = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $orderData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product->id]
            ]
        ];

        $response = $this->post('/orders', $orderData);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'products.0.quantity' => ["O campo quantidade do produto é obrigatório."]
        ]);
    }

    public function testIndexOrderSuccessfully()
    {
        $customer = Customer::create([
            'nome' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Bairro Teste',
            'cep' => '12345-678'
        ]);

        $product1 = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $product2 = Product::create([
            'nome' => 'Produto 2',
            'preco' => 20.5,
            'foto' => 'foto2.jpg'
        ]);

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $order->products()->attach($product1->id, ['quantity' => 2]);
        $order->products()->attach($product2->id, ['quantity' => 3]);

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
        $customer = Customer::create([
            'nome' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Bairro Teste',
            'cep' => '12345-678'
        ]);

        $product1 = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $product2 = Product::create([
            'nome' => 'Produto 2',
            'preco' => 20.5,
            'foto' => 'foto2.jpg'
        ]);

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $order->products()->attach($product1->id, ['quantity' => 2]);
        $order->products()->attach($product2->id, ['quantity' => 3]);

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
        $customer = Customer::create([
            'nome' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Bairro Teste',
            'cep' => '12345-678'
        ]);

        $product1 = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $product2 = Product::create([
            'nome' => 'Produto 2',
            'preco' => 20.5,
            'foto' => 'foto2.jpg'
        ]);

        $product3 = Product::create([
            'nome' => 'Produto 3',
            'preco' => 30.5,
            'foto' => 'foto3.jpg'
        ]);

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $order->products()->attach($product1->id, ['quantity' => 2]);
        $order->products()->attach($product2->id, ['quantity' => 3]);

        $updateData = [
            'customer_id' => $customer->id,
            'products' => [
                ['id' => $product1->id, 'quantity' => 1],
                ['id' => $product3->id, 'quantity' => 2],
            ]
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
        $customer = Customer::create([
            'nome' => 'Cliente Teste',
            'email' => 'cliente@teste.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Teste, 123',
            'complemento' => 'Apto 123',
            'bairro' => 'Bairro Teste',
            'cep' => '12345-678'
        ]);

        $product1 = Product::create([
            'nome' => 'Produto 1',
            'preco' => 10.5,
            'foto' => 'foto1.jpg'
        ]);

        $product2 = Product::create([
            'nome' => 'Produto 2',
            'preco' => 20.5,
            'foto' => 'foto2.jpg'
        ]);

        $order = Order::create([
            'customer_id' => $customer->id
        ]);

        $order->products()->attach($product1->id, ['quantity' => 2]);
        $order->products()->attach($product2->id, ['quantity' => 3]);

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