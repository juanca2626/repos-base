<?php

namespace App\Http\Controllers;

use App\Language;
use App\Module;
use App\TranslationFrontend;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Aza\Components\PhpGen\PhpGen;
use Illuminate\Filesystem\Filesystem;
use function foo\func;

class TranslationFrontendController extends Controller
{
    public function index($module_id)
    {
        $slugs = TranslationFrontend::where('module_id',$module_id)->whereHas('language', function ($q) {
        $q->where('state', 1);
    })->with(['language'=>function($query){
            $query->where('state',1);
        }])->get()->groupBy('slug');

        $rows = [];

        foreach ($slugs as $slug => $translations) {
            array_push($rows, [
                "slug" => $slug,
                "field"=>true,
                "translation" => null,
                "_children" => []
            ]);

            foreach ($translations as $translation) {
                array_push($rows[count($rows) - 1]["_children"], [
                    "slug" => $translation["language"]["iso"],
                    "language_id" => $translation["language_id"],
                    "translation" => $translation["value"],
                    "translate" =>true
                ]);
            }
        }

        return  response()->json($rows,200);
    }

    public function store(Request $request,$module_id)
    {
        $slugs = $request->post('translations');

        DB::transaction(function () use ($slugs,$module_id) {
            foreach ($slugs as $slug) {
                foreach ($slug["_children"] as $translation)
                {
                    DB::table('translation_frontends')->insert([
                        'module_id' => $module_id,
                        'slug' => $slug["slug"],
                        'language_id' => $translation["language_id"],
                        'value' => $translation["translation"],
                        'created_at' => date("Y-m-d H:i:s"),
                    ]);
                }
            }
        });

        $languages = Language::all();

        $module_name = Module::find($module_id)->name;

        $module_translations_array = null;

        $translations = TranslationFrontend::where('module_id', $module_id)->get()->groupBy('language_id');

        foreach ($languages as $language) {
            $fileSystem = new Filesystem();
            $module_route_translation = resource_path( 'lang/' . $language->iso . '/' . strtolower($module_name) . '.php');
            $module_translations_array = $fileSystem->getRequire($module_route_translation);

            foreach ($translations[$language->id] as $translation) {
                Arr::set($module_translations_array, $translation["slug"], $translation["value"]);
            }
            $phpGen = PhpGen::instance();

            $file = new Filesystem();

            $content = "<?php\n\r return " . $phpGen->getCode($module_translations_array) . PHP_EOL;
            $file->put($module_route_translation, $content);
        }

        return response()->json(['message' => "Traducciones Agregadas Correctamente"], 200);
    }

    public function update(Request $request, $module_id)
    {
        $slugs = $request->post('translations');

        DB::transaction(function () use ($slugs,$module_id) {
            DB::table('translation_frontends')->where('module_id',$module_id)->delete();
            foreach ($slugs as $slug)
            {
                foreach ($slug["_children"] as $translation)
                {
                    DB::table('translation_frontends')->insert([
                        'module_id' => $module_id,
                        'slug' => $slug["slug"],
                        'language_id' => $translation["language_id"],
                        'value' => $translation["translation"],
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s"),
                    ]);
                }
            }
        });

        $languages = Language::where('state',1)->get();
        $language_ids = Language::where('state',1)->pluck('id');
        $module_name = Module::find($module_id)->name;

        $module_translations_array = null;

        $translations = TranslationFrontend::where('module_id', $module_id)->whereIn('language_id',$language_ids)->get()->groupBy('language_id');

        foreach ($languages as $language) {
            $fileSystem = new Filesystem();
            $module_route_translation = resource_path( 'lang/' . $language->iso . '/' . strtolower($module_name) . '.php');
            $module_translations_array = $fileSystem->getRequire($module_route_translation);

            foreach ($translations[$language->id] as $translation) {
                Arr::set($module_translations_array, $translation["slug"], $translation["value"]);
            }
            $phpGen = PhpGen::instance();

            $file = new Filesystem();

            $content = "<?php\n\r return " . $phpGen->getCode($module_translations_array) . PHP_EOL;
            $file->put($module_route_translation, $content);
        }

        return response()->json(['message' => "Traducciones Actualizadas Correctamente"], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function getBySlug($lang = 'es', $slug = 'global')
    {
        try {
            // Validar y asignar valores predeterminados si son "null"
            $lang = ($lang === 'null' || $lang === 'undefined' || is_null($lang)) ? 'es' : $lang;
            $slug = ($slug === 'null' || $lang === 'undefined' || is_null($slug)) ? 'global' : $slug;

            $fileSystem = new Filesystem();
            $module_route_translation = resource_path('lang/' . $lang . '/' . $slug . '.php');

            // Verificar si el archivo existe
            if (!$fileSystem->exists($module_route_translation)) {
                return response()->json(['error' => 'Translation file not found'], 404);
            }

            // Cargar el archivo si existe
            $module_translations_array = $fileSystem->getRequire($module_route_translation);
            return response()->json($module_translations_array, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
