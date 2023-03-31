<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
            DB::table('blogs')->insert([
                'id'=>'01',
                'title'=>'blog1',
                'blog_post'=>'blog1 starts here..',
                'blog_category_id'=>22,
                'tags'=>'Gadgets and techs'
            ]);
    }
}