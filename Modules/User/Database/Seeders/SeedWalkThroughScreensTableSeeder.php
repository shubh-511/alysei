<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedWalkThroughScreensTableSeeder extends Seeder
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
                        array("role_id"=>"3",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"3",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"3",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"3",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"6",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"6",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"6",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"6",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),


                        array("role_id"=>"7",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"7",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"7",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"7",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"8",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"8",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"8",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"8",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"9",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"9",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"9",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"9",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"10",'step'=>'step1','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>1),

                        array("role_id"=>"10",'step'=>'step2','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>2),

                        array("role_id"=>"10",'step'=>'step3','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>3),

                        array("role_id"=>"10",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"0",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"0",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"0",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"0",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),

                        array("role_id"=>"0",'step'=>'step4','title'=>'Lorem Ispum','description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua','order'=>4),
                        

                    );

        foreach ($data as $key => $value) {
            DB::table('walk_through_screens')->insert($value);
        }
    }
}
