<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Product;
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
            'email' => 'email@example.com',
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
}