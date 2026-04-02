<?php


use Illuminate\Database\Migrations\Migration;

// @codingStandardsIgnoreLine
class UpdateIsoColumnStates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update("UPDATE states set iso='AMA' where id=1596");
        DB::update("UPDATE states set iso='ANC' where id=1597");
        DB::update("UPDATE states set iso='APU' where id=1598");
        DB::update("UPDATE states set iso='ARE' where id=1599");
        DB::update("UPDATE states set iso='AYA' where id=1600");
        DB::update("UPDATE states set iso='CAJ' where id=1601");
        DB::update("UPDATE states set iso='CAL' where id=1602");
        DB::update("UPDATE states set iso='CUS' where id=1603");
        DB::update("UPDATE states set iso='HUV' where id=1604");
        DB::update("UPDATE states set iso='HUC' where id=1605");
        DB::update("UPDATE states set iso='ICA' where id=1606");
        DB::update("UPDATE states set iso='JUN' where id=1607");
        DB::update("UPDATE states set iso='LAL' where id=1608");
        DB::update("UPDATE states set iso='LAM' where id=1609");
        DB::update("UPDATE states set iso='LIM' where id=1610");
        DB::update("UPDATE states set iso='LOR' where id=1611");
        DB::update("UPDATE states set iso='MDD' where id=1612");
        DB::update("UPDATE states set iso='MOQ' where id=1613");
        DB::update("UPDATE states set iso='PAS' where id=1614");
        DB::update("UPDATE states set iso='PIU' where id=1615");
        DB::update("UPDATE states set iso='PUN' where id=1616");
        DB::update("UPDATE states set iso='SAM' where id=1617");
        DB::update("UPDATE states set iso='TAC' where id=1618");
        DB::update("UPDATE states set iso='TUM' where id=1619");
        DB::update("UPDATE states set iso='UCA' where id=1620");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update('UPDATE states set iso="" where country_id=89');
    }
}
