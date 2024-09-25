<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'nome' => 'Pastel de Carne',
            'preco' => 10.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-carne.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Queijo',
            'preco' => 8.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-queijo.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Frango',
            'preco' => 9.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-frango.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Calabresa',
            'preco' => 11.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-calabresa.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Pizza',
            'preco' => 12.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-pizza.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Camarão',
            'preco' => 15.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-camarao.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Chocolate',
            'preco' => 7.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-chocolate.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Banana',
            'preco' => 6.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-banana.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Morango',
            'preco' => 8.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-morango.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Doce de Leite',
            'preco' => 9.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-doce-de-leite.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Goiabada',
            'preco' => 7.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-goiabada.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Romeu e Julieta',
            'preco' => 8.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-romeu-e-julieta.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Carne Seca',
            'preco' => 13.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-carne-seca.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Palmito',
            'preco' => 10.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-palmito.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Camarão com Catupiry',
            'preco' => 16.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-camarao-com-catupiry.jpg',
        ]);

        Product::create([
            'nome' => 'Pastel de Carne com Queijo',
            'preco' => 12.00,
            'foto' => 'https://marcusleitao.dev/pastelaria/pastel-de-carne-com-queijo.jpg',
        ]);
    }
}
