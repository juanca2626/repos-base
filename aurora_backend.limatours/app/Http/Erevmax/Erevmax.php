<?php
/**
 * Created by PhpStorm.
 * User: Jose
 * Date: 4/08/2019
 * Time: 09:42
 */

namespace App\Http\Erevmax;

use App\Http\Erevmax\Traits\Erevmax as ErevmaxTrait;
use Illuminate\Http\Request;

class Erevmax
{
    use ErevmaxTrait;

    /**
     * @param Request $request
     * @return Server|bool
     * @throws \Exception
     */
    public static function server(Request $request)
    {
        if (empty($request->getContent())) {
            return false;
        }

        return new Server($request->getContent());
    }

    /**
     * @param Request $request
     * @return Server|bool
     * @throws \Exception
     */
    public static function client(Request $request)
    {
        if (empty($request->getContent())) {
            return false;
        }

        return new Server($request->getContent());
    }
}
