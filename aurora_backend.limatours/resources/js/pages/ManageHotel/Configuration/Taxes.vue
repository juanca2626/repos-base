<template>
    <div class="row">
        <div class="col-xs-12 col-lg-12 mt-2">
            <b-tabs>
                <b-tab :title="$t('tax')">
                    <table-client :columns="table.columns" :data="taxes" :loading="loading" :options="tableOptions"
                                  id="dataTable"
                                  theme="bootstrap4">
                        <template slot="value" slot-scope="props">
                            <div class="ml-5" style="width: 50%">
                                <b-form-input id="amount" type="number"
                                              v-model="props.row.value" v-on:blur="store(props.row)">
                                </b-form-input>
                            </div>
                        </template>
                        <template slot="status" slot-scope="props">
                            <b-form-checkbox :id="'checkbox-'+props.row.id"
                                             :name="'checkbox-'+props.row.id"
                                             @change="store(props.row)"
                                             unchecked-value="foul"
                                             v-model="props.row.status" value="ok">
                            </b-form-checkbox>
                        </template>
                        <div class="table-loading text-center" slot="loading" slot-scope="props">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>
                    <button @click="submit" class="btn btn-danger text-rigth" type="button">
                        <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                        {{$t('global.buttons.submit')}}
                    </button>
                </b-tab>
                <b-tab :title="$t('service')">
                    <table-client :columns="table.columns" :data="services" :loading="loading" :options="tableOptions"
                                  id="dataTable"
                                  theme="bootstrap4">
                        <template slot="value" slot-scope="props">
                            <div class="ml-5" style="width: 50%">
                                <b-form-input id="amount" type="number"
                                              v-model="props.row.value" v-on:blur="store(props.row)">
                                </b-form-input>
                            </div>
                        </template>
                        <template slot="status" slot-scope="props">
                            <b-form-checkbox :id="'checkbox-'+props.row.id"
                                             :name="'checkbox-'+props.row.id"
                                             @change="store(props.row)"
                                             unchecked-value="foul"
                                             v-model="props.row.status" value="ok">
                            </b-form-checkbox>
                        </template>
                        <div class="table-loading text-center" slot="loading" slot-scope="props">
                            <img alt="loading" height="51px" src="/images/loading.svg"/>
                        </div>
                    </table-client>
                    <div class="row">
                        <div class="col align-self-end">
                            <button @click="submit" class="btn btn-danger text-rigth" type="button">
                                <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                                {{$t('global.buttons.submit')}}
                            </button>
                        </div>
                    </div>
                </b-tab>
            </b-tabs>
        </div>
    </div>
    </div>
</template>

<script>
  import { API } from './../../../api'
  import ContactForm from './ContactForm'
  import UserForm from './UserForm'
  import TableClient from './.././../../components/TableClient'
  import MenuEdit from './../../../components/MenuEdit'
  import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
  import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      'user': UserForm,
      'contact': ContactForm,
      'b-dropdown': BDropDown,
      'b-dropdown-item-button': BDropDownItemButton
    },
    data: () => {
      return {
        loading: false,
        flag: false,
        addRoute: 'ContactAdd',
        hotel_id: 1,
        taxes: [],
        services: [],
        storeTaxes: [],
        users: [],
        table: {
          columns: ['id', 'name', 'value', 'status']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('name'),
            amount: this.$i18n.t('amount'),
            aplique: this.$i18n.t('aplique'),
          },
          sortable: ['id'],
          filterable: ['id']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {

      store: function (data) {
        let index = this.storeTaxes.findIndex(storeTaxes => storeTaxes.id == data.id)
        if (index == -1) {
          this.storeTaxes.unshift(data)
        } else {
          this.storeTaxes.splice(index, 1, data)
        }
      },
      changeStatus: function (data) {
        for (var i = data.length - 1; i >= 0; i--) {
          if (data[i].status == 'ok') {
            data[i].status = 1
          } else {
            data[i].status = 0
          }
          return data
        }
      },
      submit (form) {

        if (this.storeTaxes.length == 0) {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('alert_insert')
          })
        } else {

          let data = this.changeStatus(this.storeTaxes)
          API({
            method: 'put',
            url: 'hotel_taxes/' + (this.hotel_id),
            data: { storeTaxes: data }
          })
            .then((result) => {
              if (result.data.success === false) {

                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('module.taxes'),
                  text: this.$t('error.messages.contact_incorrect')
                })

                this.loading = false
              } else {
                this.$notify({
                  group: 'main',
                  type: 'success',
                  title: 'module.taxes',
                  text: this.$t('success_insert')
                })
              }
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('error.messages.name'),
              text: this.$t('error.messages.connection_error')
            })
          })
        }

      },
      close (valor) {
        this.flag = valor
      },
      change: function () {
        if (this.flag === true) {
          this.flag = false
        } else {
          this.flag = true
        }
      },
      statusB: function () {
        this.flag = false
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('hotel_taxes/?lang=' + lang + '&country_id=' + 1 + '&hotel_id=' + 1).then((result) => {
          this.loading = false
          let data = result.data.data
          if (result.data.success === true) {
            for (var i = data.length - 1; i >= 0; i--) {
              if (data[i].hotel_taxes.length > 0) {
                data[i].value = data[i].hotel_taxes[0].amount

                if (data[i].hotel_taxes[0].status == 1) {
                  data[i].status = 'ok'
                } else {
                  data[i].status = 'foul'
                }
              } else {
                data[i].status = false
              }
              if (data[i].type == 't') {
                this.taxes.push(data[i])
              } else {
                this.services.push(data[i])
              }
            }
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
      }
    }
  }
</script>

<style lang="stylus">
</style>


