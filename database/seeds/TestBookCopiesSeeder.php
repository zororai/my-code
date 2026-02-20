<?php

use Illuminate\Database\Seeder;
use App\Book;
use App\BookCopy;

class TestBookCopiesSeeder extends Seeder
{
    public function run()
    {
        $book = Book::create([
            'title' => 'Test Book - Introduction to Programming',
            'book_number' => 'TEST-001',
            'author' => 'John Smith',
            'isbn' => null,
            'category' => 'Computer Science',
            'quantity' => 20,
            'available_quantity' => 20,
            'condition' => 'excellent',
            'condition_notes' => 'Brand new copies for testing',
            'status' => 'available',
            'added_by' => 1
        ]);

        $conditions = ['excellent', 'good', 'fair'];
        
        for ($i = 1; $i <= 20; $i++) {
            BookCopy::create([
                'book_id' => $book->id,
                'isbn' => 'ISBN-978-0-TEST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'copy_number' => 'TEST-001-' . $i,
                'condition' => $conditions[$i % 3],
                'condition_notes' => 'Test copy #' . $i,
                'status' => 'available',
                'added_by' => 1
            ]);
        }

        $this->command->info('Created book: ' . $book->title . ' with 20 copies');
    }
}
