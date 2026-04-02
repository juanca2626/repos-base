<?php

namespace App\Http\Controllers;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CartController extends Controller
{
    public function addBestOption(Request $request)
    {


        try {
            $token_search = $request->post('token_search');
            $token_search_frontend = $request->post('token_search_frontend');
            $date_from = $request->post('date_from');
            $date_to = $request->post('date_to');
            $hotel = $request->post('best_option');

            $faker = \Faker\Factory::create();

            Cart::setGlobalTax(0);

            $cart_items_id = [];

            foreach ($hotel["best_options"]["rooms"] as $index_room => $room) {
                // $rates_id = [];
                foreach ($room["rates"] as $index_rate => $rate) {
                    // array_push($rates_id, $rate["rateId"]);
                    $cart_item = Cart::add($faker->unique()->uuid, $room["name"], 1, $rate["total"], 0, [
                        "product_type" => 'hotel',
                        "date_from" => $date_from,
                        "date_to" => $date_to,
                        "token_search" => $token_search,
                        "token_search_frontend" => $token_search_frontend,
                        "hotel_name" => $hotel["name"],
                        "hotel_id" => $hotel["id"],
                        "room_id" => $room["room_id"],
                        "rate_id" => $rate["rateId"],
                        "best_option" => true,
                        "search" => $hotel,
                        "room" => $room,
                        "rate" => $rate,
                    ]);

                    array_push($cart_items_id, $cart_item->rowId);
                }
            }

            return Response::json(['success' => true, 'cart_items_id' => $cart_items_id, 'test' => 1]);

        } catch (\Error $exception) {
            return Response::json(['success' => false, 'msn' => $exception->getMessage()]);
        }
    }


    /* Habia un bug cuando agregabas una habitacion personalidad con cantidades mayores a 1 cada habitacion puede tener su propio combinacion de personas pero se asumia que era la mismma cantidad y le ponia a cada habitacion la misma tarifa y a si todas siempre tenian la mismca cantidad de personas
     *
     * */
    public function addCart(Request $request)
    {
        $faker = \Faker\Factory::create();
        $product_type = ($request->has('product_type')) ? $request->post('product_type') : 'hotel';

        // Validate Ages Child
        if ($product_type === 'service') {
            $room = $request->post('room');
            $search = $request->post('search');

            // Extraer los límites de edad permitidos, con valores por defecto si no existen
            $minAge = data_get($search, 'children_age_allowed.min', 0);
            $maxAge = data_get($search, 'children_age_allowed.max', 99);

            // Validar que hay niños
            if (!empty($room['child']) && $room['child'] > 0) {

                // Validar edades de los niños
                if (!empty($room['age_childs']) && is_array($room['age_childs'])) {
                    foreach ($room['age_childs'] as $child) {
                        $age = $child['age'] ?? 0;

                        if ($age < $minAge || $age > $maxAge) {
                            return response()->json([
                                'success' => false,
                                'message' => __('service.label.validate_age_child')
                            ], 422);
                        }
                    }
                }
            }
        }


        //Hotel
        $token_search = $request->post('token_search');
        $token_search_frontend = $request->post('token_search_frontend');
        $date_from = $request->post('date_from');
        $date_to = $request->post('date_to');
        $hotel_id = $request->post('hotel_id');
        $room_id = $request->post('room_id');
        $rate_id = $request->post('rate_id');
        $hotel_name = $request->post('hotel_name');
        $room_name = $request->post('room_name');
        $rates = $request->post('rates');
        $hotel = $request->post('search');
        $room = $request->post('room');
        $rate_details = $request->post('rate');
        $rate_details_2 = $request->post('rate');
        $ages_child = $request->post('ages_child');
        //Servicios
        $service_id = $request->post('service_id');
        $service_name = $request->post('service_name');
        $reservation_time = $request->post('reservation_time');
        $service_supplements = $request->post('service_supplements');
        Cart::setGlobalTax(0);

        $cart_items_id = [];

        $parent_id = $faker->unique()->uuid;

        if ($product_type === 'hotel') {
            foreach ($rates as $rate) {
                if (isset($rate['supplements'])) {
                    $rate_details_2['supplements'] = $rate['supplements'];
                } else {
                    $rate_details_2['supplements'] = $rate_details['supplements'];
                }

                $rate_details_2['total_taxes_and_services'] = $rate['total_taxes_and_services'];

                if (!isset($rate['quantity_extras'])) {
                    $rate['quantity_extras'] = 0;
                }
                $rate_details_2['rate'] = [$rate];
                $cart_item = Cart::add($faker->unique()->uuid, $room_name, 1, $rate["total_amount"], 0, [
                    "product_type" => $product_type,
                    "date_from" => $date_from,
                    "date_to" => $date_to,
                    "token_search" => $token_search,
                    "token_search_frontend" => $token_search_frontend,
                    "hotel_name" => $hotel_name,
                    "hotel_id" => $hotel_id,
                    "room_id" => $room_id,
                    "rate_id" => $rate_id,
                    "quantity_adults" => $rate["quantity_adults"],
                    "quantity_child" => $rate["quantity_child"],
                    "ages_child" => $ages_child,
                    "best_option" => false,
                    "search" => $hotel,
                    "room" => $room,
                    "rate" => $rate_details_2,
                    "parent_id" => $parent_id
                ]);

                array_push($cart_items_id, $cart_item->rowId);
            }
        } else {
            if (count($service_supplements) > 0) {
                $hotel = $this->alterSupplementService($hotel, $service_supplements);
            }

            $total_supplement_optional = $this->getTotalSupplementAllSelected($hotel['supplements']);

            $total = $hotel["total_amount"] + $total_supplement_optional;

            $cart_item = Cart::add($faker->unique()->uuid, $service_name, 1, $total, 0, [
                "product_type" => $product_type,
                "service_id" => $service_id,
                "reservation_time" => $reservation_time,
                "date_from" => $date_from,
                "token_search" => $token_search,
                "token_search_frontend" => $token_search_frontend,
                "service_name" => $service_name,
                "supplements" => $service_supplements,
                "rate_id" => $rate_id,
                "rate" => $rate_details_2,
                "search" => $hotel
            ]);
            array_push($cart_items_id, $cart_item->rowId);
        }



        return Response::json(['success' => true, 'cart_items_id' => $cart_items_id]);
    }

