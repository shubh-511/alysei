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
                        array("type" => "super_admin","name"=>"Super Admin",'slug'=>'super_admin','display_name'=>'Super Admin'),
                        array("type" => "admin","name"=>"Admin",'slug'=>'admin','display_name'=>'Admin'),
                        array("type" => "member","name"=>"Italian F&B Producers",'slug'=>'Italian_F_and_B_Producers','display_name'=>'Producer'),
                        array("type" => "member","name"=>"Importer",'slug'=>'importer','display_name'=>'Importer'),
                        array("type" => "member","name"=>"Distributer",'slug'=>'distributer','display_name'=>'Distributer'),
                        array("type" => "member","name"=>"US Importers & Distributers",'slug'=>'Importer_and_Distributer','display_name'=>'Importers & Distributers'),
                        array("type" => "member","name"=>"Voice Of Experts",'slug'=>'voice_of_expert','display_name'=>'Voice Of Experts'),
                        array("type" => "member","name"=>"Travel Agencies",'slug'=>'travel_agencies','display_name'=>'Travel Agencies'),
                        array("type" => "member","name"=>"Restaurents",'slug'=>'restaurents','display_name'=>'Restaurents'),
                        array("type" => "subscription","name"=>"Voyagers",'slug'=>'voyagers','display_name'=>'Voyagers')
                    );

        foreach ($data as $key => $value) {
            $db = Role::create($value);     
        }
    }
}
