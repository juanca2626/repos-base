<?php

namespace App\Http\Traits;

use claviska\SimpleImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

trait Images
{
    /**
     * @param $userID
     * @param $module
     * @param Request $request
     * @param string $ext
     * @return array
     */
    public function imagesSaveTmp($userID, $module, Request $request, $ext = 'png')
    {
        $image = $request->file('file');
        $name = $module . '_' . md5($userID);
        $nameFull = $name . "." . $image->getClientOriginalExtension();
        $image->move(public_path() . '/tmp/images/', $nameFull);
        $message = '';

        try {
            $SimpleImage = new SimpleImage();
            $SimpleImage->fromFile(public_path() . '/tmp/images/' . $nameFull)
                //->bestFit(240, 240)
                ->toFile(public_path() . '/tmp/images/' . $name . '.' . $ext, 'image/' . $ext);
            $name = '/tmp/images/' . $name . "." . $ext;

            $success = true;
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return ['success' => $success, 'name' => $name, 'message' => $message];
    }

    /**
     * @param $userID
     * @param $module
     * @param $objectID
     * @param string $ext
     */
    public function imagesSave($userID, $module, $objectID, $ext = 'png')
    {
        if (File::exists(public_path() . '/tmp/images/' . $module . '_' . md5($userID) . '.' . $ext) === true) {
            File::move(
                public_path() . '/tmp/images/' . $module . '_' . md5($userID) . '.' . $ext,
                public_path() . '/images/' . $module . '/' . md5($objectID) . '.' . $ext
            );
        }
    }

    public function imagesSaveRoom($userID, $module, $objectID, $ext = 'png')
    {
        if (File::exists(public_path() . '/tmp/images/' . $module . '_' . md5($userID) . '.' . $ext) === true) {
            File::move(
                public_path() . '/tmp/images/' . $module . '_' . md5($userID) . '.' . $ext,
                public_path() . '/images/' . $module . '/' . md5($objectID) . '.' . $ext
            );
        }
    }

    /**
     * @param $module
     * @param $objectID
     * @param string $ext
     * @return bool
     */
    public function imagesExists($module, $objectID, $ext = 'png')
    {
        $response = false;
        $imageName = $module . '/' . md5($objectID) . '.' . $ext;
        $imagePath = public_path() . '/images/' . $imageName;
        if (File::exists($imagePath) === true) {
            $image = getimagesize($imagePath);
            $response = [
                'name' => $imageName,
                'size' => filesize($imagePath),
                'type' => $image['mime']
            ];
        }

        return $response;
    }

    public function imagesRemove($module, $objectID, $ext = 'png')
    {
        unlink(public_path() . '/images/' . $module . '/' . md5($objectID) . '.' . $ext);
    }

    /**
     * @param $var / texto
     * @param $w
     * @param $h
     * @param $request 'link' / 'nom'
     * @return string
     */
    public function verifyCloudinaryImg($var, $w, $h, $request)
    {
        //https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg
        //Default: Parapente_bthb8r
        if ($var == '') {
            if ($request == 'nom') {
                $var = 'Parapente_bthb8r';
            } else { // link
                $var =
                    'https://res-5.cloudinary.com/litodti/image/upload/c_thumb,h_' .
                    $h . ',w_' . $w . '/Parapente_bthb8r';
            }
        } else {
            $explode = explode("cloudinary.com", $var);
            if (count($explode) > 1) {
                $img = explode("upload/", $var); $url = $img[0];
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[ count( $img ) - 1 ];
                }

                if ($request == 'nom') {
                    $var = $img;
                } else { // link
                    $var =
                        $url . 'upload/c_thumb,h_' .
                        $h . ',w_' . $w . '/' . trim($img);
                }
            }else{

//                $var = request()->getSchemeAndHttpHost().'/images/'.$var;
                $var = "https://backend.limatours.com.pe".'/images/'.$var;
            }
        }

        return trim(preg_replace('/\s\s+/', ' ', $var));
    }

    /**
     * @param $array
     * @param $param
     * @param $w / width
     * @param $h / height
     * @return mixed
     */
    public function verifyCloudinaryImgs($array, $param, $w, $h)
    {
        //https://res-5.cloudinary.com/litomarketing/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg
        for ($i = 0; $i < count($array); $i++) {

            $explode = explode("cloudinary.com", $array[$i][$param]);
            if (count($explode) > 1) {
                $img = explode("upload/", $array[$i][$param]); $url = $img[0];
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[1];
                }

                $array[$i]['img_nom'] = $img;
                $array[$i]['download'] = false;
                $array[$i]['resizes']['low'] = $url . 'upload/c_thumb,c_scale,w_600/'.trim($img);
                $array[$i]['resizes']['medium'] = $url . 'upload/c_thumb,c_scale,w_1200/'.trim($img);
                $array[$i]['resizes']['high'] = $url . 'upload/'.trim($img);
                $array[$i][$param] =
                    $url . 'upload/c_thumb,h_' .
                    $h . ',w_' . $w . '/' . trim($img);
            }

        }

        return $array;
    }

    public function getMultiResizesCloudinary($array, $param)
    {
        for ($i = 0; $i < count($array); $i++) {
            $explode = explode("cloudinary.com", $array[$i][$param]);

            if (count($explode) > 1) {
                $img = explode("upload/", $array[$i][$param]); $url = $img[0];
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[1];
                }

                $array[$i]['resizes']['low'] = $url . 'upload/c_thumb,c_scale,w_600/'.trim($img);
                $array[$i]['resizes']['medium'] = $url . 'upload/c_thumb,c_scale,w_1200/'.trim($img);
                $array[$i]['resizes']['high'] = $url . 'upload/'.trim($img);
            }

        }

        return $array;
    }
}
