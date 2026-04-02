<?php

namespace App\Http\Traits;

trait Images
{
    /**
     * @param $var / texto
     * @param $w
     * @param $h
     * @param $request 'link' / 'nom'
     * @return string
     */
    public function verifyCloudinaryImg($var, $w, $h, $request)
    {
        //https://res-5.cloudinary.com/litodti/image/upload/c_thumb,h_80,w_70/v1432940982/peru/amazonas/Tacacho_con_Cecina_024325_300.jpg
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
                $img = explode("upload/", $var);
                $img = $img[1];

                $verifyThumb = explode("c_thumb", $img);
                if (count($verifyThumb) > 1) {
                    $img = explode("/", $img);
                    $img = $img[ count($img) - 1 ];
                }

                if ($request == 'nom') {
                    $var = $img;
                } else { // link
                    $var =
                        'https://res-5.cloudinary.com/litodti/image/upload/c_thumb,h_' .
                        $h . ',w_' . $w . '/' . trim($img);
                }
            } else {

                //                $var = request()->getSchemeAndHttpHost().'/images/'.$var;
                $var = "https://backend.limatours.com.pe".'/images/'.$var;
            }
        }

        return trim(preg_replace('/\s\s+/', ' ', $var));
    }
}
