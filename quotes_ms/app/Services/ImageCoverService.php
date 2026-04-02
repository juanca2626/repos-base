<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use App\Models\Client;

class ImageCoverService
{
    /**
     * Procesa la imagen del paquete y retorna los datos generados.
     *
     * @param string      $portada     URL de la portada.
     * @param string      $lang        Idioma (ej. "es", "en").
     * @param string      $title       Título del paquete.
     * @param string      $destinies   Destinos, separados por comas.
     * @param string      $typePackage Tipo de paquete.
     * @param string|null $operations  Operaciones o días de salida.
     * @param int         $clientId    ID del cliente.
     * @param int         $days        Número total de días.
     * @param string      $dateFrom    Fecha de inicio (formato "Y-m-d").
     * @param string      $dateTo      Fecha de fin (formato "Y-m-d").
     * @param string      $codeUser    Código de usuario.
     *
     * @return array
     */
    public function createCoverImage(
        string $portada,
        string $lang,
        string $title,
        string $destinies,
        string $typePackage,
        ?string $operations,
        string $clientId,
        int $days,
        string $dateFrom,
        string $dateTo,
        string $codeUser
    ): array {
        // 1. Sanitizamos la URL de la portada
        $portada = $this->sanitizeUrl($portada);
        if (empty($portada)) {
            return [
                'success' => true,
                'image'   => "",
                'portada' => ""
            ];
        }

        // 2. Obtenemos el lenguaje y las traducciones
        $lang = strtolower($lang);
        $trad = $this->getTranslations($lang);

        // 3. Preparamos los datos
        $title = mb_strtoupper(trim($title), 'UTF-8');
        $operations = $operations ?? '';
        $codeUser = strtolower(trim($codeUser));

        // 4. Armamos la cadena de fechas de operación
        $dateOperations = $this->buildDateOperations($operations, $trad);

        // 5. Procesamos las imágenes: logo del cliente y portada
        $client = Client::findOrFail($clientId);
        $clientLogoPath = $client->logo;
        $dstImage = $this->createImageFromFile($clientLogoPath);

        $srcImage = imagecreatefromjpeg("https:" . $portada);

        list($width, $height) = getimagesize($clientLogoPath);
        $srcImage = $this->getLogoPackage($srcImage, $dstImage, $width, $height);

        // 6. Agregamos los textos a la imagen
        $textClient = sprintf("%s - %s %s %s %s", $title, $days, $trad['days'], ($days - 1), $trad['nights']);
        $srcImage = $this->getText($srcImage, $textClient, 18, 1420, true);
        $srcImage = $this->getText($srcImage, str_replace(",", " - ", $destinies), 17, 1450, true);
        $srcImage = $this->getText($srcImage, $trad['typeTour'] . ': ' . $typePackage, 17, 1480, true);
        $srcImage = $this->getText($srcImage, $dateOperations, 17, 1510, true);

        $validityText = $this->buildValidityText($lang, $dateFrom, $dateTo, $trad);
        $srcImage = $this->getText($srcImage, $validityText, 17, 1540, true);

        // 7. Generamos el nombre de la imagen y la guardamos en disco
        $code = $this->extractCodeFromPortada($portada);
        $imageName = $this->generateImageName($code, $lang);
        $folder = 'images/portadas/create/';
        imagepng($srcImage, $folder . $imageName, 0);
        imagedestroy($srcImage);

        // 8. Opcionalmente, movemos la imagen a Cloudinary según el código de usuario
        $urlImage = url($folder . $imageName);
        $cloudinary = false;
        if (in_array($codeUser, ['mlu', 'apa'])) {
            $urlImage = $this->moveToCloudinary($folder . $imageName, ['folder' => 'covers']);
            $cloudinary = true;
        }

        return [
            'cloudinary' => $cloudinary,
            'success'    => true,
            'code'       => $code,
            'image'      => $urlImage,
            'portada'    => $imageName
        ];
    }

    /**
     * Remueve "https:" y "http:" de una URL.
     */
    private function sanitizeUrl(string $url): string
    {
        return str_replace(["https:", "http:"], "", $url);
    }

    /**
     * Obtiene las traducciones desde el archivo JSON correspondiente al idioma.
     */
    private function getTranslations(string $lang): array
    {
        $dataLang = File::get(resource_path("lang/{$lang}/itinerary.json"));

        return json_decode($dataLang, true);
    }

    /**
     * Construye la cadena de fechas de operación.
     */
    private function buildDateOperations(string $operations, array $trad): string
    {
        $dateOperations = $trad["departure"] . ": ";
        $dates = array_filter(array_map('trim', explode(",", $operations)));

        if (count($dates) === 7 || empty($operations)) {
            $dateOperations .= $trad['daily'];
        } else {
            foreach ($dates as $key => $day) {
                if ($key > 0) {
                    $dateOperations .= '-';
                }
                $dateOperations .= $trad["semana"][$day]["name"];
            }
        }

        return $dateOperations;
    }

    /**
     * Crea una imagen a partir del archivo, eligiendo el método según su extensión.
     */
    private function createImageFromFile(string $path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'png') {
            return imagecreatefrompng($path);
        }

