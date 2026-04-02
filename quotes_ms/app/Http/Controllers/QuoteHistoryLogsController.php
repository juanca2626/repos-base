<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\QuoteHistoryLog;
use App\Models\TypeClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class QuoteHistoryLogsController extends Controller
{
    public function index($quote_id, Request $request)
    {
        $paging = $request->input('page') ? $request->input('page') : 1;
        $limit = ($request->has('limit')) ? $request->input('limit') : 10;
        $querySearch = $request->input('query');
        $lang = $request->input('lang');
        $filter_by = ($request->has('filter_by')) ? $request->input('filter_by') : "";

        $language_id = Language::where('iso', $lang)->first()->id;

        $quote_history_logs = QuoteHistoryLog::where('quote_id', $quote_id)
            ->with([
                'user',
            ]);

        if ($querySearch) {
            $quote_history_logs = $quote_history_logs->where(function ($query) use ($querySearch) {
                $query->orWhere('slug', 'like', '%'.$querySearch.'%');
                $query->orWhere('description', 'like', '%'.$querySearch.'%');
                $query->orWhere('previous_data', 'like', '%'.$querySearch.'%');
                $query->orWhere('current_data', 'like', '%'.$querySearch.'%');
            });
        }

        if ($filter_by) {
            $quote_history_logs = $quote_history_logs->where('type', $filter_by);
        }

        $count = $quote_history_logs->count();
        if ($paging === 1) {
            $quote_history_logs = $quote_history_logs->orderBy('created_at', 'desc')->take($limit)->get();
        } else {
            $quote_history_logs = $quote_history_logs->orderBy('created_at', 'desc')->skip($limit * ($paging - 1))
                ->take($limit)->get();
        }

        $quote_history_logs = $quote_history_logs->transform(function ($item) use ($language_id) {

            $item['current_data_json'] = '';
            $item['previous_data_json'] = '';

            if ($item['slug'] == 'copy_category') {
                $category = TypeClass::where('id', (int)$item['previous_data'])
                ->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->where('type', 'typeclass');
                        $query->where('language_id', $language_id);
                    }
                ])->first();
                $item['previous_data'] = ($category) ? $category->translations[0]->value : '';
            }
            if ($item['slug'] == 'destroy_category' or $item['slug'] == 'store_category' or $item['slug'] == 'copy_category') {
                $category = TypeClass::where('id', (int)$item['current_data'])
                ->with([
                    'translations' => function ($query) use ($language_id) {
                        $query->where('type', 'typeclass');
                        $query->where('language_id', $language_id);
                    }
                ])->first();
                $item['current_data'] = ($category) ? $category->translations[0]->value : '';
            }

            if ($item['slug'] == 'destroy_service' || $item['slug'] == 'replace_service' ||
                $item['slug'] == 'store_service' || $item['slug'] == 'store_extension' ||
                $item['slug'] == 'store_flight' || $item['slug'] == 'update_date' ||
                $item['slug'] == 'update_service_paxs' || $item['slug'] == 'update_occupation') {
                $item['current_data_json'] =
                    (json_decode($item['current_data'])) ? json_decode($item['current_data']) : '';
                if ($item['current_data_json'] != '') {
                    $item['current_data_json']->quote_category_name = '';
                    $category = TypeClass::where('id', $item['current_data_json']->type_class_id)
                        ->with([
                            'translations' => function ($query) use ($language_id) {
                                $query->where('type', 'typeclass');
                                $query->where('language_id', $language_id);
                            }
                        ])->first();
                    $item['current_data_json']->quote_category_name = ($category) ? $category->translations[0]->value : '';
                }
            }
            if ($item['slug'] == 'replace_service' || $item['slug'] == 'update_service_paxs' ||
                $item['slug'] == 'update_occupation') {
                $item['previous_data_json'] =
                    (json_decode($item['previous_data'])) ? json_decode($item['previous_data']) : '';
            }

            return $item;
        });

        $data = [
            'data'    => $quote_history_logs,
            'count'   => $count,
            'success' => true
        ];

        return Response::json($data);
    }



}
