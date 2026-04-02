@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group row">
                    <label for="name" class="col-sm-2 col-form-label">Modulo</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" placeholder="Modulo" v-model="name">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <button class="btn btn-success" @click="saveModule">Guardar</button>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
            <th>#</th>
            <th>Modulo</th>
            <th>Acciones</th>
            </thead>
            <tbody>
                <tr v-for="module in modules">
                    <td>@{{ module.id }}</td>
                    <td>@{{ module.name }}</td>
                    <td>
                        <div class="btn-group dropright">
                            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="icon-list"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a :href="'/modules/'+module.id+'/translations'" class="dropdown-item">Translations</a>
                                <a :href="getRouteExportExcel(module.id)" target="_blank" class="dropdown-item">Export to Excel</a>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
@section('css')
<style>
.container{
    padding-top: 30px;
}
.vue-ads-flex-grow, .vue-ads-text-sm, .vue-ads-cursor-pointer, .vue-ads-pr-2.vue-ads-leading-loose, .vue-ads-w-6{
    font-size: 14px !important;
}
input{
    font-size: 14px !important;
    border: 1px solid #e2e8f0;
    padding: 5px 10px;
}
</style>

@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                modules:[],
                name:'',

            },
            created: function () {

            },
            mounted() {
                this.getModules()
            },
            computed: {},
            methods: {
                getRouteExportExcel:function(module_id){
                    return baseExternalURL+'/translations/'+module_id+'/export'
                },
                getModules:function()
                {
                    axios.get(
                        baseURL + 'modules'
                    )
                        .then((result) => {
                                this.modules = result.data
                        }).catch((e) => {
                        console.log(e)
                    })
                },
                saveModule:function () {
                    axios.post(
                        baseURL + 'modules',
                        {
                            name:this.name
                        }
                    )
                        .then((result) => {
                            this.name ="";
                            this.$toast.success(result.data.message, {
                                // override the global option
                                position: 'top-right'
                            })
                           this.getModules()

                        }).catch((e) => {
                        console.log(e)
                    })
                }
            }
        })
    </script>
@endsection
