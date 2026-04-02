<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Auth\CognitoAuthService;
use Src\Modules\File\Domain\Repositories\OpeRepositoryInterface;
use Src\Modules\File\Domain\Repositories\VipRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileRepositoryInterface;
use Src\Shared\Domain\Repositories\CityRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileVipRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\OpeRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\VipRepository;
use Src\Modules\File\Domain\Repositories\CategoryRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileNoteRepositoryInterface;
use Src\Modules\File\Domain\Repositories\SupplierRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileRepository;
use Src\Shared\Domain\Repositories\CountryRepositoryInterface;

use Src\Shared\Domain\Repositories\CurrencyRepositoryInterface;
use Src\Shared\Domain\Repositories\LanguageRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileBalanceRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileNoteOpeRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileServiceRepositoryInterface;
use Src\Modules\File\Domain\Repositories\ServiceZeroRepositoryInterface;
use Src\Shared\Application\Repositories\Eloquent\CityRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileVipRepository;
use Src\Modules\File\Domain\Repositories\FileCategoryRepositoryInterface;
use Src\Modules\File\Domain\Repositories\StatusReasonRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\CategoryRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileNoteRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\SupplierRepository;
use Src\Modules\File\Domain\Repositories\FileDebitNoteRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileHotelRoomRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileItineraryRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FilePassengerRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileStatementRepositoryInterface;
use Src\Modules\File\Domain\Repositories\MasterServiceRepositoryInterface;
use Src\Shared\Domain\Repositories\ServiceTimeRepositoryInterface;
use Src\Shared\Domain\Repositories\TypeServiceRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileCreditNoteRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileMaterTableRepositoryInterface;
use Src\Shared\Application\Repositories\Eloquent\CountryRepository;
use Src\Shared\Domain\Repositories\NotificationRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileMasterTableRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileNoteGeneralRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileServiceUnitRepositoryInterface;
use Src\Shared\Application\Repositories\Eloquent\CurrencyRepository;


use Src\Shared\Application\Repositories\Eloquent\LanguageRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileBalanceRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileNoteOpeRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileServiceRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\ServiceZeroRepository;
use Src\Modules\File\Domain\Repositories\FileAmountReasonRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileCategoryRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\StatusReasonRepository;
use Src\Modules\File\Domain\Repositories\FileHotelRoomUnitRepositoryInterface;


use Src\Modules\File\Domain\Repositories\FileNoteItineraryRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileDebitNoteRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileHotelRoomRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileItineraryRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FilePassengerRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileStatementRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\MasterServiceRepository;
use Src\Modules\File\Domain\Repositories\FileAmountTypeFlagRepositoryInterface;
use Src\Shared\Application\Repositories\Eloquent\ServiceTimeRepository;
use Src\Shared\Application\Repositories\Eloquent\TypeServiceRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileCreditNoteRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileMaterTableRepository;
use Src\Modules\File\Domain\Repositories\FileItineraryFlightRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileReasonStatementRepositoryInterface;
use Src\Shared\Application\Repositories\Eloquent\NotificationRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileMasterTableRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileNoteGeneralRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileServiceUnitRepository;
use Src\Modules\File\Domain\Repositories\FileTemporaryServiceRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileAmountReasonRepository;
use Src\Modules\File\Domain\Repositories\FileRoomAccommodationRepositoryInterface;

use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileHotelRoomUnitRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileNoteItineraryRepository;
use Src\Modules\File\Domain\Repositories\FilePassengerModifyPaxRepositoryInterface;
use Src\Modules\File\Domain\Repositories\FileServiceCompositionRepositoryInterface;

use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileAmountTypeFlagRepository;
use Src\Modules\File\Domain\Repositories\FileNoteExternalHousingRepositoryInterface;
use Src\Shared\Domain\Repositories\ServiceClassificationRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileItineraryFlightRepository;

