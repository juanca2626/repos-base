<?php

namespace App\Http\Controllers;

use App\Http\Traits\ManageSearchHotel;
use App\Http\Traits\Package;
use Illuminate\Http\Request;

class ManageSearchHotelsController extends Controller
{
    use ManageSearchHotel, Package;

    public function index(Request $request, $token_search)
    {
        $hotels = $this->getHotelsByTokenSearch($token_search);

        return Response($hotels);
    }

    public function prices(Request $request)
    {
        try
        {
            $category_id = $request->get('category_id');
            $this->calculatePricePackage($category_id);
            return response()->json("actualización de tabla de tarifario", 200);
        }
        catch(\Exception $ex)
        {
            return response()->json($this->throwError($ex));
        }
    }

    public function destinations(Request $request)
    {
        $package_id = $request->get('package_id');
        $this->getDestinationPackage($package_id);
        return response()->json(['success' => true], 200);
    }
}
