<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cancion>
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
            'categoria_id' => \App\Models\Categoria::factory(),
            'codigo' => $this->faker->unique()->word(),
            'pdf_path' => null,
        ];
    }
}
