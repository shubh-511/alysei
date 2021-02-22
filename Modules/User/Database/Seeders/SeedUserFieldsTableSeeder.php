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
                        array("title"=>"Company name",'type'=>'text','name'=>'comapny_name','required'=>'yes'),
                        array("title"=>"Product type",'type'=>'checkbox','name'=>'product_type','required'=>'yes','hint'=>'Select Product Type'),
                        array("title"=>"Italian Regions",'type'=>'select','name'=>'italian_regions','required'=>'yes'),
                        array("title"=>"Horeca",'type'=>'radio','name'=>'horeca','required'=>'yes','hint'=>'Select Horeca'),
                        array("title"=>"Private Label",'type'=>'radio','name'=>'private_label','required'=>'yes','hint'=>'Select Private Label'),
                        array("title"=>"Alysei Brand Label",'type'=>'radio','name'=>'alysei_brand_label','required'=>'no','hint'=>'Select Alysei Brand Label'),
                        array("title"=>"Restaurant name",'type'=>'text','name'=>'restaurant_name','required'=>'yes'),
                        array("title"=>"Hub",'type'=>'select','name'=>'hub','required'=>'yes'),
                        array("title"=>"Provide Pick Up And/Or Delivery",'type'=>'checkbox','name'=>'pick_and_delivery_option','required'=>'yes','multiple_option'=>'true','hint'=>'Select Pick up or Delivery'),
                        array("title"=>"Restaurant Type",'type'=>'select','name'=>'restaurant_type','required'=>'yes'),
                        array("title"=>"What is your specialization",'type'=>'multiselect','name'=>'expertise','required'=>'yes','hint'=>'Choose your specialization'),
                        array("title"=>"Title",'type'=>'multiselect','name'=>'title','required'=>'yes'),
                        array("title"=>"Country",'type'=>'select','name'=>'country','required'=>'yes','api_call'=>'true'),
                        array("title"=>"Speciality Trips",'type'=>'multiselect','name'=>'speciality','required'=>'yes','multiple_option'=>'true','hint'=>'Select your speciality trips'),
                        array("title"=>"Zip/Postal Code",'type'=>'text','name'=>'zip_postal_code','required'=>'yes'),
                        array("title"=>"Email",'type'=>'email','name'=>'email','required'=>'yes'),
                        array("title"=>"Password",'type'=>'password','name'=>'password','required'=>'yes','hint'=>'Password must be at least 8 characters and contain at least one numeric digit and a special character.'),
                        array("title"=>"First Name",'type'=>'text','name'=>'first_name','required'=>'yes'),
                        array("title"=>"Last Name",'type'=>'text','name'=>'last_name','required'=>'yes'),
                        array("title"=>"Country",'type'=>'select','name'=>'country','required'=>'yes','multiple_option' => 'true'),
                        array("title"=>"Pick Up Disocunt For Alysei Voyagers",'type'=>'select','name'=>'pick_up_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no','hint'=>'Select One'),
                        array("title"=>"Delivery Disocunt For Alysei Voyagers",'type'=>'select','name'=>'delivery_discount_for_alysei_voyagers','required'=>'no','conditional'=>'no'),
                        array("title"=>"Neighborhood",'type'=>'select','name'=>'neighborhood','required'=>'no','conditional'=>'yes'),
                        array("title"=>"Region",'type'=>'select','name'=>'region','required'=>'no','conditional'=>'no'),
                        array("title"=>"I AGREE TO THE DATA COLLECTION POLICIES STATED IN THE <a href='https://social.alysei.com/privacy-policy'>PRIVACY POLICY</a> AND <a href='https://social.alysei.com/terms'>TERMS OF SERVICE.</a>",'type'=>'terms','name'=>'terms_and_condition','required'=>'yes','conditional'=>'no'),

                        array("title"=>"Italian Regions",'type'=>'select','name'=>'italian_regions','required'=>'no','conditional'=>'no'),
                        array("title"=>"Interests",'type'=>'multiselect','name'=>'interests','required'=>'yes','conditional'=>'no'),

                        
                        array("title"=>"State/Region",'type'=>'select','name'=>'state','required'=>'yes','conditional'=>'no','api_call'=>'true'),
                        array("title"=>"City",'type'=>'select','name'=>'city','required'=>'no','conditional'=>'no'),
                        array("title"=>"VAT No.",'type'=>'text','name'=>'vat_number','required'=>'yes','conditional'=>'no','hint'=>'Enter VAT no.'),
                        array("title"=>"Address",'type'=>'text','name'=>'address','required'=>'yes','conditional'=>'no'),
                        array("title"=>"Enter Your City",'type'=>'text','name'=>'your_city','required'=>'no','conditional'=>'no')

                    );

        foreach ($data as $key => $value) {
            DB::table('user_fields')->insert($value);
        }
    }
}
