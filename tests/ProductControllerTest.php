<?php

namespace Tests;

use App\Models\Product;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class ProductControllerTest extends BaseTestCase
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

    public function testStoreProduct()
    {
        $data = [
            'nome' => 'Produto Teste',
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->post('/products', $data);

        $response->seeStatusCode(201);
        $response->seeJsonStructure([
            'id',
            'nome',
            'preco',
            'foto',
            'created_at',
            'updated_at',
        ]);

        $this->seeInDatabase('products', ['nome' => 'Produto Teste']);
    }

    public function testStoreProductInvalidPrice()
    {
        $data = [
            'nome' => 'Produto Teste',
            'preco' => 'preco_invalido',
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->post('/products', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço deve ser um número.']]);
    }

    public function testStoreProductWithoutName()
    {
        $data = [
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->post('/products', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['nome' => ['O campo nome é obrigatório.']]);
    }

    public function testStoreProductWithoutPhoto()
    {
        $data = [
            'nome' => 'Produto Teste',
            'preco' => 99.99,
        ];

        $response = $this->post('/products', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['foto' => ['O campo foto é obrigatório.']]);
    }

    public function testStoreProductWithoutPrice()
    {
        $data = [
            'nome' => 'Produto Teste',
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->post('/products', $data);

        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço é obrigatório.']]);
    }

    public function testIndexProducts()
    {
        // Cria alguns produtos para testar a listagem
        Product::create([
            'nome' => 'Produto 1', 
            'preco' => 10.00,
            'foto' => 'imagem_teste.jpg',
        ]);
        
        Product::create([
            'nome' => 'Produto 2', 
            'preco' => 20.00,
            'foto' => 'imagem_teste2.jpg',
        ]);

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
        $response = $this->get('/products/abc');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);

        $response = $this->get('/products/-1');

        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);
    }

    public function testShowProducts()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $response = $this->get('/products/' . $produto->id);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'id',
            'nome',
            'preco',
            'foto',
            'created_at',
            'updated_at',
        ]);
    }

    public function testShowProductsNotFound()
    {
        $response = $this->get('/products/999');
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
    }

    public function testUpdateProductInvalidId()
    {
        $data = [
            'nome' => 'Produto Teste',
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->put('/products/abc', $data);
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);

        $response = $this->put('/products/-1', $data);
        $response->seeStatusCode(400);
        $response->seeJson(['error' => 'ID inválido.']);
    }

    public function testUpdateProductNotFound()
    {
        $data = [
            'nome' => 'Produto Teste',
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ];

        $response = $this->put('/products/999', $data);
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
    }

    public function testUpdateProduct()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $data = [
            'nome' => 'Produto Teste Atualizado',
            'preco' => 199.99,
            'foto' => 'imagem_teste_atualizada.jpg',
        ];

        $response = $this->put('/products/' . $produto->id, $data);
        $response->seeStatusCode(200);
        $response->seeJsonStructure([
            'id',
            'nome',
            'preco',
            'foto',
            'created_at',
            'updated_at',
        ]);

        $this->seeInDatabase('products', ['nome' => 'Produto Teste Atualizado']);
    }

    public function testUpdateProductInvalidPrice()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $data = [
            'nome' => 'Produto Teste Atualizado',
            'preco' => 'preco_invalido',
            'foto' => 'imagem_teste_atualizada.jpg',
        ];

        $response = $this->put('/products/' . $produto->id, $data);
        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço deve ser um número.']]);
    }

    public function testUpdateProductWithoutName()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $data = [
            'preco' => 199.99,
            'foto' => 'imagem_teste_atualizada.jpg',
        ];

        $response = $this->put('/products/' . $produto->id, $data);
        $response->seeStatusCode(422);
        $response->seeJson(['nome' => ['O campo nome é obrigatório.']]);
    }

    public function testUpdateProductWithoutPhoto()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $data = [
            'nome' => 'Produto Teste Atualizado',
            'preco' => 199.99,
        ];

        $response = $this->put('/products/' . $produto->id, $data);
        $response->seeStatusCode(422);
        $response->seeJson(['foto' => ['O campo foto é obrigatório.']]);
    }

    public function testUpdateProductWithoutPrice()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $data = [
            'nome' => 'Produto Teste Atualizado',
            'foto' => 'imagem_teste_atualizada.jpg',
        ];

        $response = $this->put('/products/' . $produto->id, $data);
        $response->seeStatusCode(422);
        $response->seeJson(['preco' => ['O campo preço é obrigatório.']]);
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
        $response = $this->delete('/products/999');
        $response->seeStatusCode(404);
        $response->seeJson(['error' => 'Produto não encontrado.']);
    }

    public function testDestroyProduct()
    {
        $produto = Product::create([
            'nome' => 'Produto Teste', 
            'preco' => 99.99,
            'foto' => 'imagem_teste.jpg',
        ]);

        $response = $this->delete('/products/' . $produto->id);
        $response->seeStatusCode(200);
        $response->seeJson(['message' => 'Produto removido com sucesso.']);

        $this->notSeeInDatabase('products', ['nome' => 'Produto Teste', 'deleted_at' => null]);
    }
}