        return imagecreatefromjpeg($path);
    }

    /**
     * Construye el texto de validez (fechas) de acuerdo al idioma.
     */
    private function buildValidityText(string $lang, string $dateFrom, string $dateTo, array $trad): string
    {
        $dateFromParts = explode("-", $dateFrom);
        $dateToParts = explode("-", $dateTo);

        if (in_array($lang, ['es', 'pt'])) {
            $textDateFrom = (int)$dateFromParts[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$dateFromParts[1]) - 1]['name'];
            $textDateTo = (int)$dateToParts[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$dateToParts[1]) - 1]['name'];
        } elseif ($lang === 'en') {
            $textDateFrom = $trad['mes'][((int)$dateFromParts[1]) - 1]['name'] . " " . (int)$dateFromParts[2];
            $textDateTo = $trad['mes'][((int)$dateToParts[1]) - 1]['name'] . " " . (int)$dateToParts[2];
        } else {
            $textDateFrom = (int)$dateFromParts[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$dateFromParts[1]) - 1]['name'];
            $textDateTo = (int)$dateToParts[2] . " " . $trad['de'] . " " . $trad['mes'][((int)$dateToParts[1]) - 1]['name'];
        }

        return $trad['validity'] . ':' . $trad['desde'] . $textDateFrom . strtolower($trad['al']) . $textDateTo . " " . $trad['del'] . " " . $dateToParts[0];
    }

    /**
     * Extrae el código de la URL de la portada.
     */
    private function extractCodeFromPortada(string $portada): string
    {
        $parts = explode("/packages/", $portada);

        return isset($parts[1]) ? str_replace("/frontpage.jpg", "", $parts[1]) : '';
    }

    /**
     * Genera un nombre único para la imagen.
     */
    private function generateImageName(string $code, string $lang): string
    {
        return $code . '-' . $lang . '-' . md5(date("Y-m-d H:i:s")) . '.jpg';
    }

    /**
     * Mueve la imagen a Cloudinary (implementa la lógica según tus necesidades).
     */
    private function moveToCloudinary(string $imagePath, array $options = [])
    {
        // Ejemplo: utilizando el SDK de Cloudinary
        // $result = \Cloudinary\Uploader::upload($imagePath, $options);
        // return $result['secure_url'];

        // Temporal: retorna la ruta local
        return $imagePath;
    }

    /**
     * Agrega el logo a la imagen de la portada.
     * Implementa la lógica necesaria.
     */
    private function getLogoPackage($source, $logo, int $ancho, int $top)
    {
        $max_ancho = 270;
        $max_alto = 130;

        $x_ratio = $max_ancho / $ancho;
        $y_ratio = $max_alto / $top;

        if (($ancho <= $max_ancho) && ($top <= $max_alto)) {
            $ancho_final = $ancho;
            $alto_final = $top;
        } elseif (($x_ratio * $top) < $max_alto) {
            $alto_final = ceil($x_ratio * $top);
            $ancho_final = $max_ancho;
        } else {
            $ancho_final = ceil($y_ratio * $ancho);
            $alto_final = $max_alto;
        }

        if ($max_ancho > $ancho_final) {
            $ancho_img = round(($max_ancho - $ancho_final) / 2);
            $total = $ancho_final + $ancho_img;

            $rx = round(2480 / $ancho_final) + 30;

            $y1 = 52 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;

            imagecopyresampled($source, $logo, $rx, $y - 30, 0, 0, $total, $max_alto, $ancho, $top);
        } else {
            $rx = round(2480 / $ancho_final) + 30;

            $y1 = 80 - round(3508 / $alto_final);
            $y = round(3508 / $alto_final) + $y1;
            imagecopyresampled($source, $logo, $rx, $y - 30, 0, 0, $ancho_final, $alto_final, $ancho, $top);
        }

        return $source;
    }
    /**
     * Agrega texto a la imagen.
     * Implementa la lógica necesaria (por ejemplo, usando imagettftext).
     */
    public function getText($source, $text, $fontsize, $top, $portOrigin)
    {

        $font = public_path() . '/fonts/calibri-bold.ttf';
        $angle = 0; //angle of your text

        $text = $text;
        $dimensions = imagettfbbox($fontsize, $angle, $font, $text);
        $textWidth = abs($dimensions[4] - $dimensions[0]) + 2;

        $imageWidth = $textWidth; //width of your image
        $imageHeight = 80;// height of your image
        $logoimg = imagecreatetruecolor($imageWidth, $imageHeight);

        //for transparent background
        imagealphablending($logoimg, false);
        imagesavealpha($logoimg, true);
        $col = imagecolorallocatealpha($logoimg, 255, 255, 255, 127);
        imagefill($logoimg, 0, 0, $col);
        //for transparent background
        $brown = imagecolorallocate($logoimg, 117, 116, 116); //for font color
        $white = imagecolorallocate($logoimg, 255, 255, 255); //for font color

        $x = 0; // x- position of your text
        $y = 50; // y- position of your text
        $angle = 0; //angle of your text
        //imagettftext($logoimg, $fontsize,$angle , $x, $y, $brown, $font, $text); //fill text in your image

        if ($portOrigin == false) {
            imagettftext($logoimg, $fontsize, $angle, $x, $y, $brown, $font, $text); //fill text in your image
            $x = imagesx($source) - $textWidth - 60;
        } else {
            imagettftext($logoimg, $fontsize, $angle, $x, $y, $white, $font, $text); //fill text in your image
            $x = imagesx($source) - $textWidth - 80;
        }

        imagecopy($source, $logoimg, $x, $top, 0, 0, $textWidth, $imageHeight);

        return $source;
    }
}
