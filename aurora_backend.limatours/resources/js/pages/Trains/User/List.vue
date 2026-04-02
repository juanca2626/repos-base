<template>
  <div class="container-fluid">

    <div class="row buttonTabs">
      <button v-for="t in train_companies"
              type="button" :class="{'btn btn-info':true, 'type_off': tab_code !=t.code}"
              @click="changeTypeTrain(t.id,t.code)" style="margin-left: 5px;">
        {{ t.name }}
      </button>
    </div>

    <div v-show="showAddUser" style="margin-bottom: 20px">
      <div class="row col-12">

        <div class="row col-12" style="margin-bottom: 15px;">
          <div class="col-8">
            Usuario:
            <vue-bootstrap-typeahead
                :data="users"
                :serializer="item => item.code + ' - ' + item.name"
                @hit="user = $event"
                v-model="userSearch"
            />
          </div>
          <div class="col-4">
            Código:
            <input class="form-control" type="text" v-model="train_user_code"
                   value="" placeholder="Ingrese el código">
          </div>
        </div>

        <button class="right btn btn-danger" type="button" @click="showAddUser=false">
          <font-awesome-icon :icon="['fas', 'ban']"/>
          {{$t('global.buttons.cancel')}}
        </button>
        <button class="right btn btn-success" type="button" @click="addUser()" style="margin-left: 5px;">
            <font-awesome-icon :icon="['fas', 'save']"/>
            {{$t('global.buttons.save')}}
        </button>
      </div>
    </div>

    <div class="row col-12">
      <div class="col-8">
        <input class="form-control" id="search_train_users" type="search" v-model="query_train_users"
               value="" placeholder="Buscar por nombre o código">
      </div>
      <div class="col-4 no-margin">
        <button class="right btn btn-danger" type="button"
                @click="willShowAddUser()">
          <font-awesome-icon :icon="['fas', 'plus']"/>
          {{$t('global.buttons.add')}}
        </button>
      </div>
    </div>

    <table class="VueTables__table table table-striped table-bordered table-hover">
      <thead>
      <tr>
        <th class="vueTable_column_code">
          <span title="" class="VueTables__heading">Código ({{ tab_code }})</span>
        </th>
        <th class="vueTable_column_package">
          <span title="" class="VueTables__heading">Usuario</span>
        </th>
        <th class="vueTable_column_email">
          <span title="" class="VueTables__heading">Correo Electrónico</span>
        </th>
        <th class="vueTable_column_role">
          <span title="" class="VueTables__heading">Rol</span>
        </th>
        <th class="vueTable_column_type">
          <span title="" class="VueTables__heading">Tipo</span>
        </th>
        <th class="vueTable_column_actions">
          <span title="" class="VueTables__heading">Acciones</span>
        </th>
      </tr>
      </thead>
      <tbody>
      <tr v-if="train_users.length>0" class="trPadding" v-for="train_user in train_users">
        <td class="vueTable_column_code">
          {{ train_user.code }}
        </td>
        <td class="vueTable_column_package">
          {{ train_user.user.code }} - {{ train_user.user.name }}
        </td>
        <td class="vueTable_column_email">
          {{ train_user.user.email }}
        </td>
        <td class="vueTable_column_role">
          {{ train_user.user.roles[0].name }}
        </td>
        <td class="vueTable_column_type">
          {{ train_user.user.user_type.description }}
        </td>
        <td class="vueTable_column_actions">
          <div class="table-actions">
            <div>
              <b-dropdown>
                <template v-slot:button-content>
                  <font-awesome-icon :icon="['fas', 'bars']"/>
                </template>
                <b-dropdown-item-button @click="showModal(train_user.id,train_user.user.name)" class="m-0 p-0">
                  <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                  {{$t('global.buttons.delete')}}
                </b-dropdown-item-button>
              </b-dropdown>
            </div>
          </div>
        </td>
      </tr>

      <tr v-if="train_users.length==0" class="trPadding">
        <td colspan="7">
          <center><img src="/images/loading.svg" v-if="loading" width="40px"/></center>
          <center><span v-if="!loading">Ninguno por mostrar</span></center>
        </td>
      </tr>

      </tbody>
    </table>
    <div class="VuePagination row col-md-12 justify-content-center">
      <nav class="text-center">
            <ul class="pagination VuePagination__pagination" style="">
                <li :class="{'VuePagination__pagination-item':true, 'page-item':true, 'VuePagination__pagination-item-prev-chunk':true,
                        'disabled':(pageChosenUser==1 || loading)}" @click="setPageRoute(pageChosenUser-1)">
                    <a href="javascript:void(0);" :disabled="(pageChosenUser==1 || loading)" class="page-link">&lt;</a>
                </li>
                <li v-for="page in train_user_pages" @click="setPageRoute(page)"
                    :class="{'VuePagination__pagination-item':true,'page-item':true,'active':(page==pageChosenUser), 'disabled':loading }">
                    <a href="javascript:void(0)" class="page-link active" role="button">{{ page }}</a>
                </li>
                <li :class="{'page-item':true,'VuePagination__pagination-item':true,'VuePagination__pagination-item-next-chunk':true,
                        'disabled':(pageChosenUser==train_user_pages.length || loading)}" @click="setPageRoute(pageChosenUser+1)">
                    <a href="javascript:void(0);" :disabled="(pageChosenUser==train_user_pages.length || loading)" class="page-link">&gt;</a>
                </li>
            </ul>
        </nav>
    </div>

    <b-modal :title="'Eliminar '+trainName" centered ref="my-modal" size="sm">
      <p class="text-center">¿Seguro que desea eliminar el elemento?</p>

      <div slot="modal-footer">
        <button @click="remove()" class="btn btn-success">{{$t('global.buttons.accept')}}</button>
        <button @click="hideModal()" class="btn btn-danger">{{$t('global.buttons.cancel')}}</button>
      </div>
    </b-modal>


    <block-page></block-page>
  </div>


