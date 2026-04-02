<template>
    <div class="container-fluid" >
        <h1>Mi calendario</h1>
        
        <div class="calendar-parent" style="height:1000px">
			<calendar-view
				:events="events"
				:show-date="showDate"
				:time-format-options="{hour: 'numeric', minute:'2-digit'}"
				:enable-drag-drop="true"
				:disable-past="disablePast"
				:disable-future="disableFuture"
				:show-event-times="showEventTimes"
				:display-period-uom="displayPeriodUom"
				:display-period-count="displayPeriodCount"
				:starting-day-of-week="startingDayOfWeek"
				:class="themeClasses"
				:period-changed-callback="periodChanged"
				:current-period-label="useTodayIcons ? 'icons' : ''"			 
				@click-event="onClickEvent"
			>
				<calendar-view-header
					slot="header"
					slot-scope="{ headerProps }"
					:header-props="headerProps" 
                    @input="setShowDate"
				/>
			</calendar-view>
		</div>

        <b-modal :title="$t('supplement.detalle_title')" centered ref="my-modal" hide-footer >

            <p class="text-center"> Tarifa Suplemento {{ title_detalle }}</p>

			<table-client :columns="table_person.columns" :data="suplements_table" :options="tableOptions_person" id="dataTable"
							theme="bootstrap4" v-if="supplement_per_room === 1" >				 
				<div class="table-supplement" slot="supplement" slot-scope="props">
					{{ props.row.supplement.translations[0].value }}
				</div>
			</table-client>

        </b-modal> 

    </div>
</template>

<script>

import {API} from './../../../../../api'
import BModal from 'bootstrap-vue/es/components/modal/modal'
import TableClient from './.././../../../../components/TableClient'

import {
	CalendarView,
	CalendarViewHeader,
	CalendarMathMixin,
} from "vue-simple-calendar"


// Load CSS from the published version
require("vue-simple-calendar/static/css/default.css")
require("vue-simple-calendar/static/css/holidays-us.css")

// Load CSS from the local repo
//require("../../vue-simple-calendar/static/css/default.css")
//require("../../vue-simple-calendar/static/css/holidays-us.css")



export default {
	components: {
		'table-client': TableClient,
		CalendarView,
        CalendarViewHeader,
        BModal
	},
	mixins: [CalendarMathMixin],    
	data() {
		return {
			/* Show the current month, and give it some fake events to show */
			title_detalle : "",
			suplements_table : [],
			supplement_per_room : 0,
			showDate: this.thisMonth(1),
			message: "",
			startingDayOfWeek: 0,
			disablePast: false,
			disableFuture: false,
			displayPeriodUom: "month",
			displayPeriodCount: 1,
			showEventTimes: true,
			newEventTitle: "",
			newEventStartDate: "",
			newEventEndDate: "",
			useDefaultTheme: false,
			useHolidayTheme: false,
			useTodayIcons: false,
			events: [],
			table_person: {
			columns: ['min_age' , 'max_age' ,  'price_per_person']
			}
		}
	},
	computed: {
		userLocale() {
			return this.getDefaultBrowserLocale
		},
		dayNames() {
			return this.getFormattedWeekdayNames(this.userLocale, "long", 0)
		},
		themeClasses() {
			return {
				"theme-default": this.useDefaultTheme,
				"holiday-us-traditional": this.useHolidayTheme,
				"holiday-us-official": this.useHolidayTheme,
			}
		},
		tableOptions_person: function () {
			return {
				headings: {             
					min_age: this.$i18n.t('suplements.min_age'),
					max_age: this.$i18n.t('suplements.max_age'),
					price_per_person: this.$i18n.t('suplements.price_per_person') 
				},
				sortable: [],
				filterable: []
			}
		},			
	},
	mounted() {
		//this.newEventStartDate = this.isoYearMonthDay(this.today())
		//this.newEventEndDate = this.isoYearMonthDay(this.today())
	},

	methods: {
		formatDateToString(date){ 
		   var dd = (date.getDate() < 10 ? '0' : '') + date.getDate(); 
		   var MM = ((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1); 
		   var yyyy = date.getFullYear(); 
		   return (yyyy + '-' + MM + '-' + dd);
		},
        getCelendary(inicio,fin){
            let hotel_id = this.$route.params.hotel_id;
            API.get('suplements/hotel/calendaries/?lang=' + localStorage.getItem('lang') + '&hotel_id=' + hotel_id + '&inicio=' + inicio + '&fin=' + fin ).then((result) => {
                if (result.data.success === true) {        
                    
                    
                    this.events = result.data.data
                }
                }).catch(() => {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('suplements.error.messages.name'),
                    text: this.$t('suplements.error.messages.connection_error')
                })
            })
        },
		periodChanged(range, eventSource) {
            let inicio = this.formatDateToString(range.periodStart)
			let fin = this.formatDateToString(range.periodEnd)
			this.getCelendary(inicio,fin);			
		},
		thisMonth(d, h, m) {
			const t = new Date()
			return new Date(t.getFullYear(), t.getMonth(), d, h || 0, m || 0)
		}, 
		onClickEvent(e) {        
            
             
            let supplement_id = e.id.split('-')[1];
            let fecha = this.formatDateToString(e.startDate)
            let hotel_id = this.$route.params.hotel_id;
            API.get('suplements/hotel/calendaries_fecha/?lang=' + localStorage.getItem('lang') + '&hotel_id=' + hotel_id + '&fecha=' + fecha + '&supplement_id=' + supplement_id ).then((result) => {
                if (result.data.success === true) { 

					let resultData = result.data.data;
					
					if(resultData.room == "1"){
						this.supplement_per_room = 0;
						this.title_detalle = e.title + ' USD ' + resultData.price;
						this.suplements_table = [];
					}else{
						this.supplement_per_room = 1;
						this.title_detalle = e.title;
						this.suplements_table = resultData.price;						
					}	

					this.$refs['my-modal'].show();
                }
                }).catch(() => {
					this.$notify({
						group: 'main',
						type: 'error',
						title: this.$t('suplements.error.messages.name'),
						text: this.$t('suplements.error.messages.connection_error')
					})
            })                
        },
        setShowDate(d) {
			this.message = `Changing calendar view to ${d.toLocaleDateString()}`
			this.showDate = d
		},
			 
	},
}
</script>

<style scoped>

</style>
