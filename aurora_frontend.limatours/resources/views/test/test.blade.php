@extends('layouts.app')
@section('li_menu_new')
    <li class="nav-item" v-if="{{ Auth::user()->user_type_id === 3 }}">
        <select name="" id="" v-model="client_id">
            <option value="1">Client 1</option>
        </select>
    </li>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                        You are logged in!
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data:{
                client_id:1
            },
            created: function () {
                console.log('instancia de vue ejecutada')
            },
            mounted(){
                this.getHotels()
            },
            methods:{
               getHotels:function() {

                   axios.get('api/hotels')
                       .then(response =>{
                           this.users = response.data
                       })
                       .catch(error => {

                           console.log(error);

                       });
               }
            }
        })
    </script>
@endsection