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
                        array("title"=>"Company name",'type'=>'text'),
                        array("title"=>"Product type",'type'=>'select'),
                        array("title"=>"Italian Regions",'type'=>'select'),
                        array("title"=>"Horeca",'type'=>'select'),
                        array("title"=>"Private Label",'type'=>'select'),
                        array("title"=>"Alysei Brand Label",'type'=>'select'),
                        array("title"=>"Restaurent name",'type'=>'text'),
                        array("title"=>"Hub",'type'=>'select'),
                        array("title"=>"Provide Pick Up And/Or Delivery",'type'=>'checkbox'),
                        array("title"=>"Restaurent Type",'type'=>'select'),
                        array("title"=>"Expertise",'type'=>'select'),
                        array("title"=>"Title",'type'=>'select'),
                        array("title"=>"Country",'type'=>'checkbox'),
                        array("title"=>"Speciality",'type'=>'select'),
                        array("title"=>"Zip/Postal Code",'type'=>'text'),
                        array("title"=>"Email",'type'=>'email'),
                        array("title"=>"Password",'type'=>'password'),
                        array("title"=>"First Name",'type'=>'text'),
                        array("title"=>"Last Name",'type'=>'text'),

                    );

        foreach ($data as $key => $value) {
            DB::table('user_fields')->insert($value);
        }
    }
}
