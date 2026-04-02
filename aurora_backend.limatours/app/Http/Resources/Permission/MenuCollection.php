<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\App;

class MenuCollection extends ResourceCollection
{

	const APP_ECOMMERCE =  1;
	const APP_ADMINISTRACIÓN =  2;
	const APP_GESTIÓN =  3;
	const APP_AUXILIARES =  4;
	const APP_NEGOCIACIONES =  5;
	const APP_OPERACIONES = 6;

	/**
	 * Transform the resource collection into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	public function toArray($request)
	{
		$lang = App::getLocale();

		return $this->collection->transform(function ($row, $key) use ($lang) {
			return [
				'id'                 => $row->id,
				'lang'                => $lang,
				'name'               => trans("menu.{$row->name}"),
				'icon'               => $row->icon,
				'children'           => $row->menus->transform(function($menu) {
                    return [
                        'id'   => $menu->id,
                        'name' => trans("menu.{$menu->name}"),
                        'icon' => $menu->icon,
                        'app_id' => $menu->app_id,
                        'path' => $menu->path,
                        'children' => $menu->sub_menus->transform(function($subMenu) {
							return [
								'id' => $subMenu->id,
								'name' => $subMenu->name, /**Todo  trans("menu.{$subMenu->name}")*/
								'path' => $subMenu->path,
								'icon' => $subMenu->icon,
							];
						})
                    ];
                }),

			];
		});
	}
}
