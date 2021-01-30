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
                        array("title" => "Chikago","img_id"=>"",'status'=>'1'),
                    );

        foreach ($data as $key => $value) {
            $db = Hub::create($value);     
        }
    }
}
