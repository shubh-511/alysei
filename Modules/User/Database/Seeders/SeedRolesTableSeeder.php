<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\Role;

class SeedRolesTableSeeder extends Seeder
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
                        array("type" => "super_admin","name"=>"Super Admin",'slug'=>'super_admin','display_name'=>'Super Admin','description' => ''),
                        array("type" => "admin","name"=>"Admin",'slug'=>'admin','display_name'=>'Admin','description' => ''),
                        array("type" => "member","name"=>"Italian F&B Producers",'slug'=>'Italian_F_and_B_Producers','display_name'=>'Producer','description' => 'Alysei certified Producers will be able to explore, find and connect with certified Importers and Distributors in the USA, build up and consolidate their brand, promote their products, and reach their target customers faster to gain visibility and traction in the USA market.'),
                        array("type" => "member","name"=>"Importer",'slug'=>'importer','display_name'=>'Importer','description' => ''),
                        array("type" => "member","name"=>"Distributer",'slug'=>'distributer','display_name'=>'Distributer','description' => ''),
                        array("type" => "member","name"=>"US Importers & Distributers",'slug'=>'Importer_and_Distributer','display_name'=>'Importers & Distributers','description' => 'Alysei certified Importers and Distributors will be able to explore, find and connect withlocal italian Producers to strenght their product portfolio, enanch their competiviness, gain visibility,expand their brand and market access.'),
                        array("type" => "member","name"=>"Voice Of Experts",'slug'=>'voice_of_expert','display_name'=>'Voice Of Experts','description' => 'Chefs, School of Chefs, Top Restaurants Chefs, Chefs with blogs, Chefs Association, Italian Food and Beverage Specialists, TV Shows, Magazine, Book Writer and other sector experts will be able to leverage on the Alysei platform to promote their name, brand, offering, events, blogs.'),
                        array("type" => "member","name"=>"Travel Agencies",'slug'=>'travel_agencies','display_name'=>'Travel Agencies','description' => 'Alysei Certified Travel Agencies will be able to leverage on the Alysei platform, expand their target clients, reach and strengthen connection with certified Producers, Importers and Distributors in USA, Voice of Experts, grown their visibility, reach target customers.'),
                        array("type" => "member","name"=>"Italian Restaurants in US",'slug'=>'restaurents','display_name'=>'Italian Restaurants in US','description' => 'Alysei Certified Restaurants will be able to leverage on the Alysei platform to strenght collaboration leveragin on the B2B engine, expand their brand, promote their cusine and special events, bring more clients to the table by exponentially expand their reach. '),
                        array("type" => "subscription","name"=>"Voyagers",'slug'=>'voyagers','display_name'=>'Voyagers','description' => 'Users will join Alysei to enjoy the magic world of our endless cuisine searching for products, restaurants, events, trip to Italy, tasting tours, cooking classes, recipes, blogs and many more activities helping to strengthen our great Made in Italy brand in USA.')
                    );

        foreach ($data as $key => $value) {
            $db = Role::create($value);     
        }
    }
}
