<?php

namespace App\Http\Controllers;

use App\Allotment;
use App\ClientSeller;
use App\HotelClient;
use App\RatesPlans;
use App\RatesPlansRooms;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ServiceDemoController extends Controller
{
    public function hotels(Request $request)
    {

        $hoteles = array(
            'city' => array(
                array(
                    'iso' => 'LIM,1010',
                    'description' => 'Peru, Lima, Centro de Lima',
                    'hoteles' => array(
                        array(
                            'tokenSearch' => '662e1a44a185611b19f6b668587ea9d3',
                            'name' => 'Casa Andina Premium Miraflores',
                            'country' => 'Peru',
                            'state' => 'Lima',
                            'city' => 'Lima',
                            'district' => 'Miraflores',
                            'zone' => 'Centro de Lima',
                            'description' => 'Con una ubicación estratégica en el corazón de Miraflores, nuestro Premiun Miraflores sido diseñado con el fin de expresar el sabor local con todas las comodidades, facilidades y servicios de un hotel first class. El Private Collection Miraflores cuenta con un inconfundible toque limeño y entre sus atributos exhibe una exquisita colección de arte contemporáneo, que junto con valiosas antigüedades coloniales, adornan sus habitaciones y espacios comunes.',
                            'direction' => 'Av. La Paz 463 Miraflores Lima - Per',
                            'logo' => 'http://placeimg.com/640/480/arch',
                            'category' => '5',
                            'type' => 'Ciudad',
                            'class' => 'Superior',
                            'price' => '147.89',
                            'coordinates' => array('latitud' => '-12.1219106', 'longitud' => '-77.0267998'),
                            'checkIn' => '15:00',
                            'checkOut' => '12:00',
                            'amenitis' => array(
                                array(
                                    'name' => 'Accesos a Internet / WiFi',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'BOARDING PASS PRINTER',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'Bar',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'Caja de seguridad',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                            ),
                            'gallerys' => array(
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                            ),
                            'rooms' => array(
                                array(
                                    'roomId' => '22',
                                    'roomType' => 'Standar',
                                    'name' => 'Habitación Doble Estandar',
                                    'description' => 'Habitacion con dos camas dobles vestidas con la suavidad del lino de 200 hilos. Equipada con lo necesario para trabajar en la comodidad de su habitaci&amp;oacute;n, pantalla plana con cable programado y mini bar. 02 Camas Dobles, Wifi Con Conexi&amp;oacute;n Extendida Y Desayuno Buffet Incluido',
                                    'gallerys' => array(
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                    ),
                                    'rates' =>
                                        array(
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),

                                        )


                                ),
                                array(
                                    'roomId' => '22',
                                    'roomType' => 'Standar',
                                    'name' => 'Habitación Doble Estandar',
                                    'description' => 'Habitacion con dos camas dobles vestidas con la suavidad del lino de 200 hilos. Equipada con lo necesario para trabajar en la comodidad de su habitaci&amp;oacute;n, pantalla plana con cable programado y mini bar. 02 Camas Dobles, Wifi Con Conexi&amp;oacute;n Extendida Y Desayuno Buffet Incluido',
                                    'gallerys' => array(
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                    ),
                                    'rates' =>
                                        array(
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),

                                        )


                                ),
                                array(
                                    'roomId' => '22',
                                    'roomType' => 'Standar',
                                    'name' => 'Habitación Doble Estandar',
                                    'description' => 'Habitacion con dos camas dobles vestidas con la suavidad del lino de 200 hilos. Equipada con lo necesario para trabajar en la comodidad de su habitaci&amp;oacute;n, pantalla plana con cable programado y mini bar. 02 Camas Dobles, Wifi Con Conexi&amp;oacute;n Extendida Y Desayuno Buffet Incluido',
                                    'gallerys' => array(
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                    ),
                                    'rates' =>
                                        array(
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),

                                        )


                                ),
                                array(
                                    'roomId' => '22',
                                    'roomType' => 'Standar',
                                    'name' => 'Habitación Doble Estandar',
                                    'description' => 'Habitacion con dos camas dobles vestidas con la suavidad del lino de 200 hilos. Equipada con lo necesario para trabajar en la comodidad de su habitaci&amp;oacute;n, pantalla plana con cable programado y mini bar. 02 Camas Dobles, Wifi Con Conexi&amp;oacute;n Extendida Y Desayuno Buffet Incluido',
                                    'gallerys' => array(
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                    ),
                                    'rates' =>
                                        array(
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),

                                        )


                                ),
                            ),
                        ),
                        array(
                            'tokenSearch' => '662e1a44a185611b19f6b668587ea9d3',
                            'name' => 'Casa Andina Premium Miraflores',
                            'country' => 'Peru',
                            'state' => 'Lima',
                            'city' => 'Lima',
                            'district' => 'Miraflores',
                            'zone' => 'Centro de Lima',
                            'description' => 'Con una ubicación estratégica en el corazón de Miraflores, nuestro Premiun Miraflores sido diseñado con el fin de expresar el sabor local con todas las comodidades, facilidades y servicios de un hotel first class. El Private Collection Miraflores cuenta con un inconfundible toque limeño y entre sus atributos exhibe una exquisita colección de arte contemporáneo, que junto con valiosas antigüedades coloniales, adornan sus habitaciones y espacios comunes.',
                            'direction' => 'Av. La Paz 463 Miraflores Lima - Per',
                            'logo' => 'http://placeimg.com/640/480/arch',
                            'category' => '5',
                            'type' => 'Ciudad',
                            'class' => 'Superior',
                            'price' => '147.89',
                            'coordinates' => array('latitud' => '-12.1219106', 'longitud' => '-77.0267998'),
                            'checkIn' => '15:00',
                            'checkOut' => '12:00',
                            'amenitis' => array(
                                array(
                                    'name' => 'Accesos a Internet / WiFi',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'BOARDING PASS PRINTER',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'Bar',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                                array(
                                    'name' => 'Caja de seguridad',
                                    'image' => 'http://placeimg.com/640/480/arch'
                                ),
                            ),
                            'gallerys' => array(
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                                'http://placeimg.com/640/480/arch',
                            ),
                            'rooms' => array(
                                array(
                                    'roomId' => '22',
                                    'roomType' => 'Standar',
                                    'name' => 'Habitación Estandar',
                                    'description' => 'Habitacion con dos camas dobles vestidas con la suavidad del lino de 200 hilos. Equipada con lo necesario para trabajar en la comodidad de su habitaci&amp;oacute;n, pantalla plana con cable programado y mini bar. 02 Camas Dobles, Wifi Con Conexi&amp;oacute;n Extendida Y Desayuno Buffet Incluido',
                                    'gallerys' => array(
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                        'http://placeimg.com/640/480/arch',
                                    ),
                                    'rates' =>
                                        array(
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Erev',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),
                                            array(
                                                'rateId' => '23',
                                                'available' => '2',
                                                'onRequest' => '0',
                                                'rateProvider' => 'Siteminder',
                                                'total' => '305.56',
                                                'avgPrice' => '152.78',
                                                'political' => array(
                                                    'rate' => array(
                                                        'name' => 'Politica Niños',
                                                        'message' => 'Politica Niños 2019 Los niños mayores de 12 años seran considerados con la tarifa de adultos'
                                                    ),
                                                    'cancellation' => array(
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'percent' => 50
                                                        ),
                                                        array(
                                                            'to' => 0,
                                                            'from' => 15,
                                                            'nights' => 3,
                                                            'amount' => 305.56
                                                        ),
                                                    )
                                                ),
                                                'room' => array(
                                                    array(
                                                        'item' => 1,
                                                        'adult' => '1',
                                                        'children' => '7,8',
                                                        'infants' => '0',
                                                        'extras' => '0',
                                                        'rateDay' => array(
                                                            array(
                                                                'day' => '2019-12-18',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                            array(
                                                                'day' => '2019-12-19',
                                                                'amount' => '152.78',
                                                                'amountAdult' => '152.78',
                                                                'amountChildren' => '0',
                                                                'amountInfante' => '0',
                                                                'amountExtra' => '0'
                                                            ),
                                                        )
                                                    ),

                                                )
                                            ),

                                        )


                                ),
                            ),

                        )

                    )
                )
            )
        );

        return Response::json($hoteles);
    }

    public
    function destinations(Request $request)
    {
        $destinations_test = array(
            'destinations' =>
                array(
                    0 =>
                        array(
                            'iso' => '130,100',
                            'description' => 'Peru, Lima',
                        ),
                    1 =>
                        array(
                            'iso' => '130,100,90',
                            'description' => 'Peru, Lima, Lima',
                        ),
                    2 =>
                        array(
                            'iso' => '130,100,90,30',
                            'description' => 'Peru, Lima, Lima, Miraflores',
                        ),
                ),
        );

        return Response::json($destinations_test);

    }

    public function reservation()
    {
        $reservation = array(
            'result' => 'ok',
            'nroFile' => '299829',
        );

        return Response::json($reservation);
    }

    public function cancelations()
    {
        $cancelations = array(
            'result' => 'ok',
            'nroFile' => '299829',
        );

        return Response::json($cancelations);
    }

    private
    function checkCountryState($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" =>
                        $hotel_client["hotel"]["country_id"] . ',' .
                        $hotel_client["hotel"]["state_id"],
                    "description" =>
                        $hotel_client["hotel"]["country"]["translations"][0]["value"] . "," .
                        $hotel_client["hotel"]["state"]["translations"][0]["value"]
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == ($hotel_client["hotel"]["country_id"] . ',' . $hotel_client["hotel"]["state_id"])) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" =>
                            $hotel_client["hotel"]["country_id"] . ',' .
                            $hotel_client["hotel"]["state_id"],
                        "description" =>
                            $hotel_client["hotel"]["country"]["translations"][0]["value"] . "," .
                            $hotel_client["hotel"]["state"]["translations"][0]["value"]
                    ]);
                }
            }
        }
        return $destinations;
    }

    private
    function checkCountryStateCity($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {
            if (count($destinations) === 0) {
                array_push($destinations, [
                    "ids" =>
                        $hotel_client["hotel"]["country_id"] . ',' .
                        $hotel_client["hotel"]["state_id"] . ',' .
                        $hotel_client["hotel"]["city_id"],
                    "description" =>
                        $hotel_client["hotel"]["country"]["translations"][0]["value"] . ', ' .
                        $hotel_client["hotel"]["state"]["translations"][0]["value"] . ', ' .
                        $hotel_client["hotel"]["city"]["translations"][0]["value"]
                ]);
            } else {
                $exists = false;
                for ($i = 0; $i < count($destinations); $i++) {
                    if ($destinations[$i]["ids"] == $hotel_client["hotel"]["country_id"] . ',' . $hotel_client["hotel"]["state_id"] . ',' . $hotel_client["hotel"]["city_id"]) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    array_push($destinations, [
                        "ids" =>
                            $hotel_client["hotel"]["country_id"] . ',' .
                            $hotel_client["hotel"]["state_id"] . ',' .
                            $hotel_client["hotel"]["city_id"],
                        "description" =>
                            $hotel_client["hotel"]["country"]["translations"][0]["value"] . ', ' .
                            $hotel_client["hotel"]["state"]["translations"][0]["value"] . ', ' .
                            $hotel_client["hotel"]["city"]["translations"][0]["value"]
                    ]);
                }
            }
        }
        return $destinations;
    }

    private
    function checkCountryStateCityDistrict($hotels_client)
    {
        $destinations = [];

        foreach ($hotels_client as $hotel_client) {

            $id_string_country_id = $hotel_client["hotel"]["country_id"];

            $id_string_state_id = "," . $hotel_client["hotel"]["state_id"];

            $id_string_city_id = $hotel_client["hotel"]["city_id"] ? "," . $hotel_client["hotel"]["city_id"] : '';

            $id_string_district_id = $hotel_client["hotel"]["district_id"] ? "," . $hotel_client["hotel"]["district_id"] : '';


            $ids_string = $id_string_country_id . $id_string_state_id . $id_string_city_id . $id_string_district_id;

            $country_name = $hotel_client["hotel"]["country"]["translations"][0]["value"];
            $state_name = $hotel_client["hotel"]["state"]["translations"][0]["value"] ? ',' . $hotel_client["hotel"]["state"]["translations"][0]["value"] : '';
            $city_name = $hotel_client["hotel"]["city"]["translations"][0]["value"] ? ',' . $hotel_client["hotel"]["city"]["translations"][0]["value"] : '';
            $district_name = $hotel_client["hotel"]["district"]["translations"][0]["value"] ? ',' . $hotel_client["hotel"]["district"]["translations"][0]["value"] : '';
            if ($id_string_district_id) {
                if (count($destinations) === 0) {
                    array_push($destinations, [
                        "ids" => $ids_string,
                        "description" => $country_name . $state_name . $city_name . $district_name
                    ]);
                } else {

                    $exists = false;
                    for ($i = 0; $i < count($destinations); $i++) {

                        if ($destinations[$i]["ids"] == $ids_string) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        array_push($destinations, [
                            "ids" => $ids_string,
                            "description" => $country_name . $state_name . $city_name . $district_name
                        ]);
                    }
                }
            }
        }
        return $destinations;
    }
}
