<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedFeaturedListingFieldsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $data = array(
                        array("title"=>"Image",'type'=>'file','name'=>'image_id','required'=>'yes'),
                        array("title"=>"Title",'type'=>'text','name'=>'title','required'=>'yes'),
                        array("title"=>"Description",'type'=>'text','name'=>'description','required'=>'yes'),

                        array("title"=>"Recipie Type",'type'=>'text','name'=>'recipe_type','required'=>'yes'),

                        array("title"=>"Address",'type'=>'text','name'=>'address','required'=>'yes'),

                        array("title"=>"Event Time & Date",'type'=>'datetime','name'=>'time_and_date','required'=>'yes'),

                        array("title"=>"Event Type",'type'=>'select','name'=>'event_type','required'=>'yes'),

                        array("title"=>"Package Link",'type'=>'url','name'=>'package_link','required'=>'yes'),

                        array("title"=>"Blog Link",'type'=>'url','name'=>'blog_link','required'=>'yes'),

                        array("title"=>"Tags",'type'=>'text','name'=>'tags','required'=>'yes'),
                        

                    );

        foreach ($data as $key => $value) {
            DB::table('featured_listing_fields')->insert($value);
        }
    }
}
