<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'hotel']" class="mr-1"/>
            <span v-if="title != '' && this.$route.name != 'HotelsList'">{{title}}</span>
            <span v-else>Hoteles</span>
            <div class="card-header-actions">
                <router-link :to="{ name: 'HotelsAdd' }" v-if="showAdd" class="mr-2">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
                <button class="btn-import" @click="showImportModal()" v-if="showImport">
                    <font-awesome-icon :icon="['fas', 'exchange-alt']" class="nav-icon"/>
                    Importar Hoteles
                </button>
                <button class="btn-info position-relative mr-2" @click="showBatchesModal()" type="button" v-if="showImport">
                    <font-awesome-icon :icon="['fas', 'sync-alt']" class="nav-icon"/>
                    <span v-if="importBatchesCount > 0" class="badge badge-danger position-absolute"
                          style="top: -5px; right: -5px; font-size: 0.7em;">
                        {{ importBatchesCount }}
                    </span>
                </button>
            </div>
            <div class="card-header-actions">
                <button class="btn btn-danger" @click="goEdit()" v-if="showEdit">
                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon"/>
                    {{ $t('global.buttons.edit') }}
                </button>
            </div>
            <div class="card-header-actions" style="margin-right:50px;">
                <button class="btn btn-danger" @click="goAdmin()" v-if="showManage">
                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon"/>
                    {{$t('hotels.manage_hotel')}}
                </button>
            </div>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>

        <!-- Modal de batches de importación -->
        <b-modal title="Estado de Importaciones" ref="my-modal-batches" size="lg" hide-footer>
            <div v-if="loadingBatches" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>
            <div v-else>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: activeTab === 'pending' }"
                               @click.prevent="activeTab = 'pending'" href="#">
                                Pendientes
                                <span v-if="pendingBatches.length > 0" class="badge badge-warning ml-1">
                                    {{ pendingBatches.length }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: activeTab === 'completed' }"
                               @click.prevent="activeTab = 'completed'" href="#">
                                Completados
                                <span v-if="completedBatches.length > 0" class="badge badge-success ml-1">
                                    {{ completedBatches.length }}
                                </span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: activeTab === 'failed' }"
                               @click.prevent="activeTab = 'failed'" href="#">
                                Fallidos
                                <span v-if="failedBatches.length > 0" class="badge badge-danger ml-1">
                                    {{ failedBatches.length }}
                                </span>
                            </a>
                        </li>
                    </ul>
                    <button @click="refreshBatches()" class="btn btn-sm btn-outline-primary"
                            :disabled="refreshingBatches">
                        <font-awesome-icon :icon="['fas', 'sync']"
                            :class="{ 'fa-spin': refreshingBatches }" class="mr-1"/>
                        Actualizar
                    </button>
                </div>

                <!-- Contenido de pendientes -->
                <div v-show="activeTab === 'pending'">
                    <div v-if="pendingBatches.length === 0" class="alert alert-info">
                        No hay importaciones pendientes.
                    </div>
                    <div v-else>
                        <div v-for="batch in pendingBatches" :key="batch.id" class="alert alert-warning mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">
                                        <font-awesome-icon :icon="['fas', 'clock']" class="mr-2" v-if="batch.status === 'pending'"/>
                                        <font-awesome-icon :icon="['fas', 'spinner']" class="mr-2 fa-spin" v-if="batch.status === 'processing'"/>
                                        {{ batch.status === 'pending' ? 'Importación Pendiente' : 'Importación en Proceso' }}
                                    </h6>
                                    <p class="mb-1">{{ batch.message }}</p>
                                    <!-- Lista de hoteles importados -->
                                    <div v-if="batch.imported_hotels && batch.imported_hotels.length > 0" class="mt-2">
                                        <div v-for="hotel in batch.imported_hotels" :key="hotel.hotel_id" class="mb-2">
                                            <div class="d-flex align-items-center">
                                                <font-awesome-icon
                                                    :icon="['fas', 'check-circle']"
                                                    class="text-success mr-2"
                                                    v-if="hotel.status === 'success'"/>
                                                <font-awesome-icon
                                                    :icon="['fas', 'times-circle']"
                                                    class="text-danger mr-2"
                                                    v-if="hotel.status === 'failed'"/>
                                                <span>{{ hotel.name }}</span>
                                            </div>
                                            <div v-if="hotel.status === 'failed' && hotel.error" class="ml-4 mt-1">
                                                <small class="text-danger">
                                                    <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="mr-1"/>
                                                    {{ hotel.error }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ batch.created_at }} | País: {{ batch.country || 'N/A' }}
                                    </small>
                                </div>
                                <button
                                    @click="closeBatch(batch.id)"
                                    class="btn btn-sm btn-outline-secondary ml-2"
                                    type="button"
                                    :disabled="closingBatches.includes(batch.id)">
                                    <font-awesome-icon
                                        v-if="!closingBatches.includes(batch.id)"
                                        :icon="['fas', 'times']"/>
                                    <font-awesome-icon
                                        v-else
                                        :icon="['fas', 'spinner']"
                                        class="fa-spin"/>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido de completados -->
                <div v-show="activeTab === 'completed'">
                    <div v-if="completedBatches.length === 0" class="alert alert-info">
                        No hay importaciones completadas.
                    </div>
                    <div v-else>
                        <div v-for="batch in completedBatches" :key="batch.id" class="alert alert-success mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">
                                        <font-awesome-icon :icon="['fas', 'check-circle']" class="mr-2"/>
                                        Importación Completada
                                    </h6>
                                    <p class="mb-1">{{ batch.message }}</p>
                                    <!-- Lista de hoteles importados -->
                                    <div v-if="batch.imported_hotels && batch.imported_hotels.length > 0" class="mt-2">
                                        <div v-for="hotel in batch.imported_hotels" :key="hotel.hotel_id" class="mb-2">
                                            <div class="d-flex align-items-center">
                                                <font-awesome-icon
                                                    :icon="['fas', 'check-circle']"
                                                    class="text-success mr-2"
                                                    v-if="hotel.status === 'success'"/>
                                                <font-awesome-icon
                                                    :icon="['fas', 'times-circle']"
                                                    class="text-danger mr-2"
                                                    v-if="hotel.status === 'failed'"/>
                                                <span>{{ hotel.name }}</span>
                                            </div>
                                            <div v-if="hotel.status === 'failed' && hotel.error" class="ml-4 mt-1">
                                                <small class="text-danger">
                                                    <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="mr-1"/>
                                                    {{ hotel.error }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ batch.created_at }} | País: {{ batch.country || 'N/A' }}
                                    </small>
                                </div>
                                <button
                                    @click="closeBatch(batch.id)"
                                    class="btn btn-sm btn-outline-secondary ml-2"
                                    type="button"
                                    :disabled="closingBatches.includes(batch.id)">
                                    <font-awesome-icon
                                        v-if="!closingBatches.includes(batch.id)"
                                        :icon="['fas', 'times']"/>
                                    <font-awesome-icon
                                        v-else
                                        :icon="['fas', 'spinner']"
                                        class="fa-spin"/>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido de fallidos -->
                <div v-show="activeTab === 'failed'">
                    <div v-if="failedBatches.length === 0" class="alert alert-info">
                        No hay importaciones fallidas.
                    </div>
                    <div v-else>
                        <div v-for="batch in failedBatches" :key="batch.id" class="alert alert-danger mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">
                                        <font-awesome-icon :icon="['fas', 'exclamation-circle']" class="mr-2"/>
                                        Importación Fallida
                                    </h6>
                                    <p class="mb-1">{{ batch.message }}</p>
                                    <!-- Lista de hoteles importados -->
                                    <div v-if="batch.imported_hotels && batch.imported_hotels.length > 0" class="mt-2">
                                        <div v-for="hotel in batch.imported_hotels" :key="hotel.hotel_id" class="mb-2">
                                            <div class="d-flex align-items-center">
                                                <font-awesome-icon
                                                    :icon="['fas', 'check-circle']"
                                                    class="text-success mr-2"
                                                    v-if="hotel.status === 'success'"/>
                                                <font-awesome-icon
                                                    :icon="['fas', 'times-circle']"
                                                    class="text-danger mr-2"
                                                    v-if="hotel.status === 'failed'"/>
                                                <span>{{ hotel.name }}</span>
                                            </div>
                                            <div v-if="hotel.status === 'failed' && hotel.error" class="ml-4 mt-1">
                                                <small class="text-danger">
                                                    <font-awesome-icon :icon="['fas', 'exclamation-triangle']" class="mr-1"/>
                                                    {{ hotel.error }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ batch.created_at }} | País: {{ batch.country || 'N/A' }}
                                    </small>
                                </div>
                                <button
                                    @click="closeBatch(batch.id)"
                                    class="btn btn-sm btn-outline-secondary ml-2"
                                    type="button"
                                    :disabled="closingBatches.includes(batch.id)">
                                    <font-awesome-icon
                                        v-if="!closingBatches.includes(batch.id)"
                                        :icon="['fas', 'times']"/>
                                    <font-awesome-icon
                                        v-else
                                        :icon="['fas', 'spinner']"
                                        class="fa-spin"/>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import { Switch as cSwitch } from '@coreui/vue'
    import { API } from '../../api'
    import BModal from 'bootstrap-vue/es/components/modal/modal'

    export default {
        components: {
            cSwitch,
            BModal,
        },
        data: () => {
            return {
                title: '',
                hotel: '',
                adminhotel: '',
                new_hotel: false,
                // Import batches alerts
                importBatches: [],
                batchPollingInterval: null,
                loadingBatches: false,
                activeTab: 'completed',
                refreshingBatches: false,
                closingBatches: [], // IDs de batches que se están cerrando
            }
        },
        created: function () {
            this.title = ''
            this.hotel = this.$i18n.t('hotels.title')
            this.adminhotel = this.$i18n.t('hotels.manage_hotel')
            this.$root.$on('updateTitleHotel', (payload) => {
                if (this.title === '') {
                    console.log(payload.hotel_id)
                    this.getNameHotel(payload.hotel_id)
                }
            })

            this.$root.$on('updateTitleUpdateList', () => {
                this.title = ''
            })

            // Cargar batches al iniciar
            this.loadImportBatches()
            // Polling cada 10 segundos
            this.startBatchPolling()

            // Escuchar evento para refrescar batches cuando se importa un hotel
            this.$root.$on('refreshImportBatches', () => {
                this.loadImportBatches()
            })
        },
        mounted: function () {
            this.$i18n.locale = localStorage.getItem('lang')
            this.hotel_id = (this.$route.params.hotel_id === undefined) ?  this.$route.params.id : this.$route.params.hotel_id
            this.getNameHotel(this.hotel_id)
        },
        computed: {
            showAdd () {
                return this.$route.name === 'HotelsList' && this.$can('create', 'hotels')
            },
            showEdit () {
                return this.$route.name !== 'HotelsList' && this.$route.name !== 'HotelsEdit' && this.$route.name !== 'HotelsAdd' && this.$can('update', 'hotels')
            },
            showManage () {
                return this.$route.name === 'HotelsEdit' && this.$can('update', 'hotels')
            },
            showImport () {
                return this.$route.name === 'HotelsList' && this.$can('read', 'hotelsimporthyperguest')
            },
            pendingBatches: function () {
                // Incluir pending y processing en la misma pestaña
                return this.importBatches.filter(b =>
                    (b.status === 'pending' || b.status === 'processing') && !b.viewed
                )
            },
            completedBatches: function () {
                // Solo completados sin fallos (failed_hotels === 0)
                return this.importBatches.filter(b =>
                    b.status === 'completed' &&
                    (!b.failed_hotels || b.failed_hotels === 0) &&
                    !b.viewed
                )
            },
            failedBatches: function () {
                // Fallidos: status = 'failed' O status = 'completed' con failed_hotels > 0
                return this.importBatches.filter(b => {
                    const isFailed = b.status === 'failed'
                    const hasFailedHotels = b.failed_hotels && b.failed_hotels > 0
                    return (isFailed || hasFailedHotels) && !b.viewed
                })
            },
            importBatchesCount: function () {
                return this.pendingBatches.length
            },
        },

        methods: {
            goEdit () {
                this.hotel_id = this.$route.params.hotel_id
                this.$router.push('/hotels/edit/' + this.hotel_id)
            },
            goAdmin () {
                this.hotel_id = this.$route.params.id
                this.$router.push('/hotels/' + this.hotel_id + '/manage_hotel')
                this.showTitle('')
            },
            getNameHotel: function (hotel_id) {
                console.log(hotel_id)
                if (hotel_id != undefined){
                    API.get('hotel/' + hotel_id + '/configurations')
                        .then((result) => {
                            this.title = '[' + result.data.data.channel[0].code + ']' + ' - ' + result.data.data.name
                            console.log(this.title)
                            window.localStorage.setItem('hotel_configuration', JSON.stringify(result.data.data))
                        }).catch(() => {
                        this.title = ''
                    })
                }
            },
            showImportModal () {
                // Emitir evento para que el componente hijo (List.vue) abra el modal
                this.$root.$emit('openImportHotelsModal')
            },
            // Métodos para batches de importación
            loadImportBatches() {
                API.get('hotels/import-batches')
                    .then((result) => {
                        if (result.data.success === true) {
                            // Incluir pending, processing, completed y failed no vistos
                            this.importBatches = (result.data.data || []).filter(b =>
                                (b.status === 'pending' || b.status === 'processing' || b.status === 'completed' || b.status === 'failed') && !b.viewed
                            )
                        }
                    })
                    .catch(() => {
                        // Silenciar errores, no es crítico
                    })
            },
            showBatchesModal() {
                this.loadingBatches = true
                this.activeTab = 'pending'
                if (this.$refs['my-modal-batches']) {
                    this.$refs['my-modal-batches'].show()
                }
                // Recargar batches al abrir el modal
                this.refreshBatches()
            },
            refreshBatches() {
                this.refreshingBatches = true
                API.get('hotels/import-batches')
                    .then((result) => {
                        if (result.data.success === true) {
                            // Incluir pending, processing, completed y failed no vistos
                            this.importBatches = (result.data.data || []).filter(b =>
                                (b.status === 'pending' || b.status === 'processing' || b.status === 'completed' || b.status === 'failed') && !b.viewed
                            )
                        }
                    })
                    .catch(() => {
                        // Silenciar errores
                    })
                    .finally(() => {
                        this.loadingBatches = false
                        this.refreshingBatches = false
                    })
            },
            closeBatch(batchId) {
                // Agregar a la lista de batches que se están cerrando
                if (this.closingBatches.includes(batchId)) {
                    return
                }
                this.closingBatches.push(batchId)

                API({
                    method: 'put',
                    url: `hotels/import-batch/${batchId}/viewed`
                })
                    .then((result) => {
                        if (result.data.success === true) {
                            // Remover el batch de la lista
                            this.importBatches = this.importBatches.filter(b => b.id !== batchId)
                            // Si no quedan batches, cerrar el modal
                            if (this.importBatches.length === 0 && this.$refs['my-modal-batches']) {
                                this.$refs['my-modal-batches'].hide()
                            }
                        }
                    })
                    .catch(() => {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('global.modules.hotels'),
                            text: 'Error al cerrar la notificación'
                        })
                    })
                    .finally(() => {
                        // Remover de la lista de batches que se están cerrando
                        const index = this.closingBatches.indexOf(batchId)
                        if (index > -1) {
                            this.closingBatches.splice(index, 1)
                        }
                    })
            },
            startBatchPolling() {
                // Polling cada 10 segundos para verificar nuevos batches completados/fallidos
                // this.batchPollingInterval = setInterval(() => {
                //     this.loadImportBatches()
                // }, 10000)
            },
        },
        beforeDestroy() {
            // Limpiar el intervalo cuando el componente se destruya
            if (this.batchPollingInterval) {
                clearInterval(this.batchPollingInterval)
            }
        },
    }
</script>

<style lang="stylus">
.card-header-actions .btn-import{
    background: #BD0D12;
    padding: 5px 10px;
    border-radius: 5px;
    color: #fff;
    transition: background-color 100ms linear;
    border: none;
}
.card-header-actions .btn-import:hover{
    background: #9B0B0F;
}

.card-header-actions .btn-info{
    background: #63c2de;
    padding: 5px 10px;
    border-radius: 5px;
    color: #fff;
    transition: background-color 100ms linear;
    border: none;
}
.card-header-actions .btn-info:hover{
    background: #52b8d4;
    color: #fff;
}
</style>