    public function getTotalSupplementOptionalSelected($supplement)
    {
        $supplement_optionals_selected = $supplement['supplements'];
        $total_supplement_optional = 0;
        foreach ($supplement_optionals_selected as $select_supplement) {
            if ($select_supplement['type'] === 'optional') {
                $total_supplement_optional += $select_supplement['rate']['total_amount'];
            }
        }

        return $total_supplement_optional;
    }

    public function getTotalSupplementAllSelected($supplement)
    {
        $supplement_optionals_selected = $supplement['supplements'];
        $total_supplement_optional = 0;
        foreach ($supplement_optionals_selected as $select_supplement) {
            $total_supplement_optional += $select_supplement['rate']['total_amount'];
        }

        return $total_supplement_optional;
    }

    public function alterSupplementService($service, $supplements_selected)
    {
        $service_select = $service;
        $supplement_optionals = $service['supplements']['optional_supplements'];

        foreach ($supplements_selected as $select_supplement_optional) {
            foreach ($supplement_optionals as $key_opt => $supplement_optional) {
                if ($select_supplement_optional['token_search'] == $supplement_optional['token_search']) {
                    $supplement_optional['params']['adults'] = $select_supplement_optional['adults'];
                    $supplement_optional['params']['child'] = $select_supplement_optional['child'];
                    $supplement_optional['params']['dates'] = $select_supplement_optional['dates'];
                    array_push($service_select['supplements']['supplements'], $supplement_optional);
                    array_splice($supplement_optionals, $key_opt, 1);
                }
            }
        }
        $service_select['supplements']['optional_supplements'] = $supplement_optionals;

        $service_select['supplements'] = $this->sumSupplements($service_select['supplements']);

        return $service_select;
    }

    public function sumSupplements($supplements)
    {
        $price_per_adult = 0;
        $price_per_child = 0;
        $price_per_person = 0;
        $total_adult_amount = 0;
        $total_child_amount = 0;
        $total_amount = 0;
        foreach ($supplements['supplements'] as $supplement) {
            $price_per_adult += $supplement['rate']['price_per_adult'];
            $price_per_child += $supplement['rate']['price_per_child'];
            $price_per_person += $supplement['rate']['price_per_person'];
            $total_adult_amount += $supplement['rate']['total_adult_amount'];
            $total_child_amount += $supplement['rate']['total_child_amount'];
            $total_amount += $supplement['rate']['total_amount'];
        }

        $supplements['total_amount'] = $total_amount;
        $supplements['total_adult_amount'] = $total_adult_amount;
        $supplements['total_child_amount'] = $total_child_amount;

        return $supplements;
    }

