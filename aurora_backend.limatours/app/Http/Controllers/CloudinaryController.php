<?php

namespace App\Http\Controllers;

use App\Client;
use App\Http\Traits\Images;
use Cloudinary\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use JD\Cloudder\Facades\Cloudder;

class CloudinaryController extends Controller
{
    use Images;

    public $environment = 'production';

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function uploadClientLogo(Request $request)
    {
        $extension = $request->file('imagefile')->extension();
        $image_file = $request->file('imagefile')->getRealPath();
        $image_folder = $request->get('imagefolder');
        $image_name = $request->post('imagename');
        $client_code = $request->post('client');
        $image_fullname = $image_name.'.'.$extension;

        $this->validate($request, [
            'imagefile' => 'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);


        $upload = Cloudder::upload(
            $image_file,
            null,
            array(
                "folder" => $image_folder,
                "public_id" => $image_name
            )
        )->getResult();
        $update = Client::where('code', $client_code)
            ->update(['logo' => $upload['url']]);
        $result = [
            'upload' => $upload,
            'update' => $update,
            'logo' => $upload['url'],
            'success' => true,
        ];
        return Response::json($result);
    }

    public function getFolders()
    {
        try {

            $cloudinary_folders = [];

            if(config('app.env') == $this->environment)
            {

                $cacheKey = "cloudinary_cache_folders"; $folders = Cache::get($cacheKey);

                if(empty($folders))
                {
                    try
                    {
                        $cloudinary_folders = Cloudder::subfolders('peru');
                        Cache::put($cacheKey, $cloudinary_folders, now()->addDays(1));
                    }
                    catch(\Exception $ex)
                    {
                    }
                }
                else
                {
                    $cloudinary_folders = $folders;
                }
            }

            return Response::json($cloudinary_folders);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function search(Request $request)
    {
        try {
            $paths = ($request->has('destinations')) ? $request->input('destinations') : 'all';
            $next_cursor = ($request->has('next_page')) ? $request->input('next_page') : '';
            $filter_name = ($request->has('filter')) ? $request->input('filter') : '';
            $filters_query =  (array) (($request->has('filters')) ? $request->input('filters') : []);
            $limit = 50; $lang = $request->has('lang') ? $request->input('lang') : '';

            if ($paths === 'all') {
                $destinations = Cloudder::subfolders('peru');
            } else {
                // Sin duplicados..
                $paths = collect($paths)->unique()->values()->toArray();
                // $destinations = implode(' OR ', $paths);

                $destinations = implode(' OR ', array_map(function ($p) {
                    return "folder=\"$p\"";
                }, $paths));
            }

            $filters = ''; $filters_tags = [];
            if(count($filters_query) > 0)
            {
                foreach ($filters_query as $key => $value)
                {
                    if($value == 1)
                    {
                        $array_filter = [];
                        $array_filter[] = 'tags="' . mb_strtolower($key) . '"';

                        // Sin duplicados..
                        $array_filter = collect($array_filter)->unique()->values()->toArray();
                        $filters_tags[] = '(' . implode(' OR ', $array_filter) . ')';
                    }
                }
            }

            if (count($filters_tags) > 0) {
                $filters_tags = implode(' AND ', $filters_tags);
                $filters = " AND ({$filters_tags})";
            }

            $filter_by_lang = '';
            if($lang)
            {
                $lang = mb_strtolower($lang);
                $filter_by_lang = " AND ((!context:lang) OR context.lang=\"$lang\")";
            }

            $filter_by_name = '';
            if ($filter_name != '') {
                $filter_by_name = ' AND (';

                $variants = collect([
                    mb_strtolower($filter_name),
                ])->unique()->values()->toArray();

                $filter_by_name .= implode(' OR ', array_map(function ($v) {
                    return "filename:$v* OR tags=\"$v\"";
                }, $variants));

                $filter_by_name .= ')';
            }

            $next_cursor = ''; $page = ($request->has('page')) ? $request->input('page') : 0;

            $images = []; $query = "({$destinations}) {$filters} {$filter_by_name} {$filter_by_lang}";

            $flag_cache = false;
            if(config('app.env') == $this->environment)
            {
                $cacheKey = "cloudinary_cache_search_" . md5($query . "_page_" . $page);
                $cache_images = Cache::get($cacheKey);

                if(empty($cache_images))
                {
                    try
                    {
                        Cloudder::getCloudinary();

                        for($i=0;$i<=$page;$i++)
                        {
                            $search = new Search();
                            $search_all = $search->expression($query)
                                // ->sort_by('public_id','desc')
                                ->max_results($limit)
                                ->with_field('context')
                                ->with_field('tags')
                                ->with_field('metadata');

                            if ($next_cursor !== '') {
                                $search_all->next_cursor($next_cursor);
                            }
                            $search_all = $search_all->execute();

                            $images = []; $next_cursor = (isset($search_all['next_cursor'])) ? $search_all['next_cursor'] : '';

                            if (isset($search_all["resources"]) and count($search_all["resources"]) > 0)
                            {
                                $tranfor = $this->verifyCloudinaryImgs($search_all['resources'], 'url', 250, 250);
                                $images['success'] = true;
                                $images['data'] = $tranfor;
                                $images['next_page'] = $next_cursor;
                                $images['total'] = (int)$search_all['total_count'];
                            }
                            else
                            {
                                $images['success'] = false;
                                $images['data'] = [];
                                $images['next_page'] = '';
                                $images['total'] = 0;
                            }
                        }

                        $images['pages'] = ($images['total'] > 0) ? ceil($images['total'] / $limit) : 0;
                        $images['limit'] = $limit;
                        $images['query'] = $query;

                        Cache::put($cacheKey, $images, now()->addDays(1));
                    }
                    catch(\Exception $ex)
                    {
                        $images['error'] = $this->throwError($ex);
                    }
                }
                else
                {
                    $images = $cache_images; $flag_cache = true;
                }
            }

            $images['env'] = config('app.env');
            $images['query'] = $query;
            $images['flag_cache'] = $flag_cache;
            return Response::json($images);
        } catch (\Exception $e) {
            return Response::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function getTags()
    {
        try {
            // $cloudinary_tags = Cloudder::tags(['max_results' => 5000]);
            $folder = 'peru/'; $cloudinary_tags = [];

            if(config('app.env') == $this->environment)
            {

                $cacheKey = "cloudinary_cache_tags"; $tags = Cache::get($cacheKey);

                if(empty($tags))
                {
                    try
                    {
                        // Obtener recursos de la carpeta 'Peru'
                        $resources = Cloudder::resources([
                            'type' => 'upload',
                            'prefix' => $folder,  // Filtro por la carpeta 'Peru'
                            'max_results' => 5000  // Máximo de recursos a obtener
                        ]);

                        $cloudinary_tags = [];

                        // Recorrer los recursos obtenidos y extraer sus tags
                        foreach ($resources['resources'] as $resource) {
                            $publicId = $resource['public_id'];

                            // Obtener los tags del recurso
                            $resourceData = Cloudder::resource($publicId);

                            if(isset($resourceData['tags']))
                            {
                                $tags = $resourceData['tags'];
                                // Almacenar los tags en un array
                                $cloudinary_tags = array_merge($cloudinary_tags, $tags);
                            }
                        }

                        $cloudinary_tags = array_unique($cloudinary_tags);

                        Cache::put($cacheKey, $cloudinary_tags, now()->addDays(1));
                    }
                    catch(\Exception $ex)
                    {
                    }
                }
                else
                {
                    $cloudinary_tags = $tags;
                }
            }

            return Response::json($cloudinary_tags);
        } catch (\Exception $e) {
            return $this->throwError($e);
        }
    }

    public function getImagesHighlights()
    {
        // Si NO es producción, devolver vacío inmediatamente
        if (config('app.env') !== $this->environment) {
            return Response::json([
                'success' => false,
                'data' => [],
                'next_page' => '',
                'total' => 0,
                'message' => 'Esta función solo está disponible en producción',
            ]);
        }

        try {
            $cacheKey = "cloudinary_cache_highligths";
            $forceRefresh = request()->query('refresh') == '1'; // ¿Forzar recarga?

            // Si hay caché y no se fuerza recarga, devolverlo
            if (!$forceRefresh && Cache::has($cacheKey)) {
                return Response::json(Cache::get($cacheKey));
            }

            // Llamada a Cloudinary SOLO en producción
            Cloudder::getCloudinary();
            $search = new Search();
            $search_all = $search->expression("(folder:highlights)")
                ->max_results(250)
                ->execute();

            $images = [];
            if (isset($search_all["resources"]) && count($search_all["resources"]) > 0) {
                $images = [
                    'success' => true,
                    'data' => $this->verifyCloudinaryImgs($search_all['resources'], 'url', 250, 250),
                    'next_page' => $search_all['next_cursor'] ?? '',
                    'total' => (int)$search_all['total_count'],
                ];
            } else {
                $images = [
                    'success' => false,
                    'data' => [],
                    'next_page' => '',
                    'total' => 0,
                ];
            }

            // Guardar en caché (válido por 6 horas)
            Cache::put($cacheKey, $images, now()->addDays(1));

            return Response::json($images);

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }
}
