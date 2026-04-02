<template>
    <div class="container">
        <div class="page-hoja-master page-hojas-master">
            <div class="contenedor">
                <h1 class="titulo">Grupos & Series</h1>
                <div class="contenedor">
                    <div class="input-filtrar">

                        <div class="input-icono-filtrar">
                            <input type="text" :placeholder="translations.label.filter" :disabled="loading" id="query_search"
                                   v-model="query_search"><i class="icon-serie-drag"></i>
                        </div>

                        <button id="modal_force_1" data-toggle="modal" data-target="#modal-info" @click="forceModal()"></button>
                        <button id="modal_force_2" data-toggle="modal" data-target="#modal-import-master-sheet" @click="forceModal()"></button>
                        <button id="modal_force_3" data-toggle="modal" data-target="#modal-import-serie" @click="forceModal()"></button>

                        <div class="buscar dropdown dropdown-importar">
                            <button class="button-red dropdown-toggle" data-toggle="dropdown" data-placement="bottom">
                                {{ translations.label.create_new }}
                            </button>
                            <div class="dropdown-menu dropdown-nota-mensaje">
                                <div class="dropdown-content">
                                    <a href="javascript:;"
                                       @click="toggleModal( 2, 'import-master-sheet', { translations: translations, action_after : 'go_edit' } )">
                                        {{ translations.label.import }} {{ translations.label.title }}
                                    </a>
                                    <a href="javascript:;"
                                       @click="toggleModal( 3, 'import-serie', { translations: translations, action_after : 'go_edit' } )">
                                        {{ translations.label.import }} Grupos & Series
                                    </a>
                                    <a href="javascript:;"
                                       @click="toggleModal( 1, 'edit', { translations: translations } )">
                                        Iniciar en Blanco
                                    </a>
                                </div>
                            </div>
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
                            <div class="paxes"><span class="icon-layers1 f-16"></span></div>
                            <div class="paxes"><span class="icon-thumbs-up f-16"></span></div>
                            <div class="paxes"><span class="icon-thumbs-down f-16"></span></div>
                            <div class="paxes"><span class="icon-inbox f-16"></span></div>
                            <div class="leaders"><span class="icon-mail f-16"></span></div>
                            <div class="compartir"></div>
                            <div class="editar"></div>
                            <div class="borrar"></div>
                        </div>
                        <div class="hojas-master-body">
                            <div class="celda num-pax" v-for="s in series">
                                <div class="codigo">S-{{ s.id }}</div>
                                <div class="descripcion">{{ s.name }}</div>
                                <div class="cliente">{{ s.user.name }}</div>
                                <div class="salida">{{ formatDate( s.date_start, '-', '-', 1 ) }}</div>
                                <div class="paxes zero-disabled">0</div>
                                <div class="paxes approved">15</div>
                                <div class="paxes observed">2</div>
                                <div class="paxes order">5</div>
                                <div class="leaders nmessage d-flex">{{ s.messages_count }} <div class="circle-message"></div></div>
                                <div class="compartir" v-if="s.user_id === user_id"
                                     @click="toggleModal( 0, 'share', { translations: translations, serie_id : s.id } )"
                                     data-toggle="modal" data-target="#modal-compartir">
                                    <button><i class="icon-share-2"></i><span>({{ s.users_count }})</span></button>
                                </div>
                                <div class="compartir" v-else>
                                    <button :disabled="true" class="icon-disabled"><i class="icon-share-2"></i><span>{{ s.users_count }}</span></button>
                                </div>
                                <div class="editar" @click="edit_link(s)">
                                    <button><i class="icon-edit"></i></button>
                                </div>
                                <div class="borrar" v-if="s.user_id === user_id"
                                     @click="toggleModal( 0, 'remover', { translations: translations, serie_id : s.id, action_after : 'search_parent' } )"
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
    export default {
        props: ['translations'],
        data: () => {
            return {
                lang: '',
                loading: false,
                baseExternalURL: window.baseExternalURL,
                series: [],
                modal: '',
                dataModal: {},
                query_search: '',
                pages: [],
                page_chosen: 1,
                limit: 5,
                user_invited: false,
                user_id: null,
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
                this.modal = 'serie-modal-' + _modal

                let vm = this
                if( this.index === 0 ){
                    setTimeout(function() {
                        vm.$refs.template.load()
                    }, 100)
                } else {
                    let el = document.getElementById('modal_force_'+this.index)
                    setTimeout(function() {
                        el.click()
                    }, 100)
                }
            },
            forceModal(){
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
                window.location = baseURL + 'serie/' + btoa(me.id)
            },
            search () {
                this.loading = true

                axios.get(
                    baseExternalURL + 'api/series?query='+this.query_search
                    +'&page='+this.page_chosen+'&limit='+this.limit
                )
                    .then((result) => {
                        this.loading = false
                        this.series = result.data.data

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
