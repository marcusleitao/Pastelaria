<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nome' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'nascimento' => $this->faker->date,
            'endereco' => 'Rua Exemplo, 123',
            'complemento' => 'Apto 45',
            'bairro' => 'Bairro Exemplo',
            'cep' => '12345-678',
        ];
    }
}
