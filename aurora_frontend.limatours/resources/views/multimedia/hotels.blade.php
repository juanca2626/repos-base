@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="col-lg-12">
            <div class="col-lg-4">
                <input type="text" class="form-control" v-model="code_hotel" placeholder="codigo de hotel" @keyup.enter="getHotelsMultimedia">
            </div>
        </div>
        <div class="col-lg-12">
            <table class="table table-bordered">
                <template v-for="hotel in hotels.data">
                    <tr>
                        <td><b>Codigo</b></td>
                        <td><b>Hotel</b></td>
                    </tr>
                    <tr>
                        <td>@{{ hotel.channels[0].pivot.code }}</td>
                        <td>@{{ hotel.name }}</td>
                    </tr>
                    <tr>
                        <td><b>Habitaciones</b></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><b>ID</b></td>
                        <td><b>Habitacion</b></td>
                    </tr>
                    <tr v-for="room in hotel.rooms">
                        <td>@{{ room.id }}</td>
                        <td>@{{ room.translations[0].value }}</td>
                    </tr>
                </template>
            </table>
            <div class="row">
                <div class="col-lg-6">
                    <button class="btn btn-primary" v-if="hotels.prev_page_url!=null" @click="setRouteMultimediaHotel(hotels.prev_page_url)">Pagina Anterior</button>
                </div>
                <div class="col-lg-6">
                    <button class="btn btn-primary" v-if="hotels.next_page_url!=null" @click="setRouteMultimediaHotel(hotels.next_page_url)">Pagina Siguiente</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                hotels: [],
                code_hotel: '',
                // baseExternalURL: window.baseExternalURL,
                route_multimedia_hotel: window.baseExternalURL+'api/hotels/multimedia/report?page=1'
            },
            created: function () {

            },
            mounted() {
                this.getHotelsMultimedia()
            },
            methods: {
                getHotelsMultimedia: function () {
                    let route = this.route_multimedia_hotel;

                    if(route.includes("http:"))
                    {
                        route =  route.replace('http','https')
                    }
                    if(this.code_hotel!='')
                    {
                        route+='&code_hotel='+this.code_hotel

                    }
                    axios.get(route).then((response) => {
                        this.route_multimedia_hotel = response.data.first_page_url
                        this.hotels = response.data
                    })
                },
                setRouteMultimediaHotel:function(route)
                {
                    route =  route.replace('http','https')
                  this.route_multimedia_hotel = route
                  this.getHotelsMultimedia()
                }
            }
        })
    </script>
@endsection
