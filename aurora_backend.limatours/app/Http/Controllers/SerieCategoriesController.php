<?php

namespace App\Http\Controllers;

use App\Language;
use App\SerieCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class SerieCategoriesController extends Controller
{
    public function index($serie_id, Request $request)
    {
        $lang = ($request->input('lang')) ? $request->input('lang') : 'es';
        $language_id = Language::where('iso',$lang)->first()->id;

        $data_categories = SerieCategory::where('serie_id', $serie_id)
            ->with(['type_class.translations'=>function($query) use ($language_id){
                $query->where('language_id',$language_id);
            }])
            ->get();

        return Response::json(['success' => true, 'data'=> $data_categories ]);
    }

    public function update_multiple($serie_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_classes_ids' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false, 'error' => $validator->errors()]);
        } else {

            $type_classes_ids = $request->input('type_classes_ids');

            $categories_with_trashes = SerieCategory::where('serie_id', $serie_id)
                ->orderBy('created_at','desc')->withTrashed()->get();

            foreach ( $type_classes_ids as $type_class_id ){
                $found = 0;
                foreach ( $categories_with_trashes as $category_with_trash ){
                    if( $category_with_trash->type_class_id == $type_class_id ){
                        $found++;
                        if( $category_with_trash->deleted_at !== null ){
                            if( SerieCategory::where('serie_id', $serie_id)
                                    ->where('type_class_id', $type_class_id)->count() === 0 ){
                                $category_with_trash->deleted_at = null;
                                $category_with_trash->save();
                            }
                            break;
                        }
                    }
                }
                if( $found === 0 ){
                    $new_category = new SerieCategory();
                    $new_category->serie_id = $serie_id;
                    $new_category->type_class_id = $type_class_id;
                    $new_category->save();
                }
            }

            SerieCategory::where('serie_id', $serie_id)->whereNotIn('type_class_id', $type_classes_ids)->delete();

            return Response::json(['success' => true]);
        }
    }

}
