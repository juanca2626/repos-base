<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCoverRequest;
use App\Models\Language;
use App\Models\Package;
use App\Services\ImageCoverService;

class ClientServiceController extends Controller
{
    protected $imageService;

    public function __construct(ImageCoverService $imageService)
    {
        $this->imageService = $imageService;
    }


    public function createCoverImage(CreateCoverRequest $request)
    {
        $packageId = $request->get('package_id');
        $clientId = $request->get('client_id');
        $langIso = $request->get('lang');
        $language = Language::where('iso', $langIso)->first();

        $package = Package::with(['translations' => function ($query) use ($language) {
            $query->select(['id', 'package_id', 'name', 'label']);
            $query->where('language_id', $language->id);
        }])->with([
            'package_destinations.state.translations' => function ($query) use ($language) {
                $query->where('type', 'state');
                $query->where('language_id', $language->id);
            },
        ])->with([
            'plan_rates' => function ($query) {
                $query->where('status', 1);
                $query->first();
            },
        ])->where('status', 1)
            ->where('id', $packageId)
            ->first();


        $cover = "https://res.cloudinary.com/litodti/image/upload/packages/{$packageId}/frontpage.jpg";
        $result = $this->imageService->createCoverImage(
            $cover, // portada
            $langIso,// lang
            $package->translations->first()?->name ?? ' - ', // title
            $package->destinations,// destinies
            $package->translations->first()?->label ?? '', // typePackage
            "",// operations
            $clientId,// clientId
            ($package->nights + 1),// days
            $package->plan_rates->first()->date_from,// dateFrom
            $package->plan_rates->first()->date_to,// dateTo
            "admin" // codeUser
        );

        $response = [
            "success"   => $result['success'],
            "url_cover" => $result['image'],
            "image"     => $result['portada'],
        ];

        return response()->json($response);
    }
}