</template>

<script>
    import { API } from './../../../api'
    import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
    import BlockPage from '../../../components/BlockPage'
    import BModal from 'bootstrap-vue/es/components/modal/modal'
    import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'

    export default {
        components: {
            BFormCheckbox,
            BlockPage,
            BModal,
            VueBootstrapTypeahead
        },
        data: () => {
            return {
                loading: false,
                loadingIcons: false,
                ratesChoosed: [],
                train_companies: [],
                user: null,
                users: [],
                trains: [],
                train_users: [],
                train_id: null,
                query_trains: '',
                query_train_users: '',
                trainName: '',
                pageChosen: 1,
                limit: 10,
                train_pages: [],
                pageChosenUser: 1,
                limitRoute: 10,
                train_user_pages: [],
                train_user_id: null,
                _train_id: null,
                showAddUser: false,
                showAddGeneral: false,
                showListGeneral: true,
                rail_route_name: '',
                train_user_code: '',
                tab_code: '',
                userSearch: '',
            }
        },
        mounted () {

            this.$i18n.locale = localStorage.getItem('lang')
            this.$root.$emit('updateTitleTrain', { title: 'Administrador de Usuarios de Trenes' })

            let search_train_users = document.getElementById('search_train_users')
            let timeout_route_trains
            search_train_users.addEventListener('keydown', () => {
                clearTimeout(timeout_route_trains)
                timeout_route_trains = setTimeout(() => {
                    this.pageChosenUser = 1
                    this.onUpdateByTrain()
                    clearTimeout(timeout_route_trains)
                }, 1000)
            })

            //trains
            API.get('/trains')
                .then((result) => {
                    this.train_companies = result.data.data
                    if (this.train_companies.length > 0) {
                        this._train_id = this.train_companies[0].id
                        this.tab_code = this.train_companies[0].code
                        this.onUpdateByTrain()
                    }
                }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('services.error.messages.name'),
                        text: this.$t('services.error.messages.connection_error')
                    })
            })

            //users
            API.get('/users')
                .then((result) => {
                    this.users = result.data.data
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('zones.error.messages.name'),
                    text: this.$t('zones.error.messages.connection_error')
                })
            })

        },
        created () {
            localStorage.setItem('trainnamemanage', '')
        },
        computed: {},
        methods: {
            willShowAddUser () {
                this.showAddUser = true
                this.train_user_code = ''
            },
            addUser () {

                if (this.user == null) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: 'Por favor ingrese un usuario'
                    })
                    return
                }
                if (this.train_user_code == '') {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Datos incompletos',
                        text: 'Por favor ingrese un código'
                    })
                    return
                }

                this.loading = true

                API({
                    method: "POST",
                    url: "train_users",
                    data: {
                        code: this.train_user_code,
                        train_id: this._train_id,
                        user_id: this.user.id,
                    }
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.$notify({
                                group: 'main',
                                type: 'success',
                                title: 'Modulo de Trenes',
                                text: 'Guardado correctamente'
                            })
                            this.onUpdateByTrain()
                            this.showAddUser = false
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Modulo de Trenes',
                                text: result.data.message
                            })
                        }
                        this.loading = true
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error Modulo Trenes',
                        text: this.$t('trains.error.messages.connection_error')
                    })
                })
            },
            changeTypeTrain (_type_id, _code) {
                this.tab_code = _code
                this._train_id = _type_id
                this.onUpdateByTrain()
                this.showAddUser = false
            },
            onUpdate () {

                this.loading = true
                this.trains = []

                API({
                    method: 'GET',
                    url: 'train_classes?queryCustom=' + this.query_trains +
                        '&page=' + this.pageChosen + '&limit=' + this.limit
                })
                    .then((result) => {
                        this.train_pages = []
                        for (let i = 0; i < (result.data.count / this.limit); i++) {
                            this.train_pages.push(i + 1)
                        }

                        this.trains = result.data.data
                        this.loading = false

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error Modulo Trenes',
                        text: this.$t('trains.error.messages.connection_error')
                    })
                })
            },
            onUpdateByTrain () {

                this.loading = true
                this.train_users = []

                API({
                    method: 'GET',
                    url: 'train_users/train/' + this._train_id +
                        '?queryCustom=' + this.query_train_users +
                        '&page=' + this.pageChosenUser + '&limit=' + this.limitRoute
                })
                    .then((result) => {
                        this.train_user_pages = []
                        for (let i = 0; i < (result.data.count / this.limitRoute); i++) {
                            this.train_user_pages.push(i + 1)
                        }

                        this.train_users = result.data.data
                        this.loading = false

                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Error Modulo Trenes',
                        text: this.$t('trains.error.messages.connection_error')
                    })
                })
            },
            remove () {

                API({
                    method: 'DELETE',
                    url: 'train_users/' + this.train_user_id
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            this.onUpdateByTrain()
                            this.hideModal()
                        } else {
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Modulo de Trenes',
                                text: result.data.message
                            })
                        }
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Modulo de Trenes',
                        text: this.$t('global.error.messages.connection_error')
                    })
                })
            },
            hideModal () {
                this.$refs['my-modal'].hide()
            },
            showModal (id, name) {
                this.train_user_id = id
                this.trainName = name
                this.$refs['my-modal'].show()
            },
            setPage (page) {
                if (page < 1 || page > this.train_pages.length) {
                    return
                }
                this.pageChosen = page
                this.onUpdate()
            },
            setPageRoute (page) {
                if (page < 1 || page > this.train_user_pages.length) {
                    return
                }
                this.pageChosenUser = page
                this.onUpdateByTrain()
            },
            changeStatus (me) {
                this.loadingIcons = true

                API.put('/train_class/' + me.id + '/status', { status: !me.status })
                    .then((result) => {
                        if (result.data.success) {
                            me.status = !me.status
                        } else {
                            console.log(result)
                            this.$notify({
                                group: 'main',
                                type: 'error',
                                title: 'Modulo de Trenes',
                                text: this.$t('services.error.messages.connection_error')
                            })
                        }
                        this.loadingIcons = false
                    }).catch(() => {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Modulo de Trenes',
                        text: this.$t('services.error.messages.connection_error')
                    })
                })
            }
        },
        filters: {
            formatDate: function (_date) {
                if (_date == undefined) {
                    // console.log('fecha no parseada: ' + _date)
                    return
                }
                _date = _date.split('-')
                _date = _date[2] + '/' + _date[1] + '/' + _date[0]
                return _date
            }
        }
    }
</script>

<style lang="stylus">
  .table-actions {
    display: flex;
  }

  .trExtension, .trExtension > th, .trExtension > td {
    background-color: #e9eaff;
  }

  .trExtension:hover, .trExtension:hover > th, .trExtension:hover > td {
    background-color: #e2e3ff;
  }

  .VueTables__limit {
    display: none;
  }

  .no-margin {
    padding-left: 0;
    padding-bottom: 5px !important;
    padding-right: 0px;
  }

  .trPadding, .trPadding > th, .trPadding > td {
    padding: 10px !important;
  }

  .check_true, .check_1 {
    color: #04bd12;
  }

  .el_disabled {
    opacity: 0.5;
  }

  .type_off {
    opacity: 0.5;
  }

  .buttonTabs {
    margin-bottom: 10px;
    padding-bottom: 10px !important;
    padding-left: 15px;
    border-bottom: solid 1px #a5c6c6;
  }


</style>
