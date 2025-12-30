<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Category;

class CategoryImport implements ToModel, WithHeadingRow
{
   public function model(array $row)
{
    
     return new Category([
            'name' => trim($row['name']),
            'image' => 'default-category.jpg', // Set default image path
        ]);
}

}

