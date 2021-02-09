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
                        array("title"=>"Provide Pick Up And/Or Delivery",'type'=>'checkbox','name'=>'pick_and_delivery_option','required'=>'yes','multiple_option'=>'true'),
                        array("title"=>"Restaurent Type",'type'=>'select','name'=>'restaurent_type','required'=>'yes'),
                        array("title"=>"Expertise",'type'=>'multiselect','name'=>'expertise','required'=>'yes'),
                        array("title"=>"Title",'type'=>'select','name'=>'title','required'=>'yes'),
                        array("title"=>"Country",'type'=>'checkbox','name'=>'country','required'=>'yes','multiple_option' => 'true'),
                        array("title"=>"Speciality",'type'=>'multiselect','name'=>'speciality','required'=>'yes','multiple_option'=>'true'),
                        array("title"=>"Zip/Postal Code",'type'=>'text','name'=>'zip_postal_code','required'=>'yes'),
                        array("title"=>"Email",'type'=>'email','name'=>'email','required'=>'yes'),
                        array("title"=>"Password",'type'=>'password','name'=>'password','required'=>'yes','hint'=>'Password must be at least 8 characters and contain at least one numeric digit and a special character.'),
                        array("title"=>"First Name",'type'=>'text','name'=>'first_name','required'=>'yes'),
                        array("title"=>"Last Name",'type'=>'text','name'=>'last_name','required'=>'yes'),
                        array("title"=>"Country",'type'=>'select','name'=>'country','required'=>'yes','multiple_option' => 'true'),
                        array("title"=>"Pick Up Disocunt For Alysei Voyagers",'type'=>'select','name'=>'pick_up_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no'),
                        array("title"=>"Delivery Disocunt For Alysei Voyagers",'type'=>'select','name'=>'delivery_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no'),
                        array("title"=>"Neighborhood",'type'=>'select','name'=>'neighborhood','required'=>'no','conditional'=>'yes'),
                        array("title"=>"Region",'type'=>'select','name'=>'region','required'=>'no','conditional'=>'no'),
                        array("title"=>"I AGREE TO THE DATA COLLECTION POLICIES STATED IN THE <a href='https://social.alysei.com/privacy-policy'>PRIVACY POLICY</a> AND <a href='https://social.alysei.com/terms'>TERMS OF SERVICE.</a>",'type'=>'terms','name'=>'terms_and_condition','required'=>'yes','conditional'=>'no'),

                        array("title"=>"Italian Regions",'type'=>'select','name'=>'italian_regions','required'=>'no','conditional'=>'no'),
                        array("title"=>"Interests",'type'=>'multiselect','name'=>'interests','required'=>'yes','conditional'=>'no')

                    );

        foreach ($data as $key => $value) {
            DB::table('user_fields')->insert($value);
        }
    }
}
