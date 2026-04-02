@extends('layouts.app')
@section('content')
    <section class="page-board">
        <div class="container">
            <h2> Pasajeros Pendientes </h2>

            <div class="row my-5" id="results" v-if="(bossFlag != bossFlagSearch || bossFlag == 0) && quantity > 0 && !loading">
                <h4 class="colors-filters">
                    <i v-bind:class="[ icon, _class, 'mr-3' ]"></i>
                    @{{ title }}
                </h4>
                <div class="col-12">
                    <div class="table-responsive-sm">
                        <table class="table text-center">
                            <thead>
                            <tr>
                                <th scope="col" class="text-muted">VIP</th>
                                <th scope="col" class="text-muted">FILE</th>
                                <th scope="col" class="text-muted">NOMBRE</th>
                                <th scope="col" class="text-muted">CLIENTE</th>
                                <th scope="col" class="text-muted">INGRESO</th>
                                <th scope="col" class="text-muted">MONTO</th>
                                <th scope="col" class="text-muted">#PAX</th>
                                <th v-for="(module, m) in modules" scope="col" class="text-muted">
                                    <span v-bind:title="[ module.value ]" v-b-tooltip.hover>@{{ module.key }}</span>
                                </th>
                                <th scope="col" class="text-muted">NOTAS</th>
                                <th scope="col" class="text-muted">ITINERARIO</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            <tr v-for="(file, f) in files">
                                <th scope="row" class="align-middle" style="color: #BBBDBF;"><i v-bind:class="[ (file.detalle[6] == 'VIP') ? 'icon-star' : '' ]"></i></th>
                                <td><a v-bind:href='getUrl(f)' target="_blank">@{{ f }}</a></td>
                                <td>@{{ file.detalle[1]  }}</td>
                                <td><strong>@{{ file.detalle[2] }}</strong></td>
                                <td>@{{ file.detalle[3] }}</td>
                                <td>@{{ file.detalle[5] }}</td>
                                <td>@{{ file.detalle[4] }}</td>
                                <td v-for="(module, m) in modules">
                                    <a v-bind:href="getUrlDetail(file[m], module.url, f)" v-if="file[m] == 1" v-bind:target="getTarget(module.url)" class="a-icon">
                                        <i v-bind:class="[ file[m] == 1 ? 'icon-x' : 'icon-check' ]" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                                    </a>
                                    <i v-bind:class="[ file[m] == 1 ? 'icon-x' : 'icon-check' ]" v-if="file[m] != 1" v-bind:title="[ module.value ]" v-b-tooltip.hover></i>
                                </td>
                                <td><a href="#" v-on:click="$bvModal.show('my-modal'); loadNotes(f)" class="line-none"><i class="icon-file-text" v-bind:title="[ file.notas ]" v-b-tooltip.hover></i></a></td>
                                <td><a v-bind:href='getUrlItinerary(f)'>detalles</a></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row my-5" v-if="(bossFlag != bossFlagSearch || bossFlag == 0) && quantity == 0">
                <div class="alert alert-warning" style="width:100%">
                    No hay información para mostrar. Por favor, seleccione una categoría de la tabla principal.
                </div>
            </div>
        </div>
    </section>
@endsection
@section('css')
    <style>
    </style>
@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                update_menu:1
            },
            created: function () {
            },
            mounted: function() {
            },
            computed: {},
            methods: {}
        });
    </script>
@endsection
