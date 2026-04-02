<?php

namespace App\Http\Controllers;

use App\PackageTranslation;
use App\Http\Traits\Translations;
use App\PackageItinerary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PackageTranslationsController extends Controller
{
    use Translations;

    public function __construct()
    {
        $this->middleware('permission:packagetranslations.read')->only('index');
        $this->middleware('permission:packagetranslations.create')->only('store');
        $this->middleware('permission:packagetranslations.update')->only('update');
        $this->middleware('permission:packagetranslations.delete')->only('destroy');
    }

    /**
     * @param $package_id
     * @return JsonResponse
     */
    public function index(Request $request, $package_id)
    {
        
        $translations = PackageTranslation::with([
            'language'
        ])
        ->where('package_id', $package_id)->get();


        foreach ($translations as $translation) {
            $links = PackageItinerary::with([
                'language'
            ])->where('package_id', $package_id)->where('language_id',$translation->language_id)->get();

            foreach ($links as $link) {
                $link->selected = false;
            }
            $translation->link_itinerary = $links;
            
        }

        $data = [
            'data' => $translations,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update($package_id, $translation_id, Request $request)
    {
        $packageTranslation = PackageTranslation::find($translation_id);
        $packageTranslation->name = $request->input('name');
        $packageTranslation->tradename = $request->input('tradename');
        $packageTranslation->description = $request->input('description');
        $packageTranslation->description_commercial = $request->input('description_commercial');
        $packageTranslation->label = $request->input('label');
        // $packageTranslation->itinerary_link = $request->input('itinerary_link');
        // $packageTranslation->itinerary_link_commercial = $request->input('itinerary_link_commercial');
        $packageTranslation->itinerary_label = $request->input('itinerary_label');
        $packageTranslation->itinerary_description = $request->input('itinerary_description');
        $packageTranslation->itinerary_commercial = $request->input('itinerary_commercial');
        $packageTranslation->inclusion = $request->input('inclusion');
        $packageTranslation->restriction = $request->input('restriction');
        $packageTranslation->restriction_commercial = $request->input('restriction_commercial');
        $packageTranslation->policies = $request->input('policies');
        $packageTranslation->policies_commercial = $request->input('policies_commercial');
        $packageTranslation->save();

        $dataPackageLink = $request->input("link_itinerary");

        foreach($dataPackageLink as $value){                   
            PackageItinerary::where('package_id',$value['package_id'])->where('language_id', '=', $value['language_id'])->delete();            
        }              
       
        foreach($dataPackageLink as $value){

            $packageItinerary = new PackageItinerary();
            $packageItinerary->year = $value['year'] ? $value['year'] : '';
            $packageItinerary->package_id = $value['package_id'] ? $value['package_id'] : '';
            $packageItinerary->itinerary_link = $value['itinerary_link'] ? $value['itinerary_link'] : '';
            $packageItinerary->itinerary_link_commercial = $value['itinerary_link_commercial'] ? $value['itinerary_link_commercial'] : '';
            $packageItinerary->link_itinerary_priceless = $value['link_itinerary_priceless'] ? $value['link_itinerary_priceless'] : '';
            $packageItinerary->language_id = $value['language_id'] ? $value['language_id'] : '';
            $packageItinerary->save();            
                                               
        }

        $response = ['success' => true, 'object_id' => $packageTranslation->id];

        return Response::json($response);
    }
  
}
