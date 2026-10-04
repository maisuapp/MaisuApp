<?php

use App\model\PageSettings;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $array = [
            [
                'name' => 'about-us',
                'description' => 'Description',
            ],[
                'name' => 'faq',
                'description' => 'Description',
            ],[
                'name' => 'privacy',
                'description' => 'Description',
            ],[
                'name' => 'terms-and-condition',
                'description' => 'Description',
            ]

        ];

        foreach ($array as $item) {
            $category = new PageSettings();
            $category->name = strtolower($item['name']);
            $category->description = ucwords(strtolower($item['description']));
            $category->save();
        }

        echo "Pages added successfully!";
    }
}
