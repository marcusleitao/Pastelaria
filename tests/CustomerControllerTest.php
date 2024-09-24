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
        Customer::create([
            'nome' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'nascimento' => '1985-02-14',
            'endereco' => 'Rua Teste, 456',
            'complemento' => 'Casa',
            'bairro' => 'Bairro Teste',
            'cep' => '98765-432',
        ]);

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

    public function testIndexWithValidParameters()
    {
        Customer::create([
            'nome' => 'John Doe',
            'email' => 'johndoe@example.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Exemplo, 123',
            'complemento' => 'Apto 45',
            'bairro' => 'Bairro Exemplo',
            'cep' => '12345-678',
        ]);

        $response = $this->get('/customers?sort_by=created_at&sort_direction=asc&per_page=10');

        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nome',
                    'email',
                    'nascimento',
                    'endereco',
                    'complemento',
                    'bairro',
                    'cep',
                    'created_at',
                    'updated_at',
                ]
            ]
        ]);
    }

    public function testIndexWithInvalidSortByColumn()
    {
        $response = $this->get('/customers?sort_by=invalid_column');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Coluna de ordenação inválida.']);
    }

    public function testIndexWithInvalidSortDirection()
    {
        $response = $this->get('/customers?sort_direction=invalid_direction');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Direção de ordenação inválida.']);
    }

    public function testIndexWithInvalidPerPage()
    {
        $response = $this->get('/customers?per_page=-1');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Número de itens por página inválido.']);
    }

    public function testShowWithInvalidId()
    {
        $response = $this->get('/customers/abc');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);

        $response = $this->get('/customers/-1');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
    }

    public function testShowCustomerNotFound()
    {
        $response = $this->get('/customers/99999'); // Assumindo que este ID não existe
        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
    }

    public function testShowCustomerFound()
    {
        $customer = Customer::create([
            'nome' => 'John Doe',
            'email' => 'johndoe@example.com',
            'nascimento' => '1990-01-01',
            'endereco' => 'Rua Exemplo, 123',
            'complemento' => 'Apto 45',
            'bairro' => 'Bairro Exemplo',
            'cep' => '12345-678',
        ]);

        $response = $this->get("/customers/{$customer->id}");
        $response->seeStatusCode(200);
        $response->seeJsonContains([
            'id' => $customer->id,
            'nome' => $customer->nome,
            'email' => $customer->email,
            'nascimento' => $customer->nascimento,
            'endereco' => $customer->endereco,
            'complemento' => $customer->complemento,
            'bairro' => $customer->bairro,
            'cep' => $customer->cep
        ]);
    }

    public function testUpdateWithInvalidId()
    {
        $response = $this->put('/customers/abc', []);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);

        $response = $this->put('/customers/-1', []);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
    }

    public function testUpdateCustomerNotFound()
    {
        $response = $this->put('/customers/99999', [
            'nome' => 'Novo Nome',
            'email' => 'novoemail@example.com',
            'nascimento' => '2000-01-01',
            'endereco' => 'Rua Nova, 123',
            'complemento' => 'Apto 456',
            'bairro' => 'Centro',
            'cep' => '12345-678'
        ]);

        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
    }

    public function testUpdateWithExistingEmail()
    {
        $customer1 = Customer::create([
            'nome' => 'Novo Nome',
            'email' => 'email1@example.com',
            'nascimento' => '2000-01-01',
            'endereco' => 'Rua Nova, 123',
            'complemento' => 'Apto 456',
            'bairro' => 'Centro',
            'cep' => '12345-678'
        ]);

        $customer2 = Customer::create([
            'nome' => 'Novo Nome',
            'email' => 'email2@example.com',
            'nascimento' => '2000-01-01',
            'endereco' => 'Rua Nova, 123',
            'complemento' => 'Apto 456',
            'bairro' => 'Centro',
            'cep' => '12345-678'
        ]);

        $response = $this->put("/customers/{$customer2->id}", [
            'nome' => 'Novo Nome',
            'email' => 'email1@example.com', //email duplicado
            'nascimento' => '2000-01-01',
            'endereco' => 'Rua Nova, 123',
            'complemento' => 'Apto 456',
            'bairro' => 'Centro',
            'cep' => '12345-678'
        ]);

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'E-mail já cadastrado.']);
    }

    public function testUpdateCustomerSuccessfully()
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

        $updatedData = [
            'nome' => 'Nome Novo',
            'email' => 'novonome@example.com',
            'nascimento' => '1980-01-01',
            'endereco' => 'Rua Nova, 456',
            'complemento' => 'Casa 789',
            'bairro' => 'Novo Bairro',
            'cep' => '98765-432'
        ];

        $response = $this->put("/customers/{$customer->id}", $updatedData);

        $response->seeStatusCode(200);
        $response->seeJsonContains([
            'id' => $customer->id,
            'nome' => 'Nome Novo',
            'email' => 'novonome@example.com',
            'nascimento' => '1980-01-01',
            'endereco' => 'Rua Nova, 456',
            'complemento' => 'Casa 789',
            'bairro' => 'Novo Bairro',
            'cep' => '98765-432'
        ]);
    }

    public function testDestroyWithInvalidId()
    {
        $response = $this->delete('/customers/abc');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);

        $response = $this->delete('/customers/-1');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
    }

    public function testDestroyCustomerNotFound()
    {
        $response = $this->delete('/customers/99999');

        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
    }

    public function testDestroyCustomerSuccessfully()
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

        $response = $this->delete("/customers/{$customer->id}");

        $response->seeStatusCode(200);
        $response->seeJsonContains(['message' => 'Cliente deletado com sucesso.']);

        $this->assertNull(Customer::find($customer->id));
    }
}