    public function getCart()
    {
        $cart = [
            "cart_content" => null,
            "hotels" => [],
            "services" => [],
            "total_cart" => 0,
        ];

        $cart_content = Cart::content();
        $count = 0;

        $cart["cart_content"] = $cart_content;

        foreach ($cart_content as $index => $item) {
            $count++;
            if ($item->options->product_type === 'hotel') {
                if ($count === 1) {

                    array_push($cart["hotels"], [
                        "hotel_id" => $item->options->hotel_id,
                        "hotel_name" => $item->options->hotel_name,
                        "date_from" => $item->options->date_from,
                        "date_to" => $item->options->date_to,
                        "hotel" => $item->options->search,
                        "total_hotel" => 0,
                        "rooms" => [],
                        "best_option" => false,
                    ]);
                    if ($item->options->best_option) {
                        $cart["hotels"][0]["best_option"] = true;
                    }

                    $cart["hotels"][0]["total_hotel"] += $item->subtotal;

                    array_push($cart["hotels"][0]["rooms"], [
                        "room_id" => $item->options->room_id,
                        "room_name" => $item->name,
                        "rate_name" => $item->options->rate['name'],
                        "onRequest" => $item->options->rate['onRequest'],
                        "total_room" => $item->subtotal,
                        "cart_items_id" => [],
                    ]);
                    array_push($cart["hotels"][0]["rooms"][0]["cart_items_id"], $index);
                } else {

                    $find_hotel = false;
                    foreach ($cart["hotels"] as $index_hotel => $hotel) {
                        if ($hotel["hotel_id"] == $item->options->hotel_id && $hotel["date_from"] == $item->options->date_from && $hotel["date_to"] == $item->options->date_to) {
                            $find_hotel = true;
                            $cart["hotels"][$index_hotel]["total_hotel"] += $item->subtotal;
                            if ($item->options->best_option) {
                                $cart["hotels"][$index_hotel]["best_option"] = true;
                            }

                            $find_room = false;
                            foreach ($hotel["rooms"] as $index_room => $room) {
                                if ($room["room_id"] == $item->options->room_id) {
                                    $find_room = true;

                                    $cart["hotels"][$index_hotel]["rooms"][$index_room]["total_room"] += $item->subtotal;

                                    array_push($cart["hotels"][$index_hotel]["rooms"][$index_room]["cart_items_id"],
                                        $index);
                                    break;
                                }
                            }
                            if (!$find_room) {
                                $index_room = array_push($cart["hotels"][$index_hotel]["rooms"], [
                                        "room_id" => $item->options->room_id,
                                        "room_name" => $item->name,
                                        "rate_name" => $item->options->rate['name'],
                                        "onRequest" => $item->options->rate['onRequest'],
                                        "total_room" => $item->subtotal,
                                        "cart_items_id" => [],
                                    ]) - 1;
                                array_push($cart["hotels"][$index_hotel]["rooms"][$index_room]["cart_items_id"],
                                    $index);
                            }
                        }
                    }

                    if (!$find_hotel) {
                        $index_hotel = array_push($cart["hotels"], [
                                "hotel_id" => $item->options->hotel_id,
                                "hotel_name" => $item->options->hotel_name,
                                "date_from" => $item->options->date_from,
                                "date_to" => $item->options->date_to,
                                "hotel" => $item->options->search,
                                "total_hotel" => 0,
                                "rooms" => [],
                            ]) - 1;
                        if ($item->options->best_option) {
                            $cart["hotels"][$index_hotel]["best_option"] = true;
                        }
                        $cart["hotels"][$index_hotel]["total_hotel"] += $item->subtotal;

                        $index_room = array_push($cart["hotels"][$index_hotel]["rooms"], [
                                "room_id" => $item->options->room_id,
                                "room_name" => $item->name,
                                "rate_name" => $item->options->rate['name'],
                                "onRequest" => $item->options->rate['onRequest'],
                                "total_room" => $item->subtotal,
                                "cart_items_id" => [],
                            ]) - 1;

                        array_push($cart["hotels"][$index_hotel]["rooms"][$index_room]["cart_items_id"], $index);
                    }
                }
            } else {
                array_push($cart["services"], [
                    "service_id" => $item->options->service_id,
                    "service_name" => $item->options->service_name,
                    "reservation_time" => $item->options->reservation_time,
                    "supplements" => $item->options->supplements,
                    "date_from" => $item->options->date_from,
                    "service" => $item->options->search,
                    "cart_items_id" => $index,
                    "total_service" => $item->price,
                ]);
            }
        }

        foreach ($cart["hotels"] as $index_hotel => $hotel) {
            foreach ($hotel["rooms"] as $index_room => $room) {
                $cart["hotels"][$index_hotel]["rooms"][$index_room]["total_room"] = number_format($room['total_room'],
                    2);
            }

            $cart["hotels"][$index_hotel]["total_hotel"] = number_format($hotel['total_hotel'], 2);
        }

        $cart["total_cart"] = Cart::total();

        $cart["quantity_items"] = Cart::count();

        return Response::json(['success' => true, 'cart' => $cart]);
    }

    public function destroyCart()
    {
        Cart::destroy();
        session()->forget('passengers_list');
        return Response::json(['success' => true, 'data' => 'Carrito Eliminado']);
    }

    public function deleteItemCart($id_item)
    {
        Cart::remove($id_item);

        return Response::json(['success' => true, 'data' => 'Item del Carrito Eliminado']);
    }

