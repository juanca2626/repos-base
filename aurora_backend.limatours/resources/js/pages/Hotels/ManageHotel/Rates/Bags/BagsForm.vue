<template>
    <div class="row">
        <div class="row col-12">
            <div class="row col-6">
                <div class="col-8 form-row">
                    <label class="col-3 col-form-label">Nombre</label>
                    <div class="col-9">
                        <input type="text" class="form-control" v-model="bag.name"/>
                    </div>
                    <p v-show="errorStoreBag">
                        Debe colocar un nombre de Bolsa
                    </p>
                </div>
                <div class="col-4 form-row">
                    <label class="col-6" for="status">{{ $t('status') }}</label>
                    <div class="col-6" id="status">
                        <b-form-checkbox switch v-model="bag.status" @change="updateBag">
                        </b-form-checkbox>
                    </div>
                </div>
            </div>
            <div class="col-2" v-if="formAction==='post'">
                <button @click="storeBag()" class="btn btn-success">{{ $t('global.buttons.submit') }}</button>
            </div>
            <div class="col-2" v-if="formAction==='post'">
                <router-link :to="getRouteBagsList()" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.back') }}
                    </button>
                </router-link>
            </div>
            <div class="col-4" v-if="formAction==='put'">
                <select @change="onChangeSelectRoom" class="form-control" id="room_id" name="room_id" v-model="room_id">
                    <option value=""></option>
                    <option :value="room.id" v-for="room in rooms">{{ room.translations[0].value }}</option>
                </select>
            </div>
        </div>
        <div class="row col-12"  v-show="!loading && formAction==='put'">
            <div class="col-5">
                <div class="input-group">
                 <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                 </span>
                    <input class="form-control" id="search_rates" type="search" v-model="query">
                </div>
                <ul class="style_list_ul_rates" id="list_rates">
                    <li :class="{'style_list_li_rates':true, 'item':true, 'selected':rate.selected}" :id="'rate_'+index"
                        :key="rate.id"
                        @click="selectRate(rate,index)" v-for="(rate,index) in rates">
                        <span class="style_span_li_rates" :style="rate.rate_plan_status == '0' ? 'color:red': '' ">{{ rate.rate_plan_id + "-" + rate.name}}</span>
                    </li>
                </ul>
            </div>
            <div class="col-2">
                <div class="col-12">
                    <button @click="moveOneRate()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-right']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="moveAllRates()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-double-right']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="inverseOneRate()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-left']"/>
                    </button>
                </div>
                <div class="col-12">
                    <button @click="inverseAllRates()" class="btn btn-secondary mover_controls btn-block">
                        <font-awesome-icon :icon="['fas', 'angle-double-left']"/>
                    </button>
                </div>
            </div>
            <div class="col-5">
                <div class="input-group">
                <span class="input-group-append">
                    <button class="btn btn-outline-secondary button_icon" type="button">
                        <font-awesome-icon :icon="['fas', 'search']"/>
                    </button>
                </span>
                    <input class="form-control" id="search_rates_selected" type="search" v-model="query_rates_selected"
                           value="">
                </div>
                <ul class="style_list_ul_rates" id="list_rates_selected">
                    <li :class="{'style_list_li_rates':true, 'item':true, 'selected':rate.selected}"
                        @click="selectRateRatesSelected(rate,index)" v-for="(rate,index) in rates_selected">
                        <span class="style_span_li_rates" :style="rate.rate_plan_status == '0' ? 'color:red': '' ">{{ rate.rate_plan_id + "-" + rate.name}}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="table-loading text-center" v-show="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
        <div class="row col-12">
            <router-link :to="getRouteBagsList()" v-if="!loading && formAction==='put'">
                <button class="btn btn-danger" type="reset">
                    {{ $t('global.buttons.back') }}
                </button>
            </router-link>
        </div>
    </div>
