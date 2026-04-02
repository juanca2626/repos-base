<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

// @codingStandardsIgnoreLine
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(PermissionsRolesTableSeeder::class);
        //$this->call(LanguagesTableSeeder::class);
        $this->call(UserTypesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(DistrictsTableSeeder::class);
        $this->call(ZonesTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(HotelCategoriesTableSeeder::class);
        $this->call(TypesClassTableSeeder::class);
        //$this->call(ChainsTableSeeder::class);
//        $this->call(CurrenciesTableSeeder::class);
        $this->call(MealsTableSeeder::class);
        //$this->call(AmenitiesTableSeeder::class);
        $this->call(FacilitiesTableSeeder::class);
        $this->call(HotelTypesTableSeeder::class);
        $this->call(ChannelsTableSeeder::class);
        $this->call(RoomTypesTableSeeder::class);
        $this->call(HotelsTableSeeder::class);

//        $this->call(TaxesTableSeeder::class);
//        $this->call(TranslationsTableSeeder::class);
//        $this->call(GaleriesTableSeeder::class);
        $this->call(RoomsTableSeeder::class);
//        $this->call(PhysicalIntensitiesTableSeeder::class);
//        $this->call(TagsTableSeeder::class);
//        $this->call(PackagesTableSeeder::class);
//        $this->call(PackageTranslationTableSeeder::class);
        $this->call(ContactsTableSeeder::class);
        $this->call(ChargeTypesTableSeeder::class);
        $this->call(PoliciesCancelationsTableSeeder::class);
        $this->call(PoliciesRatesTableSeeder::class);
        $this->call(RatesPlansTypesTableSeeder::class);
//        $this->call(CancellationPolicyTableSeeder::class);
        $this->call(PenaltiesTableSeeder::class);

        $this->call(ExperiencesTableSeeder::class);
        $this->call(ServiceCategoriesTableSeeder::class);
        $this->call(ServiceSubCategoriesTableSeeder::class);
        $this->call(UnitsTableSeeder::class);
        $this->call(UnitDurationsTableSeeder::class);
        $this->call(RequirementsTableSeeder::class);
        $this->call(ClassificationsTableSeeder::class);
        $this->call(TypeServicesTableSeeder::class);
        $this->call(ServicesTableSeeder::class);
        $this->call(MarketsTableSeeder::class);
        $this->call(ClientsTableSeeder::class);
        $this->call(HotelTypesTableSeeder::class);
        // $this->call(ClientExecutivesTa   bleSeeder::class);
        // $this->call(ClientSellersTableSeeder::class);
        // $this->call(MarkupsTableSeeder::class);

        Model::reguard();

    }
}
