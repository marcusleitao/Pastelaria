<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ProductControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testStoreProduct()
    {
        $data = Product::factory()->make()->toArray();

        $response = $this->post('/products', $data);

        $response->seeStatusCode(201);
        $response->seeJsonContains($data);

        $response->seeInDatabase('products', $data);
    }

    public function testStoreProductInvalidPrice()
    {
        $data = Product::factory()->make(['preco' => 'preco_invalido'])->toArray();

        $response = $this->post('/products', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço deve ser um número.']]);
    }

    public function testStoreProductWithoutRequiredFields()
    {
        $response = $this->post('/products', []);
        $response->seeStatusCode(422);
        $response->seeJsonContains([
            'nome' => ['O campo nome é obrigatório.'],
            'preco' => ['O campo preço é obrigatório.'],
            'foto' => ['O campo foto é obrigatório.']
        ]);
    }

    public function testIndexProducts()
    {
        $product = Product::factory()->create();

        $product['preco'] = number_format($product['preco'], 2, '.', '');

        $response = $this->get('/products?sort_by=created_at&sort_direction=asc&per_page=15');

        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nome',
                    'preco',
                    'foto',
                    'created_at',
                    'updated_at',
                ],
            ]
        ]);
        $response->seeJsonContains($product->toArray());
        $response->seeInDatabase('products', $product->toArray());
    }

    public function testIndexProductsInvalidSortColumn()
    {
        $response = $this->get('/products?sort_by=invalid_column&sort_direction=asc&per_page=15');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Coluna de ordenação inválida.']);
    }

    public function testIndexProductsInvalidSortDirection()
    {
        $response = $this->get('/products?sort_by=created_at&sort_direction=invalid_direction&per_page=15');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Direção de ordenação inválida.']);
    }

    public function testIndexProductsInvalidPerPage()
    {
        $response = $this->get('/products?sort_by=created_at&sort_direction=asc&per_page=-1');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Número de itens por página inválido.']);
    }

    public function testIndexProductsInvalidPerPageNotNumeric()
    {
        $response = $this->get('/products?sort_by=created_at&sort_direction=asc&per_page=abc');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Número de itens por página inválido.']);
    }

    public function testIndexProductsInvalidPerPageZero()
    {
        $response = $this->get('/products?sort_by=created_at&sort_direction=asc&per_page=0');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'Número de itens por página inválido.']);
    }

    public function testShowProductsInvalidId()
    {
        Product::factory()->create();

        $response = $this->get('/products/abc');
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);

        $response = $this->get('/products/-1');
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);
        $response->notSeeInDatabase('products', ['id' => -1]);
    }

    public function testShowProducts()
    {
        $product = Product::factory()->create();

        $product['preco'] = number_format($product['preco'], 2, '.', '');

        $response = $this->get('/products/' . $product->id);
        $response->seeStatusCode(200);
        $response->seeJsonContains($product->toArray());
        $response->seeInDatabase('products', $product->toArray());
    }

    public function testShowProductsNotFound()
    {
        Product::factory()->create();

        $notFoundId = 999;

        $response = $this->get('/products/' . $notFoundId);
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
        $response->notSeeInDatabase('products', ['id' => $notFoundId]);
    }

    public function testUpdateProductInvalidId()
    {
        Product::factory()->create();

        $data = Product::factory()->make()->toArray();

        $response = $this->put('/products/abc', $data);
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);

        $response = $this->put('/products/-1', $data);
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);
        $response->notSeeInDatabase('products', ['id' => -1]);
    }

    public function testUpdateProductNotFound()
    {
        $notFoundId = 999;

        Product::factory()->create();

        $data = Product::factory()->make()->toArray();

        $response = $this->put('/products/' . $notFoundId, $data);
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
        $response->notSeeInDatabase('products', ['id' => $notFoundId]);
    }

    public function testUpdateProduct()
    {
        $product = Product::factory()->create();

        $updatedData = Product::factory()->make()->toArray();

        $response = $this->put('/products/' . $product->id, $updatedData);
        $response->seeStatusCode(200);
        $response->seeJsonContains($updatedData);

        $this->notSeeInDatabase('products', $product->toArray());
        $this->seeInDatabase('products', ['id' => $product->id] + $updatedData);
    }

    public function testUpdateProductInvalidPrice()
    {
        $product = Product::factory()->create();

        $data = Product::factory()->make(['preco' => 'preco_invalido'])->toArray();

        $response = $this->put('/products/' . $product->id, $data);
        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço deve ser um número.']]);
    }

    public function testUpdateProductWithoutRequiredFields()
    {
        $product = Product::factory()->create();

        $response = $this->put('/products/' . $product->id, []);
        $response->seeJsonContains([
            'nome' => ['O campo nome é obrigatório.'],
            'foto' => ['O campo foto é obrigatório.'],
            'preco' => ['O campo preço é obrigatório.']
        ]);
        $response->seeStatusCode(422);
    }

    public function testDestroyProductInvalidId()
    {
        $response = $this->delete('/products/abc');
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);

        $response = $this->delete('/products/-1');
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);
    }

    public function testDestroyProductNotFound()
    {
        Product::factory()->create();

        $notFoundId = 999;

        $response = $this->delete('/products/' . $notFoundId);
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
        $response->notSeeInDatabase('products', ['id' => $notFoundId]);
    }

    public function testDestroyProduct()
    {
        $product = Product::factory()->create();

        $response = $this->delete('/products/' . $product->id);
        $response->seeStatusCode(200);
        $response->seeJson(['message' => 'Produto removido com sucesso.']);

        $this->notSeeInDatabase('products', ['id' => $product->id, 'deleted_at' => null]);
    }
}