<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use DB;

class SeedUserFieldMapRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Model::unguard();

        // User Field Id as following
        /*
        1   Company name
        2   Product type
        3   Italian Regions
        4   Horeca
        5   Private Label
        6   Alysei Brand Label
        7   Restaurent name
        8   Hub
        9   Provide Pick Up And/Or Delivery
        10  Restaurent Type
        11  Expertise
        12  Title
        13  Country
        14  Speciality
        15  Zip/Postal Code
        */

        $data = array(
                        array("user_field_id"=>16,'role_id'=>3,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>3,'step'=>1,'order'=>2),
                        array("user_field_id"=>1,'role_id'=>3,'step'=>1,'order'=>3),
                        array("user_field_id"=>2,'role_id'=>3,'step'=>1,'order'=>4),
                        array("user_field_id"=>3,'role_id'=>3,'step'=>1,'order'=>5),
                        array("user_field_id"=>4,'role_id'=>3,'step'=>2,'order'=>6),
                        array("user_field_id"=>5,'role_id'=>3,'step'=>2,'order'=>7),
                        array("user_field_id"=>6,'role_id'=>3,'step'=>2,'order'=>8),

                        array("user_field_id"=>16,'role_id'=>6,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>6,'step'=>1,'order'=>2),
                        array("user_field_id"=>1,'role_id'=>6,'step'=>1,'order'=>3),
                        array("user_field_id"=>2,'role_id'=>6,'step'=>1,'order'=>4),
                        array("user_field_id"=>3,'role_id'=>6,'step'=>1,'order'=>5),
                        array("user_field_id"=>4,'role_id'=>6,'step'=>2,'order'=>6),
                        array("user_field_id"=>5,'role_id'=>6,'step'=>2,'order'=>7),
                        array("user_field_id"=>6,'role_id'=>6,'step'=>2,'order'=>8),

                        array("user_field_id"=>16,'role_id'=>9,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>9,'step'=>1,'order'=>2),
                        array("user_field_id"=>7,'role_id'=>9,'step'=>1,'order'=>3),
                        array("user_field_id"=>8,'role_id'=>9,'step'=>2,'order'=>4),
                        array("user_field_id"=>9,'role_id'=>9,'step'=>2,'order'=>5),
                        array("user_field_id"=>10,'role_id'=>9,'step'=>2,'order'=>6),

                        array("user_field_id"=>16,'role_id'=>8,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>8,'step'=>1,'order'=>2),
                        array("user_field_id"=>1,'role_id'=>8,'step'=>1,'order'=>3),
                        array("user_field_id"=>14,'role_id'=>8,'step'=>1,'order'=>4),
                        array("user_field_id"=>13,'role_id'=>8,'step'=>1,'order'=>5),

                        array("user_field_id"=>16,'role_id'=>10,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>10,'step'=>1,'order'=>2),
                        array("user_field_id"=>18,'role_id'=>10,'step'=>1,'order'=>3),
                        array("user_field_id"=>19,'role_id'=>10,'step'=>1,'order'=>4),
                        array("user_field_id"=>13,'role_id'=>10,'step'=>2,'order'=>5),
                        array("user_field_id"=>15,'role_id'=>10,'step'=>2,'order'=>6),
                        
                        array("user_field_id"=>16,'role_id'=>7,'step'=>1,'order'=>1),
                        array("user_field_id"=>17,'role_id'=>7,'step'=>1,'order'=>2),
                        array("user_field_id"=>18,'role_id'=>7,'step'=>1,'order'=>3),
                        array("user_field_id"=>19,'role_id'=>7,'step'=>1,'order'=>4),
                        array("user_field_id"=>11,'role_id'=>7,'step'=>2,'order'=>5),
                        array("user_field_id"=>12,'role_id'=>7,'step'=>2,'order'=>6),
                        array("user_field_id"=>13,'role_id'=>7,'step'=>2,'order'=>7)


                    );

        foreach ($data as $key => $value) {
            DB::table('user_field_map_roles')->insert($value);
        }
    }
}
