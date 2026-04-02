<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1"/>
            {{ $t('global.modules.services') }} {{name_service}}
            <div class="card-header-actions">
                <router-link :to="{ name: 'ServicesAdd1' }" v-if="showAddEcommerce">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
                <button class="btn btn-danger" v-if="showAdd" @click="will_add()">
                    {{ $t('global.buttons.add') }}
                </button>
            </div>
            <div class="card-header-actions">
                <button class="btn btn-danger" @click="goEdit()" v-if="showEdit">
                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon"/>
                    {{ $t('global.buttons.edit') }}
                </button>
            </div>
            <div class="card-header-actions">
                <button class="btn btn-danger" @click="goAdmin()" v-if="showManage">
                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon"/>
                    {{$t('services.manage')}}
                </button>
            </div>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    import { API } from './../../api'

    export default {
        data () {
            return {
                service_id: '',
                name_service: '',
                client_id: '',
            }
        },
        created: function () {
            this.client_id = window.localStorage.getItem('client_id')
            this.name_service = ''
            this.service_id = (this.$route.params.service_id === undefined) ? this.$route.params.id : this.$route.params.service_id
            this.$root.$on('updateTitleService', (payload) => {
                if (this.name_service === '') {
                    this.getNameService(payload.service_id)
                }
            })

            this.$root.$on('updateTitleServiceList', () => {
                this.name_service = ''
            })

        },
        computed: {
            showAddEcommerce () {
                return this.$route.name === 'ServicesList1' && this.$ability.can('create', 'services') && this.client_id != ''
            },
            showAdd () {
                return this.$route.name === 'ServicesList1' && this.$ability.can('create', 'services') && this.client_id == ''
            },
            showEdit () {
                return this.$route.name !== 'ServicesList1' && this.$route.name !== 'ServicesEdit1' && this.$ability.can('update', 'services')
            },
            showManage () {
                return  this.$route.name === 'ServicesEdit1' && this.$ability.can('update', 'services')
            }
        },
        mounted: function () {
            console.log(this.$route.name)
            this.service_id = (this.$route.params.service_id === undefined) ? this.$route.params.id : this.$route.params.service_id
            this.getNameService(this.service_id)
        },
        methods: {
            will_add(){
                this.$notify({
                    group: 'main',
                    type: 'info',
                    title: "Servicios",
                    text: 'Los servicios (equivalencias aurora), únicamente podrán ser importados. Primero crear en Stella, luego utilice la opción "Importar más Equivalencias"'
                })
            },
            goEdit () {
                this.service_id = this.$route.params.service_id
                this.getNameService(this.service_id)
                this.$router.push('/services_new/edit/' + this.service_id)
            },
            goAdmin () {
                this.service_id = this.$route.params.id
                this.getNameService(this.service_id)
                this.$router.push('/services_new/' + this.service_id + '/manage_service')
            },
            getNameService (service_id) {
                API.get('service/' + service_id + '/configuration')
                    .then((result) => {
                        this.name_service = '[' + result.data.data.aurora_code + ']' + ' - ' + result.data.data.name
                        window.localStorage.setItem('service_configuration', JSON.stringify(result.data.data))
                    }).catch(() => {
                    this.name_service = ''
                })
            }
        }
    }
</script>

<style lang="stylus">

</style>
