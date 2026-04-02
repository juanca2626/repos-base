<template>
    <div>
        <template v-if="data">
            <b-table class="mb-3 table-outline" responsive="sm" small hover head-variant="light"
            :items="data"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
            <div slot="descri" slot-scope="data" style="padding:5px;">
                <div><span>{{ data.item.DESCRI }}</span></div>
                <div v-if="data.item.PROVIDERS.HTL != undefined && data.item.PROVIDERS.HTL.length > 0">
                    <template v-for="(provider, p) in data.item.PROVIDERS.HTL">
                        <span class="badge badge-danger mx-1" v-bind:key="'provider-' + p">
                            {{ provider }}
                        </span>
                    </template>
                </div>
            </div>
            <div slot="ciudad" slot-scope="data" style="padding:5px;">
                <div><span>{{ data.item.CIUDAD }}</span></div>
            </div>
            <div slot="nroref" slot-scope="data" style="padding:5px;">
                <div><span>{{ data.item.NROREF }}</span></div>
            </div>
            <div slot="area" slot-scope="data" style="padding:5px;">
                <div><span>{{ data.item.AREA }}</span></div>
            </div>
            <div slot="fecha" slot-scope="data" style="padding:5px;">
                <div><span>{{ data.item.FECHA }}</span></div>
            </div>
        </b-table>
        <b-pagination v-model="currentPage"
            :total-rows="rows"
            :per-page="perPage"
            aria-controls="my-table" align="center"></b-pagination>
        </template>
        <template v-else>
          <p>No hay información</p>
        </template>
    </div>
</template>

<script>
export default {
    props: ['data'],
    data() {
        return {
              //data2: [],
            perPage: 10,
            currentPage: 1,
            fields: {
                descri: {
                  label: 'Comentario'
                },
                ciudad: {
                  label: 'Ciudad',
                  class: 'text-center'
                },
                nroref: {
                  label: 'File',
                  class: 'text-center'
                },
                area: {
                  label: 'Area',
                  class: 'text-center'
                },
                fecha: {
                    label: 'Fecha',
                    class: 'text-center'
                }
            }
        }
    },
    mounted () {
    //this.data2 = this.data
    },
    computed: {
        rows() {
          return this.data.length
        }
    },
    methods: {},
    watch:{}
}
</script>
