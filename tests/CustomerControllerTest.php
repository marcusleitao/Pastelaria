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
        $data = Customer::factory()->make()->toArray();

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(201);
        $response->seeJsonContains($data);

        $response->seeInDatabase('customers', $data);
    }

    public function testStoreCustomerWithDuplicateEmail()
    {
        Customer::factory()->create([
            'email' => 'fulano@tal.com.br',
        ]);

        // Cria dados do novo cliente com email duplicado
        $data = Customer::factory()->make([
            'email' => 'fulano@tal.com.br',
        ])->toArray();

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'E-mail já cadastrado.']);
    }

    public function testStoreCustomerWithoutRequiredFields()
    {
        $response = $this->post('/customers', []);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'nome' => ['O campo nome é obrigatório.'],
            'email' => ['O campo e-mail é obrigatório.'],
            'nascimento' => ['O campo nascimento é obrigatório.'],
            'endereco' => ['O campo endereço é obrigatório.'],
            'complemento' => ['O campo complemento é obrigatório.'],
            'bairro' => ['O campo bairro é obrigatório.'],
            'cep' => ['O campo CEP é obrigatório.'],
        ]);
    }

    public function testStoreCustomerWithEmailInvalid()
    {
        $data = Customer::factory()->make(['email' => 'emailinvalido'])->toArray();

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(422);
        $response->seeJsonContains(['email' => ['O campo e-mail deve ser um e-mail válido.']]);
        $response->notSeeInDatabase('customers', ['email' => $data['email']]);
    }

    public function testStoreCustomerWithDateInvalid()
    {
        $data = Customer::factory()->make(['nascimento' => 'datainvalida'])->toArray();

        $response = $this->post('/customers', $data);
        $response->seeStatusCode(422);
        $response->seeJsonContains(['nascimento' => ['O campo nascimento deve ser uma data válida.']]);
        $response->notSeeInDatabase('customers', ['email' => $data['email']]);
    }

    public function testIndexCustomerWithValidParameters()
    {
        $data = Customer::factory()->create();

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
        $response->seeInDatabase('customers', ['email' => $data['email']]);
    }

    public function testIndexCustomerWithInvalidSortByColumn()
    {
        $response = $this->get('/customers?sort_by=invalid_column');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Coluna de ordenação inválida.']);
    }

    public function testIndexCustomerWithInvalidSortDirection()
    {
        $response = $this->get('/customers?sort_direction=invalid_direction');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Direção de ordenação inválida.']);
    }

    public function testIndexCustomerWithInvalidPerPage()
    {
        $response = $this->get('/customers?per_page=-1');

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'Número de itens por página inválido.']);
    }

    public function testShowCustomerWithInvalidId()
    {
        Customer::factory()->create();

        $response = $this->get('/customers/abc');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
        // Não é possível verificar se há um registro com id 'abc' no banco, pois o tipo do campo id é 'int'

        $response = $this->get('/customers/-1');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
        $response->notSeeInDatabase('customers', ['id' => -1]);
    }

    public function testShowCustomerNotFound()
    {
        $notFoundId = 99999;
        Customer::factory()->create();

        $response = $this->get('/customers/' . $notFoundId); // Assumindo que este ID não existe
        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
        $response->notSeeInDatabase('customers', ['id' => $notFoundId]);
    }

    public function testShowCustomerFound()
    {
        $customer = Customer::factory()->create();

        $response = $this->get("/customers/{$customer->id}");
        $response->seeStatusCode(200);
        $response->seeJsonContains($customer->toArray());
        $response->seeInDatabase('customers', ['id' => $customer->id]);
    }

    public function testUpdateCustomerWithInvalidId()
    {
        Customer::factory()->create();

        $data = Customer::factory()->make()->toArray();

        $response = $this->put('/customers/abc', $data);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);

        $response = $this->put('/customers/-1', $data);
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
        $response->notSeeInDatabase('customers', ['id' => -1]);
    }

    public function testUpdateCustomerWithoutRequiredFields()
    {
        $customer = Customer::factory()->create();

        $response = $this->put('/customers/' . $customer->id, []);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'nome' => ['O campo nome é obrigatório.'],
            'email' => ['O campo e-mail é obrigatório.'],
            'nascimento' => ['O campo nascimento é obrigatório.'],
            'endereco' => ['O campo endereço é obrigatório.'],
            'complemento' => ['O campo complemento é obrigatório.'],
            'bairro' => ['O campo bairro é obrigatório.'],
            'cep' => ['O campo CEP é obrigatório.'],
        ]);
    }

    public function testUpdateCustomerWithEmailInvalid()
    {
        $customer = Customer::factory()->create();

        $customer['email'] = 'emailinvalido';

        $response = $this->put('/customers/' . $customer->id, $customer->toArray());
        $response->seeStatusCode(422);
        $response->seeJsonContains(['email' => ['O campo e-mail deve ser um e-mail válido.']]);
        $response->notSeeInDatabase('customers', ['email' => $customer['email']]);
    }

    public function testUpdateCustomerWithDateInvalid()
    {
        $customer = Customer::factory()->create();

        $customer['nascimento'] = 'datainvalida';

        $response = $this->put('/customers/' . $customer->id, $customer->toArray());
        $response->seeStatusCode(422);
        $response->seeJsonContains(['nascimento' => ['O campo nascimento deve ser uma data válida.']]);
    }
      

    public function testUpdateCustomerNotFound()
    {
        Customer::factory()->create();

        $notFoundId = 99999;

        $data = Customer::factory()->make()->toArray();

        $response = $this->put('/customers/' . $notFoundId, $data);

        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
        $response->notSeeInDatabase('customers', ['id' => $notFoundId]);
    }

    public function testUpdateCustomerWithExistingEmail()
    {
        $duplicatedEmail = 'email1@example.com.br';

        Customer::factory()->create([
            'email' => $duplicatedEmail,
        ]);

        $customer2 = Customer::factory()->create([
            'email' => 'email2@example.com',
        ]);

        $customer2['email'] = $duplicatedEmail; //altera apenas o email, mantendo os outros dados

        $response = $this->put("/customers/" . $customer2->id, $customer2->toArray());

        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'E-mail já cadastrado.']);
        $response->NotSeeInDatabase('customers', ['id' => $customer2->id, 'email' => $duplicatedEmail]);
    }

    public function testUpdateCustomerSuccessfully()
    {

        $customer = Customer::factory()->create();

        $updatedData = Customer::factory()->make()->toArray();

        $response = $this->put("/customers/{$customer->id}", $updatedData);

        $updatedData['id'] = $customer->id; // Adiciona o id ao array para comparação

        $response->seeStatusCode(200);
        $response->seeJsonContains($updatedData);
        $response->seeInDatabase('customers', ['id' => $customer->id] + $updatedData);
    }

    public function testDestroyCustomerWithInvalidId()
    {
        Customer::factory()->create();

        $response = $this->delete('/customers/abc');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);

        $response = $this->delete('/customers/-1');
        $response->seeStatusCode(400);
        $response->seeJsonContains(['error' => 'ID inválido.']);
        $response->notSeeInDatabase('customers', ['id' => -1]);
    }

    public function testDestroyCustomerNotFound()
    {
        $notFoundId = 99999;

        Customer::factory()->create();

        $response = $this->delete('/customers/' . $notFoundId);

        $response->seeStatusCode(404);
        $response->seeJsonContains(['error' => 'Cliente não encontrado.']);
        $response->notSeeInDatabase('customers', ['id' => $notFoundId]);
    }

    public function testDestroyCustomerSuccessfully()
    {
        $customer = Customer::factory()->create();

        $response = $this->delete("/customers/{$customer->id}");

        $response->seeStatusCode(200);
        $response->seeJsonContains(['message' => 'Cliente deletado com sucesso.']);
        $response->notSeeInDatabase('customers', ['id' => $customer->id, 'deleted_at' => null]);
    }
}
