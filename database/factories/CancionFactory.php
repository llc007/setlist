<?php

namespace Database\Factories;

use App\Models\Cancion;
use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cancion>
 */
class CancionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence(3),
            'artista' => $this->faker->name(),
            'letra' => $this->faker->text(),
            'tono_original' => $this->faker->randomElement(['C', 'D', 'E', 'F', 'G', 'A', 'B']),
            'categoria_id' => Categoria::factory(),
            'codigo' => $this->faker->unique()->word(),
            'pdf_path' => null,
        ];
    }
}
