<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('products')->insert([
            'id'=>'01',
            'title'=>'t-shirt',
            'sku'=>'xl',
            'description'=>'gorgeous and comfortable'
        ]);
    }
}