<template>
    <div>
        <div class="container">
            <h2>GESTIÓN DE USUARIOS - TOM</h2>
            <div class="mt-5">
                <a href="javascript:;" v-on:click="toggleModal( 0, 'add', { translations: translations } )" data-toggle="modal" data-target="#add-modal" class="btn btn-secondary btn-lg">Agregar</a>
                <a href="javascript:;" v-on:click="exportExcel()" class="btn btn-success btn-lg">Exportar Excel</a>
            </div>
            <div class="mt-5">
                <div class="alert alert-warning mt-3 mb-3" v-if="loading">
                    <p class="mb-0">Cargando..</p>
                </div>
                <div v-if="!loading">
                    <div class="alert alert-warning" v-if="quantity == 0">
                        <p class="mb-0">No se encontró información para mostrar. Por favor, intente con nuevos filtros.</p>
                    </div>
                    <div class="container-fluid" v-if="quantity > 0">
                        <table class="table table-striped" id="_executives">
                            <thead>
                            <tr>
                                <th>REGION</th>
                                <th>REGIONAL</th>
                                <th>JEFE / SUPERVISOR</th>
                                <th>ESPECIALISTA</th>
                                <th class="center">ACCIONES</th>
                            </tr>
                            </thead>
                            <tbody>
                                <template v-for="(executive, e) in executives">
                                    <tr v-bind:key="'executive-' + e">
                                        <td>{{ executive.REGION }}</td>
                                        <td>{{ executive.JEFE_REGIONAL }}</td>
                                        <td>{{ executive.JEFE }}</td>
                                        <td>{{ executive.NOMESP }}</td>
                                        <td class="center">
                                            <a v-if="executive.INACTIVO == 0" class="edit btn-effect btn-check btn btn-xs" title="Desactivar" v-bind:disabled="loading_button" v-on:click="inactive( e )" href="javascript:;">
                                                <i class="fa fa-user fa-2x text-success" style="min-width:27px;"></i>
                                            </a>
                                            <a v-if="executive.INACTIVO >= 1" class="edit btn-effect btn-check btn btn-xs" title="Activar" v-bind:disabled="loading_button" v-on:click="active( e )" href="javascript:;">
                                                <i class="fa fa-user-times fa-2x text-danger" style="min-width:27px;"></i>
                                            </a>

                                            <a class="edit btn-effect btn-check btn btn-xs" title="Editar usuario" data-toggle="modal" data-target="#update-modal" v-on:click="toggleModal( e, 'update', { translations: translations, executive: executive } )" href="javascript:;">
                                                <i class="fa fa-edit fa-2x"></i>
                                            </a>

                                            <a class="edit btn-effect btn-check btn btn-xs" title="Disponibilidad" data-toggle="modal" data-target="#vacations-modal" v-on:click="toggleModal( e, 'vacations', { translations: translations, executive: executive } )" href="javascript:;">
                                                <i class="fa fa-calendar fa-2x"></i>
                                            </a>

                                            <a class="edit btn-effect btn-check btn btn-xs" title="Asignar Clientes" v-on:click="alert_('Las asignaciones de clientes ahora son desde el backend')" href="javascript:;">
                                                <i class="fa fa-user-friends fa-2x"></i>
                                            </a>

        <!--                                    <a class="edit btn-effect btn-check btn btn-xs" title="Asignar Clientes" data-toggle="modal" data-target="#customers-modal" v-on:click="toggleModal( e, 'customers', { translations: translations, executive: executive, identi: 'J' } )" href="javascript:;">-->
        <!--                                        <i class="fa fa-user-friends fa-2x"></i>-->
        <!--                                    </a>-->

                                            <!-- a class="edit btn-effect btn-check btn btn-xs" title="Asignar Clientes - Ignorando la cola de atención" data-toggle="modal" data-target="#customers-modal" v-on:click="toggleModal( e, 'customers', { translations: translations, executive: executive, identi: 'K' } )" href="javascript:;">
                                                <i class="fa fa-users fa-2x"></i>
                                            </a -->

                                            <a class="edit btn-effect btn-check btn btn-xs" title="Asignar Países" data-toggle="modal" data-target="#countries-modal" v-on:click="toggleModal( e, 'countries', { translations: translations, executive: executive } )" href="javascript:;">
                                                <i class="fa fa-globe fa-2x"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <component ref="template" v-bind:is="modal" v-bind:data="dataModal"></component>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['translations'],
        data: () => {
            return {
                data: [],
                flag: true,
                lang: '',
                loading: false,
                loading_button: false,
                modal: '',
                dataModal: {},
                quantity: 0,
                executives: [],
                index: 0
            }
        },
        created: function () {
            this.lang = localStorage.getItem('lang')
            this.searchUsers()
        },
        mounted: function() {

        },
        computed: {

        },
        methods: {
            alert_(message){
                this.$toast.info(message, {
                    // override the global option
                    position: 'top-right'
                })
            },
            toggleModal: function(index, _modal, _data) {
                this.index = index
                this.dataModal = _data
                this.modal = 'user-' + _modal + '-modal'
                let vm = this

                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            _updateExecutive: function (_executive) {
                this.executives[index] = _executive
            },
            _closeModal: function () {
                let vm = this

                $('.modal').modal('hide');
                setTimeout(function () {
                    vm.modal = ''
                }, 10)
            },
            exportExcel: function () {
                axios.post(
                    baseURL + 'users/exportTOM', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false
                        this.downloadExcel()
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            downloadExcel: function () {
                window.location = baseURL + 'export_excel?type=usersTOM&table=';
            },
            active: function (index)
            {
                this.loading_button = true
                axios.post(
                    baseURL + 'users/updateStateTOM', {
                        lang: this.lang,
                        executive: this.executives[index].NOMESP,
                        state: 1
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.executives[index].INACTIVO = 0

                        this.$toast.success('Usuario actualizado correctamente.', {
                            // override the global option
                            position: 'top-right'
                        })
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            inactive: function (index)
            {
                this.loading_button = true
                axios.post(
                    baseURL + 'users/updateStateTOM', {
                        lang: this.lang,
                        executive: this.executives[index].NOMESP,
                        state: 0
                    }
                )
                    .then((result) => {
                        this.loading_button = false
                        this.executives[index].INACTIVO = 1

                        this.$toast.success('Usuario actualizado correctamente.', {
                            // override the global option
                            position: 'top-right'
                        })
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            searchUsers: function () {
                this.loading = true

                axios.post(
                    baseURL + 'users/searchTOM', {
                        lang: this.lang
                    }
                )
                    .then((result) => {
                        this.loading = false

                        const seen = new Set()
                        const filtered = result.data.users.filter(user => {
                            const nomesp = user.NOMESP?.trim()
                            if (seen.has(nomesp)) {
                                return false
                            }
                            seen.add(nomesp)
                            return true
                        })

                        this.executives = filtered
                        this.quantity = filtered.length

                        if(this.quantity > 0)
                        {
                            setTimeout(function(){
                                $('#_executives').DataTable({
                                    ordering: false,
                                    language: {
                                        "sProcessing":     "Procesando...",
                                        "sLengthMenu":     "Mostrar _MENU_ registros",
                                        "sZeroRecords":    "No se encontraron resultados",
                                        "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
                                        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                                        "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                                        "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                                        "sInfoPostFix":    "",
                                        "sSearch":         "Buscar:",
                                        "sUrl":            "",
                                        "sInfoThousands":  ",",
                                        "sLoadingRecords": "Cargando...",
                                        "oPaginate": {
                                            "sFirst":    "Primero",
                                            "sLast":     "Último",
                                            "sNext":     "Siguiente",
                                            "sPrevious": "Anterior"
                                        },
                                        "oAria": {
                                            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                                            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                                        },
                                        "buttons": {
                                            "copy": "Copiar",
                                            "colvis": "Visibilidad"
                                        }
                                    }
                                })
                            }, 10)
                        }
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            }
        }
    };
</script>
