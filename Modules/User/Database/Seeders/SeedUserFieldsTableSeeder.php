<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedUserFieldsTableSeeder extends Seeder
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
                        array("title"=>"Company name",'type'=>'text','name'=>'comapny_name','required'=>'no'),
                        array("title"=>"Product type",'type'=>'checkbox','name'=>'product_type','required'=>'yes'),
                        array("title"=>"Italian Regions",'type'=>'select','name'=>'italian_regions','required'=>'yes'),
                        array("title"=>"Horeca",'type'=>'radio','name'=>'horeca','required'=>'yes'),
                        array("title"=>"Private Label",'type'=>'radio','name'=>'private_label','required'=>'yes'),
                        array("title"=>"Alysei Brand Label",'type'=>'radio','name'=>'alysei_brand_label','required'=>'no'),
                        array("title"=>"Restaurent name",'type'=>'text','name'=>'restaurent_name','required'=>'no'),
                        array("title"=>"Hub",'type'=>'select','name'=>'hub','required'=>'yes'),
                        array("title"=>"Provide Pick Up And/Or Delivery",'type'=>'checkbox','name'=>'pick_and_delivery_option','required'=>'yes'),
                        array("title"=>"Restaurent Type",'type'=>'select','name'=>'restaurent_type','required'=>'yes'),
                        array("title"=>"Expertise",'type'=>'multiselect','name'=>'expertise','required'=>'yes'),
                        array("title"=>"Title",'type'=>'select','name'=>'title','required'=>'yes'),
                        array("title"=>"Country",'type'=>'checkbox','name'=>'country','required'=>'yes'),
                        array("title"=>"Speciality",'type'=>'multiselect','name'=>'speciality','required'=>'yes'),
                        array("title"=>"Zip/Postal Code",'type'=>'text','name'=>'zip_postal_code','required'=>'yes'),
                        array("title"=>"Email",'type'=>'email','name'=>'email','required'=>'yes'),
                        array("title"=>"Password",'type'=>'password','name'=>'password','required'=>'yes'),
                        array("title"=>"First Name",'type'=>'text','name'=>'first_name','required'=>'yes'),
                        array("title"=>"Last Name",'type'=>'text','name'=>'last_name','required'=>'yes'),
                        array("title"=>"Country",'type'=>'select','name'=>'country','required'=>'yes'),
                        array("title"=>"Pick Up Disocunt For Alysei Voyagers",'type'=>'select','name'=>'pick_up_discount_for_alysei_voyagers','required'=>'no','conditional'=>'yes'),
                        array("title"=>"Delivery Disocunt For Alysei Voyagers",'type'=>'select','name'=>'delivery_discount_for_alysei_voyagers','required'=>'no','conditional'=>'yes'),
                        array("title"=>"Neighborhood",'type'=>'select','name'=>'neighborhood','required'=>'no','conditional'=>'yes'),
                        array("title"=>"Region",'type'=>'select','name'=>'region','required'=>'no','conditional'=>'yes')

                    );

        foreach ($data as $key => $value) {
            DB::table('user_fields')->insert($value);
        }
    }
}
