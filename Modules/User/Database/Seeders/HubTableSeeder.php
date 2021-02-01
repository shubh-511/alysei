<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\Hub;

class HubTableSeeder extends Seeder
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
                        array("role_id" => "3", "title" => "Chikago","img_id"=>"",'status'=>'1'),
                        array("role_id" => "7", "title" => "Hongkong","img_id"=>"",'status'=>'1'),
                        array("role_id" => "8", "title" => "Denmark","img_id"=>"",'status'=>'1'),
                        array("role_id" => "9", "title" => "Chikago","img_id"=>"",'status'=>'1'),
                    );

        foreach ($data as $key => $value) {
            $db = Hub::create($value);     
        }
    }
}
