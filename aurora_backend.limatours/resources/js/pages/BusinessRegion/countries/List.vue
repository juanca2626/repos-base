<template>
    <div class="container-fluid">
        <table-client :columns="table.columns" :data="processedCountries" :options="tableOptions" id="dataTable" theme="bootstrap4">
            <div class="table-actions" slot="actions" slot-scope="props">
                <b-dropdown class="mt-2 ml-2 mb-0" dropleft size="sm">
                    <template slot="button-content">
                        <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                    </template>
                    <b-dropdown-item-button @click="showModal(props.row)"
                                            class="m-0 p-0"
                                            v-if="$can('delete', 'departments')">
                        <font-awesome-icon :icon="['fas', 'trash']" class="m-0"/>
                        {{ $t('global.buttons.delete') }}
                    </b-dropdown-item-button>
                </b-dropdown>
            </div>
        </table-client>

         <b-modal :title="message_title" centered ref="my-modal" size="sm">
            <!-- <p class="text-center">{{ $t('global.message_delete') }}</p> -->
            <div v-if="isLoading" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Cargando...</p>
            </div>
            <p v-else class="text-center" v-html="messageModal"></p>

            <div slot="modal-footer">
                <button @click="remove()" class="btn btn-success" :disabled="isLoading">{{ $t('global.buttons.accept') }}</button>
                <button @click="hideModal()" class="btn btn-danger" :disabled="isLoading">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>
    </div>
</template>
<script>
import {API} from '../../../api'
import TableClient from '../../../components/TableClient'
import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'
import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
import BModal from 'bootstrap-vue/es/components/modal/modal'

export default {
    components: {
        BFormCheckbox,
        'table-client': TableClient,
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
        BModal
    },
    data () {
      return {
        pais: '',
        isLoading: false,
        messageModal: '',
        message_title : '',
        country_id: '',
        region: {
          countries: []
        },
        region_id: '',
        table: {
            columns: ['iso', 'name', 'actions'],
        }
      }
    },
    computed: {
        tableOptions: function () {
            return {
                headings: {
                    iso: "ISO",
                    name: "País",
                    actions: this.$i18n.t('global.table.actions')
                },
                sortable: ['iso', 'name'],
                filterable: ['iso', 'name']
            }
        },
        processedCountries() {
            return this.region.countries.map(country => {
                const currentLang = localStorage.getItem('lang') || '1';
                const translation = country.translations.find(t => t.language_id == currentLang) ||
                                  country.translations[0];

                return {
                    id: country.id,
                    iso: country.iso,
                    name: translation ? translation.value : 'Sin nombre',
                    phone_code: country.phone_code,
                    rawData: country
                };
            });
        }
    },
    mounted() {
        this.$i18n.locale = localStorage.getItem('lang')
    },
    created() {
        this.getBusinessRegion()
    },
    methods: {
        getBusinessRegion() {
            this.region_id = this.$route.params.region_id;
            API.get('/business_region/' + this.$route.params.region_id +
                  '?token=' + window.localStorage.getItem('access_token') +
                  '&lang=' + localStorage.getItem('lang'))
            .then((result) => {
                if (result.data.success === true) {
                    this.region = result.data.data;
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: 'Fetch Error',
                        text: result.data.message
                    });
                }
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: 'Fetch Error',
                    text: 'Cannot load data'
                });
            });
        },
        async showModal(data) {
            this.message_title = data.name;
            this.country_id = data.id;
            this.$refs['my-modal'].show();
            console.log(data);
            await this.validateDestroy();
        },
        hideModal() {
            this.$refs['my-modal'].hide();
        },

        async validateDestroy(){
            this.isLoading = true;
            try{
                let response = await API({ method: 'POST' , url: 'business_region/'+ this.region_id +'/countries/' + this.country_id });
                this.messageModal = this.formattedMessage(response.data.message);
            } finally {
                this.isLoading = false;
            }
        },

        async remove() {
            const response = await API.delete(`business_region/${this.region_id}/countries/${this.country_id}`);
            if(response.data.success){
                this.$notify({
                    group: 'main',
                    type: 'success',
                    title: 'Éxito',
                    text: 'País eliminado correctamente'
                });
                this.$refs['my-modal'].hide();
                this.getBusinessRegion();
            }else{
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Error al eliminar",
                    text: "Ocurrió un error al eliminar aqui"
                });
            }
        },
        formattedMessage(message) {
            // Convierte saltos de línea en <br> y escapa el resto del HTML
            return message
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\n/g, "<br>");
        }
    }
}
</script>
