<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// @codingStandardsIgnoreLine
class UpdateIsoColumnTableCountries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE countries set iso='AD' where id=145");
        DB::update("UPDATE countries set iso='AE' where id=93");
        DB::update("UPDATE countries set iso='AF' where id=144");
        DB::update("UPDATE countries set iso='AG' where id=147");
        DB::update("UPDATE countries set iso='AI' where id=4");
        DB::update("UPDATE countries set iso='AL' where id=114");
        DB::update("UPDATE countries set iso='AM' where id=6");
        DB::update("UPDATE countries set iso='AN' where id=207");
        DB::update("UPDATE countries set iso='AO' where id=119");
        DB::update("UPDATE countries set iso='AR' where id=5");
        DB::update("UPDATE countries set iso='AT' where id=2");
        DB::update("UPDATE countries set iso='AU' where id=1");
        DB::update("UPDATE countries set iso='AW' where id=142");
        DB::update("UPDATE countries set iso='AZ' where id=3");
        DB::update("UPDATE countries set iso='BA' where id=79");
        DB::update("UPDATE countries set iso='BB' where id=128");
        DB::update("UPDATE countries set iso='BD' where id=149");
        DB::update("UPDATE countries set iso='BE' where id=9");
        DB::update("UPDATE countries set iso='BF' where id=156");
        DB::update("UPDATE countries set iso='BG' where id=11");
        DB::update("UPDATE countries set iso='BH' where id=127");
        DB::update("UPDATE countries set iso='BI' where id=157");
        DB::update("UPDATE countries set iso='BJ' where id=151");
        DB::update("UPDATE countries set iso='BM' where id=10");
        DB::update("UPDATE countries set iso='BN' where id=155");
        DB::update("UPDATE countries set iso='BO' where id=123");
        DB::update("UPDATE countries set iso='BR' where id=12");
        DB::update("UPDATE countries set iso='BS' where id=80");
        DB::update("UPDATE countries set iso='BT' where id=152");
        DB::update("UPDATE countries set iso='BW' where id=100");
        DB::update("UPDATE countries set iso='BY' where id=7");
        DB::update("UPDATE countries set iso='BZ' where id=8");
        DB::update("UPDATE countries set iso='CA' where id=32");
        DB::update("UPDATE countries set iso='CD' where id=165");
        DB::update("UPDATE countries set iso='CG' where id=112");
        DB::update("UPDATE countries set iso='CH' where id=66");
        DB::update("UPDATE countries set iso='CI' where id=168");
        DB::update("UPDATE countries set iso='CK' where id=166");
        DB::update("UPDATE countries set iso='CL' where id=81");
        DB::update("UPDATE countries set iso='CM' where id=31");
        DB::update("UPDATE countries set iso='CN' where id=35");
        DB::update("UPDATE countries set iso='CO' where id=82");
        DB::update("UPDATE countries set iso='CR' where id=36");
        DB::update("UPDATE countries set iso='CU' where id=113");
        DB::update("UPDATE countries set iso='CV' where id=159");
        DB::update("UPDATE countries set iso='CY' where id=33");
        DB::update("UPDATE countries set iso='CZ' where id=65");
        DB::update("UPDATE countries set iso='DE' where id=18");
        DB::update("UPDATE countries set iso='DJ' where id=169");
        DB::update("UPDATE countries set iso='DK' where id=22");
        DB::update("UPDATE countries set iso='DO' where id=138");
        DB::update("UPDATE countries set iso='DZ' where id=98");
        DB::update("UPDATE countries set iso='EC' where id=103");
        DB::update("UPDATE countries set iso='EE' where id=68");
        DB::update("UPDATE countries set iso='EG' where id=23");
        DB::update("UPDATE countries set iso='EH' where id=242");
        DB::update("UPDATE countries set iso='ER' where id=173");
        DB::update("UPDATE countries set iso='ES' where id=28");
        DB::update("UPDATE countries set iso='ET' where id=121");
        DB::update("UPDATE countries set iso='FI' where id=63");
        DB::update("UPDATE countries set iso='FJ' where id=176");
        DB::update("UPDATE countries set iso='FO' where id=175");
        DB::update("UPDATE countries set iso='FR' where id=64");
        DB::update("UPDATE countries set iso='GA' where id=180");
        DB::update("UPDATE countries set iso='GB' where id=13");
        DB::update("UPDATE countries set iso='GD' where id=184");
        DB::update("UPDATE countries set iso='GE' where id=21");
        DB::update("UPDATE countries set iso='GG' where id=186");
        DB::update("UPDATE countries set iso='GH' where id=105");
        DB::update("UPDATE countries set iso='GI' where id=143");
        DB::update("UPDATE countries set iso='GL' where id=94");
        DB::update("UPDATE countries set iso='GM' where id=181");
        DB::update("UPDATE countries set iso='GN' where id=187");
        DB::update("UPDATE countries set iso='GP' where id=17");
        DB::update("UPDATE countries set iso='GQ' where id=172");
        DB::update("UPDATE countries set iso='GR' where id=20");
        DB::update("UPDATE countries set iso='GT' where id=185");
        DB::update("UPDATE countries set iso='GW' where id=188");
        DB::update("UPDATE countries set iso='GY' where id=189");
        DB::update("UPDATE countries set iso='HK' where id=73");
        DB::update("UPDATE countries set iso='HN' where id=137");
        DB::update("UPDATE countries set iso='HR' where id=71");
        DB::update("UPDATE countries set iso='HT' where id=16");
        DB::update("UPDATE countries set iso='HU' where id=14");
        DB::update("UPDATE countries set iso='ID' where id=74");
        DB::update("UPDATE countries set iso='IE' where id=27");
        DB::update("UPDATE countries set iso='IL' where id=24");
        DB::update("UPDATE countries set iso='IM' where id=131");
        DB::update("UPDATE countries set iso='IN' where id=25");
        DB::update("UPDATE countries set iso='IQ' where id=140");
        DB::update("UPDATE countries set iso='IR' where id=26");
        DB::update("UPDATE countries set iso='IS' where id=83");
        DB::update("UPDATE countries set iso='IT' where id=29");
        DB::update("UPDATE countries set iso='JE' where id=193");
        DB::update("UPDATE countries set iso='JM' where id=132");
        DB::update("UPDATE countries set iso='JO' where id=75");
        DB::update("UPDATE countries set iso='JP' where id=70");
        DB::update("UPDATE countries set iso='KE' where id=97");
        DB::update("UPDATE countries set iso='KG' where id=34");
        DB::update("UPDATE countries set iso='KH' where id=158");
        DB::update("UPDATE countries set iso='KI' where id=195");
        DB::update("UPDATE countries set iso='KM' where id=164");
        DB::update("UPDATE countries set iso='KN' where id=219");
        DB::update("UPDATE countries set iso='KP' where id=84");
        DB::update("UPDATE countries set iso='KR' where id=69");
        DB::update("UPDATE countries set iso='KW' where id=37");
        DB::update("UPDATE countries set iso='KZ' where id=30");
        DB::update("UPDATE countries set iso='LA' where id=196");
        DB::update("UPDATE countries set iso='LB' where id=99");
        DB::update("UPDATE countries set iso='LC' where id=220");
        DB::update("UPDATE countries set iso='LI' where id=126");
        DB::update("UPDATE countries set iso='LK' where id=120");
        DB::update("UPDATE countries set iso='LR' where id=198");
        DB::update("UPDATE countries set iso='LS' where id=197");
        DB::update("UPDATE countries set iso='LT' where id=40");
        DB::update("UPDATE countries set iso='LU' where id=41");
        DB::update("UPDATE countries set iso='LV' where id=38");
        DB::update("UPDATE countries set iso='LY' where id=39");
        DB::update("UPDATE countries set iso='MA' where id=104");
        DB::update("UPDATE countries set iso='MC' where id=44");
        DB::update("UPDATE countries set iso='MD' where id=43");
        DB::update("UPDATE countries set iso='ME' where id=226");
        DB::update("UPDATE countries set iso='MG' where id=134");
        DB::update("UPDATE countries set iso='MK' where id=85");
        DB::update("UPDATE countries set iso='ML' where id=133");
        DB::update("UPDATE countries set iso='MM' where id=205");
        DB::update("UPDATE countries set iso='MN' where id=139");
        DB::update("UPDATE countries set iso='MQ' where id=201");
        DB::update("UPDATE countries set iso='MR' where id=108");
        DB::update("UPDATE countries set iso='MT' where id=86");
        DB::update("UPDATE countries set iso='MU' where id=202");
        DB::update("UPDATE countries set iso='MV' where id=200");
        DB::update("UPDATE countries set iso='MW' where id=125");
        DB::update("UPDATE countries set iso='MX' where id=42");
        DB::update("UPDATE countries set iso='MY' where id=76");
        DB::update("UPDATE countries set iso='MZ' where id=117");
        DB::update("UPDATE countries set iso='NA' where id=102");
        DB::update("UPDATE countries set iso='NC' where id=208");
        DB::update("UPDATE countries set iso='NE' where id=210");
        DB::update("UPDATE countries set iso='NF' where id=212");
        DB::update("UPDATE countries set iso='NG' where id=115");
        DB::update("UPDATE countries set iso='NI' where id=209");
        DB::update("UPDATE countries set iso='NL' where id=19");
        DB::update("UPDATE countries set iso='NO' where id=46");
        DB::update("UPDATE countries set iso='NP' where id=107");
        DB::update("UPDATE countries set iso='NR' where id=206");
        DB::update("UPDATE countries set iso='NZ' where id=45");
        DB::update("UPDATE countries set iso='OM' where id=213");
        DB::update("UPDATE countries set iso='PA' where id=124");
        DB::update("UPDATE countries set iso='PE' where id=89");
        DB::update("UPDATE countries set iso='PF' where id=178");
        DB::update("UPDATE countries set iso='PG' where id=88");
        DB::update("UPDATE countries set iso='PH' where id=90");
        DB::update("UPDATE countries set iso='PK' where id=87");
        DB::update("UPDATE countries set iso='PL' where id=47");
        DB::update("UPDATE countries set iso='PM' where id=221");
        DB::update("UPDATE countries set iso='PN' where id=215");
        DB::update("UPDATE countries set iso='PR' where id=246");
        DB::update("UPDATE countries set iso='PT' where id=48");
        DB::update("UPDATE countries set iso='PY' where id=110");
        DB::update("UPDATE countries set iso='QA' where id=216");
        DB::update("UPDATE countries set iso='RE' where id=49");
        DB::update("UPDATE countries set iso='RO' where id=72");
        DB::update("UPDATE countries set iso='RU' where id=50");
        DB::update("UPDATE countries set iso='RW' where id=217");
        DB::update("UPDATE countries set iso='SA' where id=91");
        DB::update("UPDATE countries set iso='SB' where id=228");
        DB::update("UPDATE countries set iso='SC' where id=109");
        DB::update("UPDATE countries set iso='SE' where id=67");
        DB::update("UPDATE countries set iso='SG' where id=77");
        DB::update("UPDATE countries set iso='SH' where id=218");
        DB::update("UPDATE countries set iso='SI' where id=53");
        DB::update("UPDATE countries set iso='SK' where id=52");
        DB::update("UPDATE countries set iso='SL' where id=227");
        DB::update("UPDATE countries set iso='SM' where id=224");
        DB::update("UPDATE countries set iso='SN' where id=135");
        DB::update("UPDATE countries set iso='SO' where id=229");
        DB::update("UPDATE countries set iso='SR' where id=54");
        DB::update("UPDATE countries set iso='SS' where id=232");
        DB::update("UPDATE countries set iso='ST' where id=225");
        DB::update("UPDATE countries set iso='SV' where id=51");
        DB::update("UPDATE countries set iso='SY' where id=106");
        DB::update("UPDATE countries set iso='SZ' where id=234");
        DB::update("UPDATE countries set iso='TC' where id=58");
        DB::update("UPDATE countries set iso='TD' where id=130");
        DB::update("UPDATE countries set iso='TG' where id=136");
        DB::update("UPDATE countries set iso='TH' where id=92");
        DB::update("UPDATE countries set iso='TJ' where id=56");
        DB::update("UPDATE countries set iso='TK' where id=235");
        DB::update("UPDATE countries set iso='TL' where id=171");
        DB::update("UPDATE countries set iso='TM' where id=57");
        DB::update("UPDATE countries set iso='TN' where id=122");
        DB::update("UPDATE countries set iso='TO' where id=236");
        DB::update("UPDATE countries set iso='TR' where id=59");
        DB::update("UPDATE countries set iso='TT' where id=237");
        DB::update("UPDATE countries set iso='TV' where id=239");
        DB::update("UPDATE countries set iso='TW' where id=78");
        DB::update("UPDATE countries set iso='TZ' where id=101");
        DB::update("UPDATE countries set iso='UA' where id=62");
        DB::update("UPDATE countries set iso='UG' where id=60");
        DB::update("UPDATE countries set iso='US' where id=55");
        DB::update("UPDATE countries set iso='UY' where id=111");
        DB::update("UPDATE countries set iso='UZ' where id=61");
        DB::update("UPDATE countries set iso='VC' where id=222");
        DB::update("UPDATE countries set iso='VE' where id=95");
        DB::update("UPDATE countries set iso='VG' where id=154");
        DB::update("UPDATE countries set iso='VN' where id=15");
        DB::update("UPDATE countries set iso='VU' where id=240");
        DB::update("UPDATE countries set iso='WF' where id=241");
        DB::update("UPDATE countries set iso='WS' where id=223");
        DB::update("UPDATE countries set iso='YE' where id=243");
        DB::update("UPDATE countries set iso='ZA' where id=141");
        DB::update("UPDATE countries set iso='ZM' where id=116");
        DB::update("UPDATE countries set iso='ZW' where id=96");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update("UPDATE countries set iso=''");
    }
}
