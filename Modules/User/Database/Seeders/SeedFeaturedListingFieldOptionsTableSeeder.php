<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingFieldOptionsTableSeeder extends Seeder
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
                        array("featured_listing_field_id" => 7,"option"=>"Event Type 1"),
                        array("featured_listing_field_id" => 7,"option"=>"Event Type 2"),
                        array("featured_listing_field_id" => 7,"option"=>"Event Type 3"),
                        array("featured_listing_field_id" => 7,"option"=>"Event Type 4"),
                        array("featured_listing_field_id" => 7,"option"=>"Event Type 5"),
                        
                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_field_options')->insert($value);
        }
        // $this->call("OthersTableSeeder");
    }
}
