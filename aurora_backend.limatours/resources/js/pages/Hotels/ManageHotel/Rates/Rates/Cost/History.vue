<template>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>{{$t('hotelsmanagehotelratesratescost.rate')}}: {{name}}
                    {{$t('hotelsmanagehotelratesratescost.history')}}</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-2">{{$t('hotelsmanagehotelratesratescost.date_range')}}</div>
            <div class="col-5">
                <v-select
                        :options="history"
                        :resetOnOptionsChange="true"
                        :selectOnTab="true"
                        autocomplete="true"
                        id="historyFrom"
                        label="label"
                        ref="historyFromTypeahead"
                        v-model="historyFrom">
                </v-select>
            </div>
            <div class="col-5">
                <v-select
                        :options="history"
                        :resetOnOptionsChange="true"
                        :selectOnTab="true"
                        autocomplete="true"
                        id="historyTo"
                        label="label"
                        ref="historyToTypeahead"
                        v-model="historyTo">
                </v-select>
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.name !==  historyTo.data.name}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>{{ $t('global.name') }}</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.name}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.name}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.translations[1]['commercial_name'] !==  historyTo.data.translations[1]['commercial_name']}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>{{ $t('hotelsmanagehotelratesratescost.commercial_name') }}</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.translations[1]['commercial_name']}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.translations[1]['commercial_name']}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.code !==  historyTo.data.code}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>{{ $t('hotelsmanagehotelratesratescost.code') }}</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.code}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.code}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.meal_name !==  historyTo.data.meal_name}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>{{ $t('hotelsmanagehotelratesratescost.meal_name') }}</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.meal_name}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.meal_name}}
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <hr/>
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.type_name !==  historyTo.data.type_name}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Tipo de Tarifa</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.type_name}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.type_name}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.allotment !==  historyTo.data.allotment}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Allotment</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.allotment}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.allotment}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.taxes !==  historyTo.data.taxes}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Impuestos</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.taxes}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.taxes}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.services !==  historyTo.data.services}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Services</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.services}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.services}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.timeshares !==  historyTo.data.timeshares}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Multipropiedad</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.timeshares}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.timeshares}}
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true, 'is-different':historyFrom.data.promotions !==  historyTo.data.promotions}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-2 py-2">
                <strong>Promoción</strong>
            </div>
            <div :class="{'col-5': true, 'py-2':true, 'from-block': true}">
                {{historyFrom.data.promotions}}
            </div>
            <div class="col-5 py-2">
                {{historyTo.data.promotions}}
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <hr/>
            </div>
        </div>
        <div :class="{'row': true, 'py-0': true}"
             v-if="historyFrom && historyTo"
        >
            <div class="col-6 from-block pr-4 py-2">
                <div class="row" v-for="(room, index) in historyFrom.dataRooms">
                    <div :class="{'col-4':true, 'text-align':right, 'is-different': (historyFrom.dataRooms[index] !== undefined) && historyFrom.dataRooms[index].room_name !== historyTo.dataRooms[index].room_name}">
                        {{room.room_name}}
                    </div>
                    <div :class="{'col-2':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].policy_name !== historyTo.dataRooms[index].policy_name}">
                        {{room.policy_name}}
                    </div>
                    <div :class="{'col-2':true, 'p-0':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].from !== historyTo.dataRooms[index].from}">
                        {{room.from}} {{room.to}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].adult !== historyTo.dataRooms[index].adult}">
                        {{room.adult}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].child !== historyTo.dataRooms[index].child}">
                        {{room.child}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].infant !== historyTo.dataRooms[index].infant}">
                        {{room.infant}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].extra !== historyTo.dataRooms[index].extra}">
                        {{room.extra}}
                    </div>
                </div>
            </div>
            <div class="col-6 py-2">
                <div class="row" v-for="(room, index) in historyTo.dataRooms">
                    <div :class="{'col-4':true, 'text-align':right,'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].room_name !== historyTo.dataRooms[index].room_name}">
                        {{room.room_name}}
                    </div>
                    <div :class="{'col-2':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].policy_name !== historyTo.dataRooms[index].policy_name}">
                        {{room.policy_name}}
                    </div>
                    <div :class="{'col-2':true, 'p-0':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].from !== historyTo.dataRooms[index].from}">
                        {{room.from}} {{room.to}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].adult !== historyTo.dataRooms[index].adult}">
                        {{room.adult}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].child !== historyTo.dataRooms[index].child}">
                        {{room.child}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].infant !== historyTo.dataRooms[index].infant}">
                        {{room.infant}}
                    </div>
                    <div :class="{'col-1':true, 'p-0':true, 'text-right':true, 'is-different': (historyFrom.dataRooms[index] !== undefined) &&  historyFrom.dataRooms[index].extra !== historyTo.dataRooms[index].extra}">
                        {{room.extra}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../../../api'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      vSelect
    },
    data: () => {
      return {
        name: '',
        rate: {},
        history: [],
        historyFrom: null,
        historyTo: null
      }
    },
    created () {
      document.body.classList.add('brand-minimized')
      document.body.classList.add('sidebar-minimized')
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    methods: {
      fetchData () {
        API
          .get('rates/cost/' +
            this.$route.params.hotel_id + '/'
            + this.$route.params.rate_id + '/'
            + 'history?lang=' + localStorage.getItem('lang'))
          .then((result) => {
            this.history = result.data.data
            this.name = result.data.name
          })
      }
    }
  }
</script>

<style lang="stylus">
    .from-block
        background #F9FBFC

    .is-different
        background rgba(255, 0, 0, 0.5) !important

        .from-block
            background rgba(255, 0, 0, 0) !important
</style>

