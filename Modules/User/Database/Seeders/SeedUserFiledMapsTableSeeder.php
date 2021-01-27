<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;
class SeedUserFiledMapsTableSeeder extends Seeder
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
                        array("user_field_id"=>"20",'option_id'=>'686','child_id'=>'8'),
                        array("user_field_id"=>"20",'option_id'=>'687','child_id'=>'24'),
                        array("user_field_id"=>"9",'option_id'=>'650','child_id'=>'21'),
                        array("user_field_id"=>"9",'option_id'=>'651','child_id'=>'22'),
                        array("user_field_id"=>"8",'option_id'=>'649','child_id'=>'15'),
                    );

        foreach ($data as $key => $value) {
            DB::table('user_field_maps')->insert($value);
        }
    }
}
