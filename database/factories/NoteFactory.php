<?php

namespace Database\Factories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends Factory<Note>
 */
class NoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(6);
        $slugTitle = Str::slug($title);
        
        // Tiru logikanya di sini menggunakan pembantu milidetik
        $curr_time = now();
        $timestamp = $curr_time->format('YmdHis');
        $hash = substr(hash('sha256', $slugTitle . '-' . $timestamp), 0, 16);

        return [
            'title' => $title,
            'slug' => $slugTitle . '-' . $hash, // Slug hasil format barumu!
            'body' => $this->faker->paragraphs(3, true),
            'is_indexed' => $this->faker->boolean(85),
            'author_id' => \App\Models\User::inRandomOrder(null)->first()->id ?? 1,
            'category_id' => \App\Models\Category::inRandomOrder(null)->first()->id ?? 1,
            'created_at' => $curr_time->format("Y-m-d H:i:s")
        ];
    }
}