    public function deleteItemsCart(Request $request)
    {
        $cart_items_id = $request->post('cart_items_id');
        try {
            foreach ($cart_items_id as $id) {
                Cart::remove($id);
            }
            return Response::json(['success' => true, 'data' => 'Items Eliminados del Carrito']);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'data' => $exception->getMessage()]);
        }


    }

    public function clearItemsCart(Request $request)
    {
        Cart::destroy();

        return Response::json(['success' => true, 'data' => 'Items Eliminados del Carrito']);
    }


    public function getCartContentFrontEnd()
    {
//        $this->destroyCart();
        $cart = [
            "services" => [],
            "hotels" => [],
            "quantity_items" => 0,
        ];
        $cart_content = Cart::content();

        $count = 0;
        foreach ($cart_content as $rowId => $item) {
            $count++;
            if ($item->options->product_type === 'hotel') {
                if ($count === 1) {

                    array_push($cart["hotels"], [
                        "hotel_id" => $item->options->hotel_id,
                        "hotel_name" => $item->options->hotel_name,
                        "date_from" => $item->options->date_from,
                        "date_to" => $item->options->date_to,
                        "total_hotel" => 0,
                        "rooms" => [],
                        "hotel" => $item->options->search,
                        "quantity_rooms" => 0,
                        "quantity_adults" => 0,
                        "quantity_child" => 0,
                        "quantity_extras" => 0,
                        "best_option" => false,
                        "cart_items_id" => [],
                        "token_search" => $item->options->token_search,
                        "token_search_frontend" => $item->options->token_search_frontend,

                    ]);

                    $cart["hotels"][0]["total_hotel"] += $item->subtotal;
                    $cart["hotels"][0]["total_hotel"] = number_format($cart["hotels"][0]["total_hotel"], 2, '.', '');

                    array_push($cart["hotels"][0]["rooms"], [
                        "room_id" => $item->options->room_id,
                        "room_name" => $item->name,
                        "total_room" => $item->subtotal,
                        "room" => $item->options->room,
                        "rate" => $item->options->rate,
                        "quantity_adults" => 0,
                        "quantity_child" => 0,
                        "quantity_extras" => 0,
                        "cart_items_id" => [],
                    ]);
                    array_push($cart["hotels"][0]["cart_items_id"], $rowId);
                    array_push($cart["hotels"][0]["rooms"][0]["cart_items_id"], $rowId);
                    $cart["hotels"][0]["quantity_rooms"] += 1;
                    if ($item->options->best_option) {
                        $cart["hotels"][0]["best_option"] = true;

                        $cart["hotels"][0]["rooms"][0]["quantity_adults"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_adults"];
                        $cart["hotels"][0]["rooms"][0]["quantity_child"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_child"];
                        $cart["hotels"][0]["rooms"][0]["quantity_extras"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_extras"];

                        $cart["hotels"][0]["quantity_adults"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_adults"];
                        $cart["hotels"][0]["quantity_child"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_child"];
                        $cart["hotels"][0]["quantity_extras"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"]["quantity_extras"];
                    } else {

                        $cart["hotels"][0]["rooms"][0]["quantity_adults"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_adults"];
                        $cart["hotels"][0]["rooms"][0]["quantity_child"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_child"];
                        $cart["hotels"][0]["rooms"][0]["quantity_extras"] = $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_extras"];

                        $cart["hotels"][0]["quantity_adults"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_adults"];
                        $cart["hotels"][0]["quantity_child"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_child"];
                        $cart["hotels"][0]["quantity_extras"] += $cart["hotels"][0]["rooms"][0]["rate"]["rate"][0]["quantity_extras"];
                    }
                } else {
                    $find_hotel = false;
                    foreach ($cart["hotels"] as $index_hotel => $hotel) {
                        if ($hotel["hotel_id"] == $item->options->hotel_id && $hotel["date_from"] == $item->options->date_from && $hotel["date_to"] == $item->options->date_to) {
                            $find_hotel = true;
                            $cart["hotels"][$index_hotel]["total_hotel"] += $item->subtotal;
                            $cart["hotels"][$index_hotel]["total_hotel"] = number_format($cart["hotels"][$index_hotel]["total_hotel"],
                                2, '.', '');

                            $index_room = array_push($cart["hotels"][$index_hotel]["rooms"], [
                                    "room_id" => $item->options->room_id,
                                    "room_name" => $item->name,
                                    "total_room" => $item->subtotal,
                                    "room" => $item->options->room,
                                    "rate" => $item->options->rate,
                                    "quantity_adults" => 0,
                                    "quantity_child" => 0,
                                    "quantity_extras" => 0,
                                    "cart_items_id" => [],
                                ]) - 1;
                            array_push($cart["hotels"][$index_hotel]["cart_items_id"], $rowId);
                            array_push($cart["hotels"][$index_hotel]["rooms"][$index_room]["cart_items_id"], $rowId);
                            $cart["hotels"][$index_hotel]["quantity_rooms"] += 1;

                            if ($item->options->best_option) {
                                $cart["hotels"][$index_hotel]["best_option"] = true;

                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_adults"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_adults"];
                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_child"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_child"];
                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_extras"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_extras"];

                                $cart["hotels"][$index_hotel]["quantity_adults"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_adults"];
                                $cart["hotels"][$index_hotel]["quantity_child"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_child"];
                                $cart["hotels"][$index_hotel]["quantity_extras"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_extras"];
                            } else {
                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_adults"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_adults"];
                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_child"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_child"];
                                $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_extras"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_extras"];

                                $cart["hotels"][$index_hotel]["quantity_adults"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_adults"];
                                $cart["hotels"][$index_hotel]["quantity_child"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_child"];
                                $cart["hotels"][$index_hotel]["quantity_extras"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_extras"];
                            }
                        }
                    }
                    if (!$find_hotel) {
                        $index_hotel = array_push($cart["hotels"], [
                                "hotel_id" => $item->options->hotel_id,
                                "hotel_name" => $item->options->hotel_name,
                                "date_from" => $item->options->date_from,
                                "date_to" => $item->options->date_to,
                                "total_hotel" => 0,
                                "hotel" => $item->options->search,
                                "rooms" => [],
                                "quantity_rooms" => 0,
                                "quantity_adults" => 0,
                                "quantity_child" => 0,
                                "quantity_extras" => 0,
                                "best_option" => false,
                                "cart_items_id" => [],
                                "token_search" => $item->options->token_search,
                                "token_search_frontend" => $item->options->token_search_frontend,
                            ]) - 1;

                        $cart["hotels"][$index_hotel]["total_hotel"] += $item->subtotal;
                        $cart["hotels"][$index_hotel]["total_hotel"] = number_format($cart["hotels"][$index_hotel]["total_hotel"],
                            2, '.', '');

                        $index_room = array_push($cart["hotels"][$index_hotel]["rooms"], [
                                "room_id" => $item->options->room_id,
                                "room_name" => $item->name,
                                "total_room" => number_format($item->subtotal, 2, '.', ''),
                                "room" => $item->options->room,
                                "rate" => $item->options->rate,
                                "quantity_adults" => 0,
                                "quantity_child" => 0,
                                "quantity_extras" => 0,
                                "cart_items_id" => [],
                            ]) - 1;
                        array_push($cart["hotels"][$index_hotel]["cart_items_id"], $rowId);
                        array_push($cart["hotels"][$index_hotel]["rooms"][$index_room]["cart_items_id"], $rowId);

                        $cart["hotels"][$index_hotel]["quantity_rooms"] += 1;

                        if ($item->options->best_option) {
                            $cart["hotels"][$index_hotel]["best_option"] = true;

                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_adults"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_adults"];
                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_child"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_child"];
                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_extras"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_extras"];

                            $cart["hotels"][$index_hotel]["quantity_adults"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_adults"];
                            $cart["hotels"][$index_hotel]["quantity_child"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_child"];
                            $cart["hotels"][$index_hotel]["quantity_extras"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"]["quantity_extras"];
                        } else {
                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_adults"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_adults"];
                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_child"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_child"];
                            $cart["hotels"][$index_hotel]["rooms"][$index_room]["quantity_extras"] = $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_extras"];

                            $cart["hotels"][$index_hotel]["quantity_adults"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_adults"];
                            $cart["hotels"][$index_hotel]["quantity_child"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_child"];
                            $cart["hotels"][$index_hotel]["quantity_extras"] += $cart["hotels"][$index_hotel]["rooms"][$index_room]["rate"]["rate"][0]["quantity_extras"];
                        }
                    }
                }
            } else {
                array_push($cart["services"], [
                    "service_id" => $item->options->service_id,
                    "service_name" => $item->options->service_name,
                    "date_from" => $item->options->date_from,
                    "total_service" => $item->price,
                    "service" => $item->options->search,
                    "quantity_adults" => $item->options->search['quantity_adult'],
                    "quantity_child" => $item->options->search['quantity_child'],
                    "cart_items_id" => [$rowId],
                    "token_search" => $item->options->token_search,
                    "token_search_frontend" => $item->options->token_search_frontend,
                ]);
            }
        }

        $cart["quantity_items"] = Cart::count();
        $cart["total_cart"] = Cart::total();

        return Response::json(['success' => true, 'cart' => $cart]);
    }

    public function updateItemCart(Request $request)
    {
        $quantity_adults = $request->post('quantity_adult');
        $quantity_child = $request->post('quantity_child');
        $cart_item_id = $request->post('cart_item_id');
        $supplements_optional = $request->post('supplements_optional');

        $item_cart = Cart::get($cart_item_id);

        $faker = \Faker\Factory::create();

        Cart::setGlobalTax(0);

        $item = $item_cart->toArray();
        foreach ($supplements_optional as $supplement_optional) {
            //if ($supplement_optional["amount_extra"] == 1) {

            $total_amount_supplement = 0;
            $suplemen_name = '';
            $supplement_id = '';
            foreach ($supplement_optional["supplement_dates_selected"] as $date_selected) {

                $calculoAdults = false;
                $calculoChilds = false;
                $rates = [];
                foreach ($date_selected['rates'] as $rate) {

                    $suplemen_name = $rate["supplement"]["translations"][0]["value"];
                    $supplement_id = $rate["supplement"]["id"];
                    $amount_extra = $rate["rate_supplements"]['amount_extra'];

                    if ($rate["price_per_room"] > 0) {
                        $rate["total_amount"] = $rate["price_per_room"];
                        $total_amount_supplement += $rate["price_per_room"];

                    } else {

                        if ($rate["max_age"] >= 18) {

                            if ($calculoAdults == false) {
                                $rate["total_amount"] = $rate["price_per_person"] * $quantity_adults;
                                $total_amount_supplement += $rate["price_per_person"] * $quantity_adults;
                                $calculoAdults = true;
                            }

                        }

                        if ($quantity_child > 0) {
                            if ($rate["min_age"] >= 0 and $rate["max_age"] < 18) {

                                if ($calculoChilds == false) {
                                    $rate["total_amount"] = $rate["price_per_person"] * $quantity_child;
                                    $total_amount_supplement += $rate["price_per_person"] * $quantity_child;
                                    $calculoChilds = true;
                                }

                            }
                        }

                    }

                }

            }

            if (count($supplement_optional["supplement_dates_selected"]) > 0) {
                $item["price"] += $total_amount_supplement / count($supplement_optional["supplement_dates_selected"]);
                $item['options']['rate']['total'] += $total_amount_supplement;
                $item['options']['rate']['avgPrice'] += $total_amount_supplement;
                $item['options']['rate']['supplements']['total_amount'] += $total_amount_supplement;


                $key = array_search($suplemen_name,
                    array_column($item['options']['rate']['supplements']['supplements'], 'supplement'));

                if (!$key) {
                    array_push($item['options']['rate']['supplements']['supplements'], [
                        "total_amount" => $total_amount_supplement,
                        "supplement" => $suplemen_name,
                        "supplement_id" => $supplement_id,
                        "amount_extra" => $amount_extra,
                        "date" => '',
                        "type" => $supplement_optional["type"],
                        "type_req_opt" => "optional",
                        "amount_extra" => $supplement_optional["amount_extra"],
                        "calendars" => $supplement_optional["supplement_dates_selected"]
                    ]);
                } else {

                    $item['options']['rate']['supplements']['supplements'][$key]['calendars'] = array_merge($item['options']['rate']['supplements']['supplements'][$key]['calendars'],
                        $supplement_optional["supplement_dates_selected"]);
//                    array_push($item['options']['rate']['supplements']['supplements'][$key]['calendars'], $supplement_optional["supplement_dates_selected"]);
                    $item['options']['rate']['supplements']['supplements'][$key]['total_amount'] = $item['options']['rate']['supplements']['supplements'][$key]['total_amount'] + $total_amount_supplement;


                }
            }


            //}
        }

        $item['options']['rate']['supplements']['supplements_optional'] = $supplements_optional;

        foreach ($item['options']['rate']['supplements']['supplements_optional'] as $index_supplement => $supplement) {
            //if ($item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["amount_extra"] == 1) {
            foreach ($item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["supplement_dates_selected"] as $date_selected) {

                if (array_key_exists("totals",
                    $item['options']['rate']['supplements']['supplements_optional'][$index_supplement])) {
                    array_push($item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["totals"],
                        $date_selected["date"]);
                } else {
                    $item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["totals"] = [];
                    array_push($item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["totals"],
                        $date_selected["date"]);
                }
            }
            $item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["key"] += 1;
            $item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["supplement_dates_selected"] = [];
            //}
        }
        Cart::add($faker->unique()->uuid, $item["name"], 1, $item["price"], 0, $item["options"]);

        Cart::remove($item["rowId"]);

        return $item_cart;
    }

    public function deleteSupplementItemCart(Request $request)
    {
        $cart_item_id = $request->post('cart_item_id');
        $index_supplement = $request->post('index_supplement');

        $item_cart = Cart::get($cart_item_id);

        $faker = \Faker\Factory::create();

        Cart::setGlobalTax(0);

        $item = $item_cart->toArray();

        $item["price"] -= $item['options']['rate']['supplements']['supplements'][$index_supplement]["total_amount"];
        $item['options']['rate']['total'] -= $item['options']['rate']['supplements']['supplements'][$index_supplement]["total_amount"];
        $item['options']['rate']['avgPrice'] -= $item['options']['rate']['supplements']['supplements'][$index_supplement]["total_amount"];
        $item['options']['rate']['supplements']['total_amount'] -= $item['options']['rate']['supplements']['supplements'][$index_supplement]["total_amount"];

        $supplement_name = $item['options']['rate']['supplements']['supplements'][$index_supplement]["supplement"];
//        $date = $item['options']['rate']['supplements']['supplements'][$index_supplement]["date"];
//        $index_selected = null;
        foreach ($item['options']['rate']['supplements']['supplements_optional'] as $index_supplement_optional => $supplement) {
            if ($supplement_name == $supplement["supplement"]) {

                unset($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["options"]["daysOfWeekDisabled"]);
                foreach ($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["totals"] as $index_total => $total_date) {
                    array_push($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["options"]["enabledDates"],
                        $total_date);
                }
                $item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["totals"] = [];
                $item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["supplement_dates_selected"] = [];

                /*
                if (count($supplement["options"]["enabledDates"]) === 0) {
                    unset($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["options"]["daysOfWeekDisabled"]);
                }
                array_push($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["options"]["enabledDates"], $date);
                foreach ($supplement["supplement_dates_selected"] as $index_date => $supplement_date) {
                    if ($supplement_date["date"] == $date) {
                        $index_selected = $index_date;
                    }
                }
                foreach ($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["totals"] as $index_total => $total_date) {
                    if ($total_date == $date) {

                        array_splice($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["totals"], $index_total, 1);
                        break;
                    }
                }
                array_splice($item['options']['rate']['supplements']['supplements_optional'][$index_supplement_optional]["supplement_dates_selected"], $index_selected, 1);
                */
            }
        }

        array_splice($item['options']['rate']['supplements']['supplements'], $index_supplement, 1);

        foreach ($item['options']['rate']['supplements']['supplements_optional'] as $index_supplement => $supplement) {
            //if ($item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["amount_extra"] == 1) {
            $item['options']['rate']['supplements']['supplements_optional'][$index_supplement]["key"] += 1;
            //}
        }

        Cart::add($faker->unique()->uuid, $item["name"], 1, $item["price"], 0, $item["options"]);

        Cart::remove($item["rowId"]);

        return $item;
    }

    public function change_item(Request $request)
    {
        $cart_items_id = explode(',', $request->post('cart_items_id'));
        foreach ($cart_items_id as $id) {
            Cart::remove($id);
        }

        $token_search = $request->post('token_search');
        $token_search_frontend = $request->post('token_search_frontend');
        $date_from = $request->post('date_from');
        $date_to = $request->post('date_to');
        $hotel = $request->post('upselling_item');

        $faker = \Faker\Factory::create();

        Cart::setGlobalTax(0);

        $cart_items_id = [];

        foreach ($hotel["best_options"]["rooms"] as $index_room => $room) {
            // $rates_id = [];
            foreach ($room["rates"] as $index_rate => $rate) {
                // array_push($rates_id, $rate["rateId"]);
                $cart_item = Cart::add($faker->unique()->uuid, $room["name"], 1, $rate["total"], 0, [
                    "date_from" => $date_from,
                    "date_to" => $date_to,
                    "token_search" => $token_search,
                    "token_search_frontend" => $token_search_frontend,
                    "hotel_name" => $hotel["name"],
                    "hotel_id" => $hotel["id"],
                    "room_id" => $room["room_id"],
                    "rate_id" => $rate["rateId"],
                    "best_option" => true,
                    "search" => $hotel,
                    "room" => $room,
                    "rate" => $rate,
                ]);

                array_push($cart_items_id, $cart_item->rowId);
            }
        }

        return Response::json(['success' => true, 'cart_items_id' => $cart_items_id]);
    }

    public function addSupplementServiceCart(Request $request)
    {

        $cart_item_id = $request->post('cart_item_id');
        $token_supplement = $request->post('token');
        $prices = $request->post('prices');
        $adults = $request->post('adults');
        $child = $request->post('child');
        $dates = $request->post('dates');
        $faker = \Faker\Factory::create();
        $item_cart = Cart::get($cart_item_id);
        Cart::setGlobalTax(0);
        $item = $item_cart->toArray();
        //Todo: buscamos por token del supplemento
        $key = array_search($token_supplement,
            array_column($item['options']['search']['supplements']['optional_supplements'], 'token_search'));
        if ($key !== false) {
            //Todo: buscamos el suplemento opcional seleccionado y lo quitamos de la lista de suplementos
            $item['options']['search']['supplements']['optional_supplements'][$key]['params']['adults'] = $adults;
            $item['options']['search']['supplements']['optional_supplements'][$key]['params']['child'] = $child;
            $item['options']['search']['supplements']['optional_supplements'][$key]['params']['dates'] = $dates;
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['price_per_adult'] = $prices['price_per_adult'];
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['price_per_child'] = $prices['price_per_child'];
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['price_per_person'] = $prices['price_per_person'];
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['total_adult_amount'] = $prices['total_adult_amount'];
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['total_amount'] = $prices['total_amount'];
            $item['options']['search']['supplements']['optional_supplements'][$key]['rate']['total_child_amount'] = $prices['total_child_amount'];
            $item['options']['search']['supplements']['supplements'][] = $item['options']['search']['supplements']['optional_supplements'][$key];
            $total_supplement_optional_selected = $this->getTotalSupplementOptionalSelected($item['options']['search']['supplements']);
            $item["price"] += $total_supplement_optional_selected;
            $item['subtotal'] = $item["price"];
            array_splice($item['options']['search']['supplements']['optional_supplements'], $key, 1);
            $item['options']['search']['supplements'] = $this->sumSupplements($item['options']['search']['supplements']);
            Cart::add($faker->unique()->uuid, $item["name"], 1, $item["price"], 0, $item["options"]);
            Cart::remove($item["rowId"]);
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => false]);
        }

    }

    public function deleteSupplementServiceItemCart(Request $request)
    {
        $cart_item_id = $request->post('cart_item_id');
        $token_supplement = $request->post('token');
        $faker = \Faker\Factory::create();
        $item_cart = Cart::get($cart_item_id);
        Cart::setGlobalTax(0);
        $item = $item_cart->toArray();

        //Todo: buscamos por token del supplemento
        $key = array_search($token_supplement,
            array_column($item['options']['search']['supplements']['supplements'], 'token_search'));

        //Todo: Si no existe el suplemento opcional seleccionado se agrega a la lista de suplementos
        if ($key !== false) {
            //Todo: buscamos el suplemento opcional seleccionado y lo quitamos de la lista de suplementos
            $total_supplement_optional_selected = $this->getTotalSupplementOptionalSelected($item['options']['search']['supplements']);
            $item['options']['search']['supplements']['optional_supplements'][] = $item['options']['search']['supplements']['supplements'][$key];
            $item["price"] = $item["price"] - $total_supplement_optional_selected;
            $item['subtotal'] = $item["price"];
            array_splice($item['options']['search']['supplements']['supplements'], $key, 1);
            $item['options']['search']['supplements'] = $this->sumSupplements($item['options']['search']['supplements']);
            Cart::add($faker->unique()->uuid, $item["name"], 1, $item["price"], 0, $item["options"]);
            Cart::remove($item["rowId"]);
            return Response::json(['success' => true]);
        } else {
            return Response::json(['success' => true]);
        }


    }

    public function update_multiservice_removed(Request $request)
    {

        $cart_item_id = $request->input('cart_item_id');
        $component_index = $request->input('component_index');
        $removed = $request->input('removed');

        $item_cart = Cart::get($cart_item_id);
        $item = $item_cart->toArray();
        $item["options"]["search"]["components"][$component_index]["removed"] = $removed;
        $update = Cart::update($cart_item_id, $item);

        return Response::json(['success' => true, 'update' => $update]);

    }

    public function update_service_reservation_time(Request $request)
    {

        $cart_item_id = $request->input('cart_item_id');
        $reservation_time = $request->input('reservation_time');
        try {
            $item_cart = Cart::get($cart_item_id);
            $item = $item_cart->toArray();
            $item["options"]["search"]["reservation_time"] = $reservation_time;
            $item["options"]["reservation_time"] = $reservation_time;
            $update = Cart::update($cart_item_id, $item);
            return Response::json(['success' => true, 'update' => $update]);
        } catch (\Exception $exception) {
            return Response::json(['success' => false, 'update' => $exception->getMessage()]);
        }
    }

    public function update_multiservice_substitute(Request $request)
    {

        $cart_item_id = $request->input('cart_item_id');
        $component_index = $request->input('component_index');
        $substitute_service_id = $request->input('substitute_service_id');

        $item_cart = Cart::get($cart_item_id);
        $item = $item_cart->toArray();

        $new_component = [];
        foreach ($item["options"]["search"]["components"][$component_index]["substitutes"] as $substitute) {
            if ($substitute["service_id"] == $substitute_service_id) {
                $new_component = $substitute;
                $new_component['lock'] = $item["options"]["search"]["components"][$component_index]['lock'];
                $new_component['after_days'] = $item["options"]["search"]["components"][$component_index]['after_days'];
                $new_component['sub_type'] = 'component';
                $new_component['substitutes'] = [];

                foreach ($item["options"]["search"]["components"][$component_index]["substitutes"] as $c_s_) {
                    if ($c_s_["service_id"] !== $substitute_service_id) {
                        array_push($new_component['substitutes'], $c_s_);
                    }
                }
                // Nuevo substitute
                $new_substitute = $item["options"]["search"]["components"][$component_index];
                $new_substitute['label'] = '[' . $item["options"]["search"]["components"][$component_index]['code'] . '] - ' . $item["options"]["search"]["components"][$component_index]['descriptions']['name'];
                $new_substitute['sub_type'] = "substitute";
                unset($new_substitute['substitutes']);

                array_push($new_component['substitutes'], $new_substitute);
                break;
            }
        }

        $item["options"]["search"]["components"][$component_index] = $new_component;


        $update = Cart::update($cart_item_id, $item);

        return Response::json(['success' => true, 'update' => $update]);

    }

}
