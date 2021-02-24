<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data = array(
                        array("title" => "Product","slug"=>"product"),
                        array("title" => "Recipie","slug"=>"recipie"), 
                        array("title" => "Blog","slug"=>"Blog"), 
                        array("title" => "Package","slug"=>"package"),
                        array("title" => "Event","slug"=>"events")

                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_types')->insert($value);
        }
    }
}
