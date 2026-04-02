<?php

namespace App\Http\Controllers;

use App\ServiceEquivalenceAssociation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ServiceEquivalenceAssociationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($service_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = $request->input('limit');
        $querySearch = $request->input('query');
        $count = 0;
        $equivalences = ServiceEquivalenceAssociation::with([
            'service' => function ($query) {
                $query->with([
                    'serviceType' => function ($query) {
                        $query->with([
                            'translations' => function ($query) {
                                $query->select('object_id', 'value');
                                $query->where('type', 'servicetype');
                                $query->where('language_id', 1);
                            },
                        ]);
                    }
                ]);
                $query->with([
                    'service_rate' => function ($query) {
                        $query->with([
                            'service_rate_plans' => function ($query) {
//                            $query->where('date_from', '<=', $from);
//                            $query->where('date_to', '>=', $from);
                            }
                        ]);
                    }
                ]);

            }
        ])->where('service_id', $service_id)->get();

        $count = $equivalences->count();

        $data = [
            'data' => $equivalences,
            'count' => $count,
            'success' => true
        ];

        return Response::json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($service_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'equivalence_association_id' => 'required'
        ]);

        if ($validator->fails()) {
            $response = ['success' => false];
        } else {
            $serviceEquiv = ServiceEquivalenceAssociation::where('service_id', $service_id)
                ->where('service_equivalence_id', $request->input('equivalence_association_id'))->get()->first();
            if (!$serviceEquiv) {
                $newEquivalence = new ServiceEquivalenceAssociation();
                $newEquivalence->service_id = $service_id;
                $newEquivalence->service_equivalence_id = $request->input('equivalence_association_id');
                $newEquivalence->save();
                $response = ['success' => true];
            } else {
                $response = ['success' => false, 'object_id' => 'ALREADY_EXISTS'];
            }
        }
        return Response::json($response);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\ServiceEquivalenceAssociation $serviceEquivalenceAssociation
     * @return \Illuminate\Http\Response
     */
    public function destroy($equivalence_association_id)
    {
        $component = ServiceEquivalenceAssociation::find($equivalence_association_id);
        $component->delete();
        return Response::json(['success' => true]);
    }
}
