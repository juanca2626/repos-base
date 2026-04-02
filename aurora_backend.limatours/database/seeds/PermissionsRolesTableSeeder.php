<?php

use Illuminate\Database\Seeder;
use jeremykenedy\LaravelRoles\Models\Role;

// @codingStandardsIgnoreLine
class PermissionsRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::where('slug', '=', 'admin')->first();

        if ($adminRole === null) {
            $adminRole = config('roles.models.role')::create([
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => '',
                'level' => 10,
            ]);
        }

        $userRole = Role::where('slug', '=', 'user')->first();

        if ($userRole === null) {
            $userRole = config('roles.models.role')::create([
                'name' => 'User',
                'slug' => 'user',
                'description' => '',
                'level' => 3,
            ]);
        }

        $modulesCRUD = [
            'Chains',
            'Amenities',
            'HotelCategories',
            'Channels',
            'Cities',
            'Countries',
            'Currencies',
            'Districts',
            'Facilities',
            'HotelTypes',
            'Hotels',
            'Inventories',
            'Galeries',
            'Languages',
            'ManageHotels',
            'Meals',
            'Packages',
            'PackageChildren',
            'PackageCustomers',
            'PackageDestinations',
            'PackageDestinationsDays',
            'PackageImages',
            'PackagePriorities',
            'PackageRates',
            'PackageSchedules',
            'PackageTranslations',
            'Permissions',
            'PhysicalIntensities',
            'Rates',
            'Roles',
            'Rooms',
            'RoomTypes',
            'States',
            'Tags',
            'Taxes',
            'Translations',
            'TypesClass',
            'Users',
            'Zones',
            'Services',
            'Classifications',
            'ServiceCategories',
            'ServiceSubCategories',
            'Experiences',
            'ServiceGalleries',
            'Inclusions',
            'Requirements',
            'Units',
            'UnitDurations',
            'ServiceTypeActivities',
            'Restrictions',
            'Markets',
            'Clients',
            'Contacts',
            'ClientSellers',
            'ChannelUsers',
            'HotelUsers',
            'Markups',
            'PolicyRates',
            'PolicyCancelations',
            // SIG Modules
            'NonConformingProducts',
            'Congratulations',
            'ManagementMonitoring',
            'SuggestionsForImprovement',
            'MaintenanceOfSanctions',
            // Customer Service Modules
            'Claims',
            'Reports',
            // Adventure Modules
            'ACategories',
            'ASettings',
            'Templates',
            'Departures',
            'Entrances',
            'Cash',
            'Programming',
            'Manifest',
            'ACalendar',
            'AServices',
            // Operations Modules
            'OpeManagementReports',
        ];

        foreach ($modulesCRUD as $module) {
            $permissions = config('roles.models.permission')::where('name', 'LIKE', $module . '%')->get();

            if (count($permissions) === 0) {
                config('roles.models.permission')::firstOrCreate([
                    'name' => $module . ': Create',
                    'slug' => strtolower($module) . '.create',
                    'description' => 'Create new ' . $module, // optional
                ]);
                config('roles.models.permission')::firstOrCreate([
                    'name' => $module . ': Read',
                    'slug' => strtolower($module) . '.read',
                    'description' => 'Read new ' . $module, // optional
                ]);
                config('roles.models.permission')::firstOrCreate([
                    'name' => $module . ': Update',
                    'slug' => strtolower($module) . '.update',
                    'description' => 'Update new ' . $module, // optional
                ]);
                config('roles.models.permission')::firstOrCreate([
                    'name' => $module . ': Delete',
                    'slug' => strtolower($module) . '.delete',
                    'description' => 'Delete new ' . $module, // optional
                ]);
            }
        }

        $permissions = config('roles.models.permission')::all();

        foreach ($permissions as $permission) {
            $adminRole->attachPermission($permission);
        }

        $languagePermission = config('roles.models.permission')::where('slug', '=', 'languages.read')->first();
        $userRole->attachPermission($languagePermission);

        $languagePermission = config('roles.models.permission')::where('slug', '=', 'users.update')->first();
        $userRole->attachPermission($languagePermission);

        // Assign SIG module permissions to 'sg' role
        $sgRole = Role::where('slug', '=', 'sg')->first();
        if ($sgRole !== null) {
            $sigModules = [
                'MaintenanceOfSanctions',
                'NonConformingProducts',
                'Congratulations',
                'ManagementMonitoring',
                'SuggestionsForImprovement',
                'Claims',
                'Reports'
            ];

            foreach ($sigModules as $module) {
                $sigPermissions = config('roles.models.permission')::where('name', 'LIKE', $module . '%')->get();
                foreach ($sigPermissions as $permission) {
                    $sgRole->attachPermission($permission);
                }
            }
        }

        // Assign Adventure module permissions to 'adventure' role
        $adventureRole = Role::where('slug', '=', 'adventure')->first();
        if ($adventureRole !== null) {
            $adventureModules = [
                'ACategories',
                'ASettings',
                'Templates',
                'Departures',
                'Entrances',
                'Cash',
                'Programming',
                'Manifest',
                'ACalendar',
                'AServices',
                'OpeManagementReports'
            ];

            foreach ($adventureModules as $module) {
                $adventurePermissions = config('roles.models.permission')::where('name', 'LIKE', $module . '%')->get();
                foreach ($adventurePermissions as $permission) {
                    $adventureRole->attachPermission($permission);
                }
            }
        }
    }
}
