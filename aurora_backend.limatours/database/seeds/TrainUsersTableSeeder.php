<?php

use Illuminate\Database\Seeder;

class TrainUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user_emails = [
            "jap@limatours.com.pe",
            "pmf@limatours.com.pe",
            "trenescus2@limatours.pe",
            "ovb@limatours.com.pe",
            "nft@limatours.com.pe",
            "kl@limatours.com.pe",
            "agc@limatours.com.pe",
            "gjm@limatours.com.pe",
            "mcp@limatours.com.pe",
            "mgv@limatours.com.pe",
            "kmv@limatours.com.pe",
            "ssc@limatours.com.pe",
            "pbg@limatours.com.pe",
            "dct@limatours.com.pe",
            "kba@limatours.com.pe",
            "spg@limatours.com.pe",
            "aec@limatours.com.pe",
            "ddl@limatours.com.pe",
            "vcv@limatours.com.pe",
            "rbs@limatours.com.pe",
            "isp@limatours.com.pe",
            "aam.limatours@gmail.com",
            "clo@limatours.com.pe",
            "epl@limatours.com.pe",
            "maa@limatours.com.pe",
            "gtb@limatours.com.pe",
            "kcr@limatours.com.pe",
            "hwr@limatours.com.pe",
            "trenescus1@limatours.com.pe",
            "cac@limatours.com.pe",
            "trenescus2@limatours.com.pe",
            "pmr@limatours.com.pe",
            "lzz@limatours.com.pe",
            "alg@limatours.com.pe",
            "prc@limatours.com.pe",
            "mgr@limatours.com.pe",
            "kbm@limatours.com.pe",
            "atn@limatours.com.pe",
            "apac.limatours@gmail.com",
            "pbm@limatours.com.pe",
            "snb@limatours.com.pe",
            "afc@limatours.com.pe",
            "tvr@limatours.com.pe",
            "amm@limatours.com.pe",
            "gmg@limatours.com.pe",
            "destinationservices@limatours.com.pe",
            "liv@limatours.com.pe",
            "meh@limatours.com.pe",
            "jhe@limatours.com.pe",
            "ema@limatours.com.pe",
            "vgg@limatours.com.pe",
            "mcf@limatours.com.pe",
            "clc@limatours.com.pe",
            "sss@limatours.com.pe",
            "phc@limatours.com.pe",
            "svb@limatours.com.pe",
            "fde@limatours.com.pe",
            "nar@limatours.com.pe",
            "mbb@limatours.com.pe",
            "llc@limatours.com.pe",
            "gsg@limatours.com.pe",
            "fcc@limatours.com.pe",
            "ltr@limatours.com.pe",
            "aoc@limatours.com.pe",
            "dbd@limatours.com.pe",
            "ntn@limatours.com.pe",
            "mco@limatours.com.pe",
            "jka@limatours.com.pe",
            "rlc@limatours.com.pe",
            "kec@limatours.com.pe",
            "rdc@limatours.com.pe",
            "cct@limatours.com.pe",
            "pbo@limatours.com.pe",
            "mer@limatours.com.pe",
            "fte@limatours.com.pe",
            "ovc@limatours.com.pe",
            "nce@limatours.com.pe",
            "erm@limatours.com.pe",
            "ang@limatours.com.pe",
            "asc@limatours.com.pe",
            "brs@limatours.com.pe",
            "eas@limatours.com.pe",
            "mhg@limatours.com.pe",
            "osh@limatours.com.pe",
            "kdl@limatours.com.pe",
            "mrs@limatours.com.pe",
            "rgc@limatours.com.pe",
            "cpa@limatours.com.pe",
            "arn@limatours.com.pe",
            "pss@limatours.com.pe",
            "lms@limatours.com.pe",
            "smc@limatours.com.pe",
            "vhe@limatours.com.pe",
            "wnh@limatours.com.pe",
            "hmc@limatours.com.pe",
            "ymc@limatours.com.pe",
            "per@limatours.com.pe",
            "lom@limatours.com.pe",
            "onc@limatours.com.pe",
            "sya@limatours.com.pe",
            "cmz@limatours.com.pe",
            "gaa@limatours.com.pe",
            "mal@limatours.com.pe",
            "mpg@limatours.com.pe",
            "kbg@limatours.com.pe",
            "fhm@limatours.com.pe",
            "sha@limatours.com.pe",
            "stp@limatours.com.pe",
            "arv@limatours.com.pe",
            "saa@limatours.com.pe",
            "ach@limatours.com.pe",
            "mve@limatours.com.pe",
            "apacp1@limatours.com.pe",
            "mpt@limatours.com.pe"
        ];

        $train_id = \App\Train::where('code',"IR")->first();

        foreach ( $user_emails as $u_m ){
            $user = \App\User::where('email',$u_m);
            if( $user->count() > 0 ){
                $user = $user->first();
                $new_train_user = new \App\TrainUser;
                $new_train_user->train_id = $train_id->id;
                $new_train_user->user_id = $user->id;
                $new_train_user->save();
            }
        }
    }
}
