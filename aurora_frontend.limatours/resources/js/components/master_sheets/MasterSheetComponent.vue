<template>
    <div class="container">
        <div class="page-hoja-master page-hojas-master">
            <div class="contenedor">
                <h1 class="titulo">{{ translations.label.title }}</h1>
                <div class="contenedor">
                    <div class="input-filtrar">
                        <div class="input-icono-filtrar">
                            <input type="text" :placeholder="translations.label.filter" :disabled="loading" id="query_search"
                                   v-model="query_search"><i class="icon-serie-drag"></i>
                        </div>
                        <div class="buscar">
                            <button class="button-red"
                                    @click="toggleModal( 0, 'edit', { translations: translations } )"
                                    data-toggle="modal" data-target="#modal-info">{{ translations.label.create_new }}</button>
                        </div>
                    </div>
                </div>
                <div class="contenedor">
                    <div class="hojas-master">
                        <div class="hojas-master-header">
                            <div class="codigo">{{ translations.label.code | upper }}<i class="icon-serie-eliminar"></i></div>
                            <div class="descripcion">{{ translations.label.description | upper }}</div>
                            <div class="cliente">{{ translations.label.customer | upper }}</div>
                            <div class="salida">{{ translations.label.departure | upper }}</div>
                            <div class="paxes">{{ translations.label.paxes | upper }}</div>
                            <div class="leaders">{{ translations.label.leaders | upper }}</div>
                            <div class="compartir"></div>
                            <div class="editar"></div>
                            <div class="borrar"></div>
                        </div>
                        <div class="hojas-master-body">
                            <div class="celda" v-for="ms in master_sheets">
                                <div class="codigo">HM-{{ ms.id }}</div>
                                <div class="descripcion">{{ ms.name }}</div>
                                <div class="cliente">{{ ms.user.name }}</div>
                                <div class="salida">{{ formatDate( ms.date_out, '-', '-', 1 ) }}</div>
                                <div class="paxes">{{ ms.paxes }}</div>
                                <div class="leaders">{{ ms.leader }}</div>
                                <div class="compartir" v-if="ms.user_id === user_id"
                                     @click="toggleModal( 0, 'share', { translations: translations, master_sheet_id : ms.id } )"
                                     data-toggle="modal" data-target="#modal-compartir">
                                    <button><i class="icon-share-2"></i><span>{{ ms.users_count }}</span></button>
                                </div>
                                <div class="compartir" v-else>
                                    <button :disabled="true" class="icon-disabled"><i class="icon-share-2"></i><span>{{ ms.users_count }}</span></button>
                                </div>
                                <div class="editar" @click="edit_link(ms)">
                                    <button><i class="icon-edit"></i></button>
                                </div>
                                <div class="borrar" v-if="ms.user_id === user_id"
                                     @click="toggleModal( 0, 'remover', { translations: translations, master_sheet_id : ms.id, action_after : 'search_parent' } )"
                                     data-toggle="modal" data-target="#modal-borrar-itinerario">
                                    <button><i class="icon-trash-2"></i></button>
                                </div>
                                <div class="borrar" v-else>
                                    <button :disabled="true"><i class="icon-trash-2 icon-disabled"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <nav aria-label="page navigation">
                        <div class="text-center">
                            <ul class="pagination">
                                <li :class="{'page-item':true,'disabled':(page_chosen==1)}"
                                    @click="setPage(page_chosen-1)">
                                    <a class="page-link" href="#"><</a>
                                </li>

                                <li v-for="page in pages" @click="setPage(page)"
                                    :class="{'page-item':true,'active':(page==page_chosen) }">
                                    <a class="page-link" href="javascript:;">{{ page }}</a>
                                </li>

                                <li :class="{'page-item':true,'disabled':(page_chosen==pages.length)}"
                                    @click="setPage(page_chosen+1)">
                                    <a class="page-link" href="#">></a>
                                </li>
                            </ul>
                        </div>

                    </nav>
                </div>
            </div>
        </div>
        <component ref="template" v-bind:is="modal" v-bind:data="dataModal"></component>
    </div>
</template>
<script>
    import store from "../../store"
    export default {
        props: ['translations'],
        store,
        data: () => {
            return {
                lang: '',
                loading: false,
                baseExternalURL: window.baseExternalURL,
                master_sheets: [],
                modal: '',
                dataModal: {},
                query_search: '',
                pages: [],
                page_chosen: 1,
                limit: 5,
                user_invited: false,
                user_id: null
            }
        },
        created: function () {
            this.search()
        },
        mounted: function() {
            this.lang = localStorage.getItem('lang')
            this.user_id = parseInt( localStorage.getItem('user_id') )

            this.user_invited = ( localStorage.getItem('code') === 'guest' )
            if( this.user_invited ){
                window.location = '/error'
            }

            let query_search = document.getElementById('query_search')
            let timeout_
            query_search.addEventListener('keydown', () => {
                clearTimeout(timeout_);
                timeout_ = setTimeout(() => {
                    this.page_chosen = 1
                    this.search()
                    clearTimeout(timeout_)
                }, 1000);
            });
        },
        computed: {

        },
        methods: {
            setPage(page) {
                if (page < 1 || page > this.pages.length) {
                    return;
                }
                this.page_chosen = page
                this.search()
            },
            toggleModal: function(index, _modal, _data) {
                this.index = index
                this.dataModal = _data
                this.modal = 'master-sheet-modal-' + _modal
                let vm = this

                setTimeout(function() {
                    vm.$refs.template.load()
                }, 100)
            },
            _closeModal: function () {
                this.modal = ''
                document.getElementsByClassName('modal-backdrop')[0].remove()
            },
            edit_link( me ){
                window.location = baseURL + 'master-sheet/' + btoa(me.id)
            },
            search () {
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/master_sheet?query='+this.query_search
                    +'&page='+this.page_chosen+'&limit='+this.limit
                )
                    .then((result) => {
                        this.loading = false
                        this.master_sheets = result.data.data

                        // Pagination
                        this.pages = [];
                        for (let i = 0; i < (result.data.count / this.limit); i++) {
                            this.pages.push(i + 1);
                        }
                        // Pagination
                    })
                    .catch((e) => {
                        console.log(e)
                    })
            },
            formatDate (_date, charFrom, charTo, orientation) {
                _date = _date.split(charFrom)
                _date =
                    (orientation)
                        ? _date[2] + charTo + _date[1] + charTo + _date[0]
                        : _date[0] + charTo + _date[1] + charTo + _date[2]
                return _date
            }
        },
        filters:{
            upper (value){
                return (value!==undefined) ? value.toUpperCase() : value
            }
        }
    }
</script>
<style>
    .icon-disabled{
        color: #b4b4b4;
    }
</style>
