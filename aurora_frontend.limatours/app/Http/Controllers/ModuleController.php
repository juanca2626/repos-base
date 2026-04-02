<?php

namespace App\Http\Controllers;

use App\Language;
use App\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $modules = Module::all();

            return response()->json($modules, 200);
        } else {

            return view('modules.module_index');
        }
    }

    public function store(Request $request)
    {
        $module = new Module();
        $module->name = $request->post('name');
        $module->save();

        $languages = Language::select('iso')->pluck('iso');

        foreach ($languages as $iso) {

            if (!File::isDirectory(resource_path( 'lang/'))) {
                File::makeDirectory(resource_path( 'lang/'), 0777, true, true);
            }

            if (!File::isDirectory(resource_path( 'lang/' . $iso))) {
                File::makeDirectory(resource_path( 'lang/'. $iso), 0777, true, true);
            }
            $module_route_translation = resource_path( 'lang/' . $iso . '/' . strtolower($module->name) . '.php');

            if (!is_file($module_route_translation)) {
                $translation_file = fopen($module_route_translation, "x+");
                fwrite($translation_file, "<?php\n\r return [ \n\r 'label'=>[\n], \n 'messages'=>[\n], \n 'validations'=>[\n], \n\r ];");
                fclose($translation_file);
                chmod($module_route_translation, 0777);
            }
        }

        return response()->json(["message"=>"Modulo Guardado Exitosamente"],200);
    }

    public function destroy($id)
    {
        //
    }
    public function translations($id)
    {
        $module = Module::findOrFail($id);
        $name = $module->name;

        return view('modules.module_translations',compact('id','name'));
    }

    public function translations_create($id)
    {
        $module = Module::findOrFail($id);
        $name = $module->name;

        return view('modules.module_translations_form',compact('id','name'));
    }
}