</template>
<script>
  import { API } from './../../../../../api'
  import { Switch as cSwitch } from '@coreui/vue'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      cSwitch,
      BFormCheckbox
    },
    data () {
      return {
        scroll_limit: 2900,
        rates: [],
        room_id:'',
        rooms:[],
        page: 1,
        limit: 100,
        formAction:'post',
        bag:{
          id:null,
          name:'',
          status:false,
        },
        check_bag:false,
        errorStoreBag:false,
        count: 0,
        num_pages: 1,
        query: '',
        interval: null,
        rates_selected: [],
        page_rates_selected: 1,
        limit_rates_selected: 100,
        count_rates_selected: 0,
        num_pages_rates_selected: 1,
        query_rates_selected: '',
        scroll_limit_rates_selected: 2900,
        interval_rates_selected: null,
        loading: false
      }
    },
    computed: {},
    mounted: function () {
      if (this.$route.params.bag_id !==undefined)
      {
        this.formAction = "put"
        this.getBag()
        this.getRoomsByHotel()
      }
      let search_rates = document.getElementById('search_rates')
      let timeout_rates
      search_rates.addEventListener('keydown', () => {
        clearTimeout(timeout_rates)
        timeout_rates = setTimeout(() => {
          this.getRates()
          clearTimeout(timeout_rates)
        }, 1000)
      })

      let search_rates_selected = document.getElementById('search_rates_selected')
      let timeout_rates_selected
      search_rates_selected.addEventListener('keydown', () => {
        clearTimeout(timeout_rates_selected)
        timeout_rates_selected = setTimeout(() => {
          this.getRatesSelected()
          clearTimeout(timeout_rates_selected)
        }, 1000)
      })

      this.interval = setInterval(this.getScrollTop, 3000)
      this.interval_rates_selected = setInterval(this.getScrollTopRatesSelected, 3000)
    },
    methods: {
      onChangeSelectRoom:function(){
        this.getRates()
        this.getRatesSelected()
      },
      getRoomsByHotel:function(){
        API({
          method: 'post',
          url: 'rooms/by/hotel',
          data: {
            hotel_id: this.$route.params.hotel_id,
            lang: localStorage.getItem('lang'),
          }
        })
          .then((result) => {
            if (result.data.success === true) {
              this.rooms = result.data.data
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('error.messages.information_error')
              })
            }
          }).catch(() => {
          this.showTable = false
          this.showMessage = true
        })
      },
      getRouteBagsList: function () {

        return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rates/bags'

      },
      selectRate: function (rate, index) {
        if (this.rates[index].selected) {
          this.$set(this.rates[index], 'selected', false)
        } else {
          this.setPropertySelectedInRates()
          this.$set(this.rates[index], 'selected', true)
        }
      },
      selectRateRatesSelected: function (rate, index) {
        if (this.rates_selected[index].selected) {
          this.$set(this.rates_selected[index], 'selected', false)
        } else {
          this.setPropertySelectedInRatesSelected()
          this.$set(this.rates_selected[index], 'selected', true)
        }
      },
      searchSelectRate: function () {
        for (let i = 0; i < this.rates.length; i++) {
          if (this.rates[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      searchSelectRateRatesSelected: function () {
        for (let i = 0; i < this.rates_selected.length; i++) {
          if (this.rates_selected[i].selected) {
            return i
            break
          }
        }
        return -1
      },
      setPropertySelectedInRates: function () {
        for (let i = 0; i < this.rates.length; i++) {
          this.$set(this.rates[i], 'selected', false)
        }
      },
      setPropertySelectedInRatesSelected: function () {
        for (let i = 0; i < this.rates_selected.length; i++) {
          this.$set(this.rates_selected[i], 'selected', false)
        }
      },
      getBag:function(){
        API({
          method: 'get',
          url: 'rates/bags/'+this.$route.params.bag_id,
        })
          .then((result) => {
            if (result.data.success === true) {
              let bag = result.data.bag
              this.bag.id = bag.id
              this.bag.name = bag.name
              this.bag.status = bag.status ? true: false
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      },
      getRates: function () {
        API({
          method: 'post',
          url: 'rates/search',
          data: {
            page: 1,
            limit: this.limit,
            query: this.query,
            hotel_id: this.$route.params.hotel_id,
            room_id:this.room_id
          }
        })
          .then((result) => {
            this.rates = result.data.data
            this.count = result.data.count
            this.calculateNumPages(result.data.count, this.limit)
            this.scroll_limit = 2900
            document.getElementById('list_rates').scrollTop = 0

          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      },
      getRatesSelected: function () {
        API({
          method: 'post',
          url: 'rates/bag_rates',
          data: {
            page: 1,
            limit: this.limit_rates_selected,
            query: this.query_rates_selected,
            bag_id: this.$route.params.bag_id,
            room_id: this.room_id
          }
        })
          .then((result) => {
            this.rates_selected = result.data.data
            this.count_rates_selected = result.data.count
            this.calculateNumPagesRatesSelected(result.data.count, this.limit_rates_selected)
            this.scroll_limit_rates_selected = 2900
            document.getElementById('list_rates_selected').scrollTop = 0

          }).catch((e) => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error') + e
          })
        })

      },
      storeBag:function(){
        if (this.bag.name !=="")
        {
          API({
            method: 'post',
            url: 'rates/bags/store',
            data: { bag: this.bag, hotel_id: this.$route.params.hotel_id }
          })
            .then((result) => {
              if (result.data.success === true) {
                this.$router.push('/hotels/' + this.$route.params.hotel_id +'/manage_hotel/rates/bags/edit/'+result.data.bag_id)
                this.$root.$emit('updateBag', { id: this.bag.id })
              }
            }).catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('error.messages.name'),
              text: this.$t('error.messages.connection_error')
            })
          })
        }else{
          this.errorStoreBag = true
          return false
        }
      },
      updateBag:function(){
        if (this.bag.name !=="")
        {
          API({
            method: 'put',
            url: 'rates/bags/update',
            data: this.bag
          })
            .then((result) => {
              if (result.data.success === true) {
                console.log("bag actualizado")
              }
            }).catch((e) => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: this.$t('error.messages.name'),
              text: this.$t('error.messages.connection_error')
            })
          })
        }else{
          this.errorStoreBag = true
          return false
        }
      },
      moveOneRate: function () {
        if (this.loading === false) {
          this.loading = true
          let search_rate = this.searchSelectRate()
          if (search_rate !== -1) {
            this.updateBag()
            API({
              method: 'post',
              url: 'rates/bags/rate/store',
              data: {bag_id:this.bag.id ,rate: this.rates[search_rate],room_id:this.room_id}
            }).then((result) => {
              if (result.data.success === true)
              {
                this.getRates()
                this.getRatesSelected()
                this.loading = false
              }
            }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error')
              })
            })
          } else {
            if (this.rates.length > 0)
            {
              this.updateBag()
              let element = this.rates.shift()
              API({
                method: 'post',
                url: 'rates/bags/rate/store',
                data: {bag_id:this.bag.id ,rate: element ,room_id:this.room_id}
              }).then((result) => {
                if (result.data.success === true)
                {
                  if (this.formAction === "post"){

                    this.$router.push('/hotels/' + this.$route.params.hotel_id +'/manage_hotel/rates/bags/edit/'+this.bag.id)
                    this.$root.$emit('updateBag', { id: this.bag.id })
                  }else{
                    this.getRates()
                    this.getRatesSelected()
                    this.loading = false
                  }
                }
              }).catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('error.messages.name'),
                  text: this.$t('error.messages.connection_error')
                })
              })
            }else{
              this.loading = false
            }
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      inverseOneRate: function () {
        if (this.loading === false) {
          this.loading = true
          this.updateBag()
          let search_rate = this.searchSelectRateRatesSelected()
          if (search_rate !== -1) {
            API({
              method: 'post',
              url: 'rates/bags/rate/inverse',
              data: {rate: this.rates_selected[search_rate] }
            }).then((result) => {
              if (result.data.success === true)
              {
                this.getRates()
                this.getRatesSelected()
                this.loading = false
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
          else {
            if (this.rates_selected.length > 0) {
              let element = this.rates_selected.shift()
              API({
                method: 'post',
                url: 'rates/bags/rate/inverse',
                data: {rate: element }
              }).then((result) => {
                if (result.data.success === true)
                {
                  this.getRates()
                  this.getRatesSelected()
                  this.loading = false
                }
              }).catch(() => {
                this.$notify({
                  group: 'main',
                  type: 'error',
                  title: this.$t('error.messages.name'),
                  text: this.$t('error.messages.connection_error')
                })
              })
            }else{
              this.loading = false
            }
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      moveAllRates: function () {
        if (this.loading === false) {
          this.loading = true
          this.updateBag()
          if (this.rates.length > 0) {
            API({
              method: 'post',
              url: 'rates/bags/rate/store/all',
              data: {
                bag_id: this.bag.id,
                hotel_id: this.$route.params.hotel_id,
                room_id: this.room_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.getRates()
                  this.getRatesSelected()
                  this.loading = false
                }
              }).catch((e) => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error') + e
              })
            })
          } else{
            this.loading = false
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      inverseAllRates: function () {
        if (this.loading === false) {
          this.loading = true
          this.updateBag()
          if (this.rates_selected.length > 0) {
            API({
              method: 'post',
              url: 'rates/bags/rate/inverse/all',
              data: {
                bag_id: this.bag.id,
                room_id: this.room_id
              }
            })
              .then((result) => {
                if (result.data.success === true) {
                  this.getRates()
                  this.getRatesSelected()
                  this.loading = false
                }
              }).catch(() => {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('error.messages.name'),
                text: this.$t('error.messages.connection_error')
              })
            })
          }else{
            this.loading = false
          }
        } else {
          console.log('Bloqueado accion')
        }
      },
      calculateNumPages: function (num_rates, limit) {
        this.num_pages = Math.ceil(num_rates / limit)
      },
      calculateNumPagesRatesSelected: function (num_rates, limit) {
        this.num_pages_rates_selected = Math.ceil(num_rates / limit)
      },
      getScrollTop: function () {
        let scroll = document.getElementById('list_rates').scrollTop
        if (scroll > this.scroll_limit) {
          this.page += 1
          this.scroll_limit = 2900 * this.page
          if (this.page === this.num_pages) {
            clearInterval(this.interval)
            this.getRatesScroll()
          } else {

            this.getRatesScroll()
          }

        }
      },
      getScrollTopRatesSelected: function () {
        let scroll = document.getElementById('list_rates_selected').scrollTop
        if (scroll > this.scroll_limit_rates_selected) {
          this.page_rates_selected += 1
          this.scroll_limit_rates_selected = 2900 * this.page_rates_selected
          if (this.page_rates_selected === this.num_pages_rates_selected) {
            clearInterval(this.interval_rates_selected)
            this.getRatesScrollSelected()
          } else {

            this.getRatesScrollSelected()
          }

        }
      },
      getRatesScroll: function () {
        API({
          method: 'post',
          url: 'rates/search',
          data: {
            page: 1,
            limit: this.limit,
            query: this.query
          }
        })
          .then((result) => {
            let rates = result.data.data
            for (let i = 0; i < rates.length; i++) {
              this.rates.push(rates[i])
            }
            if (this.page === 1) {
              this.count = result.data.count
              this.calculateNumPages(result.data.count, this.limit)
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
        API({
          method: 'post',
          url: 'rate/search/up_selling',
          data: {
            page: this.page,
            limit: this.limit,
            query: this.query,
            rate_id: this.$route.params.rate_id
          }
        })
          .then((result) => {
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })

      },
      getRatesScrollSelected: function () {
        API({
          method: 'post',
          url: 'rates/bag_rates',
          data: {
            page: 1,
            limit: this.limit_rates_selected,
            query: this.query_rates_selected,
            bag_id: this.$route.params.bag_id
          }
        })
          .then((result) => {
            let rates_selected = result.data.data
            for (let i = 0; i < rates_selected.length; i++) {
              this.rates_selected.push(rates_selected[i])
            }
            if (this.page_rates_selected === 1) {
              this.count_rates_selected = result.data.count
              this.calculateNumPagesRatesSelected(result.data.count, this.limit_rates_selected)
            }
          }).catch(() => {
          this.$notify({
            group: 'main',
            type: 'error',
            title: this.$t('error.messages.name'),
            text: this.$t('error.messages.connection_error')
          })
        })
      },
    },
    beforeDestroy () {

      clearInterval(this.interval)
      clearInterval(this.interval_rates_selected)


    }
  }
</script>

<style>
    body {
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .style_list_ul_rates {
        height: 160px;
        max-height: 160px;
        overflow-y: scroll;
        list-style-type: none;
        padding: 0px;
        margin-left: -1px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }

    .selected {
        background-color: #005ba5;
        color: white;
    }

    .style_list_li_rates {
        border-bottom: 1px solid #ccc;
        padding: 5px 5px 5px 5px;
    }

    .style_span_li_rates {
        margin-left: 5px;
    }

    #search_rates:focus {
        box-shadow: none;
        border-color: #ccc;
    }
    #search_rates_selected:focus {
        box-shadow: none;
        border-color: #ccc;
    }
    #search_rates_selected {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    #search_rates {
        border-top: 1px solid #ccc;
        border-right: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        border-bottom-right-radius: 0px;
        border-top-right-radius: 0.2rem;
    }

    .button_icon {
        background-color: #f0f3f5 !important;
        border-top-left-radius: 0.2rem;
        color: #000;
        cursor: default !important;
    }

    .button_icon:hover {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:focus {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .button_icon:active {
        box-shadow: none;
        background-color: #f0f3f5 !important;
    }

    .mover_controls {
        padding: 10px;
        margin-bottom: 10px;
    }
</style>
