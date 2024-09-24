<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CustomerControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoreCustomer()
    {
        $data = [
            'nome' => 'John Doe',
            'email' => 'johndoe@example.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Exemplo, 123',
            'complemento' => 'Apto 45',
            'bairro' => 'Bairro Exemplo',
            'cep' => '12345-678',
        ];

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(201);
        $response->seeJsonContains(['nome' => 'John Doe']);
    }

    public function testStoreCustomerWithDuplicateEmail()
    {
        // Crie um cliente existente
        Customer::create([
            'nome' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'nascimento' => '1985-02-14',
            'endereco' => 'Rua Teste, 456',
            'complemento' => 'Casa',
            'bairro' => 'Bairro Teste',
            'cep' => '98765-432',
        ]);

        // Tente criar um cliente com o mesmo e-mail
        $data = [
            'nome' => 'John Doe',
            'email' => 'janedoe@example.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Exemplo, 123',
            'complemento' => 'Apto 45',
            'bairro' => 'Bairro Exemplo',
            'cep' => '12345-678',
        ];

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'E-mail já cadastrado.']);
    }
}
