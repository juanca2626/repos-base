<?php

namespace App\Http\Traits;

use App\Hotel;
use App\HotelUser;

trait AccessInfo
{
    /**
     * @return string
     */
    private function getUserIDSession()
    {
        $user = auth()->user();
        $id_user = $user->toArray();
        $iduser = $id_user['id'];
        return $iduser;
    }

    /**
     * @return array
     */
    private function getHotelUserbyUser($idususario)
    {

        $hotel_user = HotelUser::where('user_id', $idususario)->get();
        $range = 0;
        $hotel_id = 0;
        foreach ($hotel_user as $datauser) {
            $range = $datauser->range;
            $hotel_id = $datauser->hotel_id;
        }

        $data = [
            'range' => $range,
            'hotel_id' => $hotel_id,
        ];

        return $data;
    }

    /**
     * @return bool
     */
    private function getVerifAdminRolSession()
    {
        //verifica si tiene rol admin o no
        $user = auth()->user();
        $rol_asig = false;
        $user_roles = $user->roles()->get();
        foreach ($user_roles as $user_role) {
            if ($user_role->id == 1) {
                $rol_asig = true;
            }
        }
        return $rol_asig;
    }

    /**
     * @return array
     */
    public function getAccessViewHotel()
    {

        $idususario = $this->getUserIDSession();
        $verroladmin = $this->getVerifAdminRolSession();
        $data_user = $this->getHotelUserbyUser($idususario);
        $cadena = 0;

        if ($data_user['hotel_id'] != 0) {
            $chain_user = Hotel::find($data_user['hotel_id']);
            $cadena = $chain_user->chain_id;
        }

        $data = [
            'chain' => $cadena,
            'iduser' => $idususario,
            'flagroladmin' => $verroladmin,
            'range' => $data_user['range'],
            'hotel_id' => $data_user['hotel_id']
        ];
        return $data;
    }
}