use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileReasonStatementRepository;
use Src\Modules\File\Domain\Repositories\FileServiceAccommodationRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileTemporaryServiceRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileRoomAccommodationRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FilePassengerModifyPaxRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileServiceCompositionRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileNoteExternalHousingRepository;
use Src\Shared\Application\Repositories\Eloquent\ServiceClassificationRepository;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileServiceAccommodationRepository;
use Src\Modules\File\Domain\Repositories\FileStatementReasonsModificationRepositoryInterface;
use Src\Modules\File\Infrastructure\Persistence\Eloquent\FileStatementReasonsModificationRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OpeRepositoryInterface::class,
            OpeRepository::class
        );

        $this->app->bind(
            FileRepositoryInterface::class,
            FileRepository::class
        );

        $this->app->bind(
            FileVipRepositoryInterface::class,
            FileVipRepository::class
        );

        $this->app->bind(
            FileAmountReasonRepositoryInterface::class,
            FileAmountReasonRepository::class,
        );

        $this->app->bind(
            FileAmountTypeFlagRepositoryInterface::class,
            FileAmountTypeFlagRepository::class,
        );

        $this->app->bind(
            VipRepositoryInterface::class,
            VipRepository::class,
        );

        $this->app->bind(
            LanguageRepositoryInterface::class,
            LanguageRepository::class,
        );
        $this->app->bind(
            CountryRepositoryInterface::class,
            CountryRepository::class,
        );
        $this->app->bind(
            CityRepositoryInterface::class,
            CityRepository::class,
        );
          $this->app->bind(
            ServiceTimeRepositoryInterface::class,
            ServiceTimeRepository::class,
        );
        $this->app->bind(
            CurrencyRepositoryInterface::class,
            CurrencyRepository::class,
        );
         $this->app->bind(
            ServiceClassificationRepositoryInterface::class,
            ServiceClassificationRepository::class,
        );
         $this->app->bind(
            TypeServiceRepositoryInterface::class,
            TypeServiceRepository::class,
        );
        $this->app->bind(
            ServiceZeroRepositoryInterface::class,
            ServiceZeroRepository::class,

        );
        $this->app->bind(
            FileItineraryRepositoryInterface::class,
            FileItineraryRepository::class
        );

        $this->app->bind(
            FileServiceRepositoryInterface::class,
            FileServiceRepository::class
        );

        $this->app->bind(
            FileServiceCompositionRepositoryInterface::class,
            FileServiceCompositionRepository::class
        );

        $this->app->bind(
            FileServiceUnitRepositoryInterface::class,
            FileServiceUnitRepository::class
        );

        $this->app->bind(
            FileHotelRoomRepositoryInterface::class,
            FileHotelRoomRepository::class
        );

        $this->app->bind(
            FileServiceAccommodationRepositoryInterface::class,
            FileServiceAccommodationRepository::class
        );

        $this->app->bind(
            FileRoomAccommodationRepositoryInterface::class,
            FileRoomAccommodationRepository::class
        );

        $this->app->bind(
            FileHotelRoomUnitRepositoryInterface::class,
            FileHotelRoomUnitRepository::class
        );

        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        $this->app->bind(
            StatusReasonRepositoryInterface::class,
            StatusReasonRepository::class
        );

        $this->app->bind(
            FilePassengerModifyPaxRepositoryInterface::class,
            FilePassengerModifyPaxRepository::class
        );

        $this->app->bind(
            FilePassengerRepositoryInterface::class,
            FilePassengerRepository::class
        );

        $this->app->bind(
            FileMasterTableRepositoryInterface::class,
            FileMasterTableRepository::class
        );

        $this->app->bind(
            FileItineraryFlightRepositoryInterface::class,
            FileItineraryFlightRepository::class
        );

         $this->app->bind(
            ServiceZeroRepositoryInterface::class,
            ServiceZeroRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(
            FileReasonStatementRepositoryInterface::class,
            FileReasonStatementRepository::class
        );

        $this->app->bind(
            SupplierRepositoryInterface::class,
            SupplierRepository::class
        );

        $this->app->bind(
            MasterServiceRepositoryInterface::class,
            MasterServiceRepository::class
        );

        $this->app->bind(
            FileTemporaryServiceRepositoryInterface::class,
            FileTemporaryServiceRepository::class
        );

        $this->app->bind(
            FileCategoryRepositoryInterface::class,
            FileCategoryRepository::class
        );

        $this->app->bind(
            FileStatementReasonsModificationRepositoryInterface::class,
            FileStatementReasonsModificationRepository::class
        );

        $this->app->bind(
            FileStatementRepositoryInterface::class,
            FileStatementRepository::class
        );

        $this->app->bind(
            FileCreditNoteRepositoryInterface::class,
            FileCreditNoteRepository::class
        );

        $this->app->bind(
            FileDebitNoteRepositoryInterface::class,
            FileDebitNoteRepository::class
        );

        $this->app->bind(
            FileNoteRepositoryInterface::class,
            FileNoteRepository::class
        );

        $this->app->bind(
            FileNoteItineraryRepositoryInterface::class,
            FileNoteItineraryRepository::class
        );

        $this->app->bind(
            FileNoteGeneralRepositoryInterface::class,
            FileNoteGeneralRepository::class
        );

        $this->app->bind(
            FileNoteExternalHousingRepositoryInterface::class,
            FileNoteExternalHousingRepository::class
        );

        $this->app->singleton(CognitoAuthService::class, function () {
            return new CognitoAuthService();
        });

        $this->app->bind(
            FileNoteOpeRepositoryInterface::class,
            FileNoteOpeRepository::class
        );

        $this->app->bind(
            FileBalanceRepositoryInterface::class,
            FileBalanceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
