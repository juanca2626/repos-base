<template>
    <div class="container mt-4">
        <div class="row" v-if="newOrEditRatePlan">
            <b-button
                :key="channel.value"
                :variant="currentTab === channel.value ? 'primary' : 'outline-primary'"
                @click="changeTab(channel.value)"
                class="col-2 mr-2"
                v-for="channel in channels" v-if="channel_id == channel.value">
                {{channel.text}}
            </b-button>
        </div>
        <div class="row mt-4" v-if="currentTab === 1">
            <Aurora
                :newOrEditRatePlan="newOrEditRatePlan"
                :formAction="formAction"
                :hotelID="hotelID"
                :options="options"
                :ratePlanID="ratePlanID"
                :channelID="1"
            />
        </div>
        <div class="row mt-4" v-for="channel in channels" v-if="channel.value > 1 && currentTab === channel.value">
            <Channel
                :newOrEditRatePlan="newOrEditRatePlan"
                :channelID="channel.value"
                :formAction="formAction"
                :hotelID="hotelID"
                :options="options"
                :ratePlanID="ratePlanID"
            />
        </div>
    </div>
</template>

<script>
    import { API } from './../../../../../../../api'
    import BButton from 'bootstrap-vue/src/components/button/button'
    import Aurora from './Aurora'
    import Channel from './Channel'
    import { functionsIn } from 'lodash'

    export default {
        components: { BButton, Aurora, Channel },
        props: {
            newOrEditRatePlan: Boolean,
            hotelID: Number,
            ratePlanID: Number,
            formAction: String,
            options: Object
        },
        data: () => {
            return {
                channels: [],
                currentTab: 1,
                channel_id:null,
                rate_plan_id:null
            }
        },
        created(){
            this.rate_plan_id = this.$route.params.rate_id
            this.getChannelRatePlan()
        },
        mounted () {
            API.get('channels/selectHotelBox')
                .then((result) => {
                    this.channels = result.data.data
                })
        },
        methods: {
            changeTab (id) {
                this.currentTab = id
            },
            getChannelRatePlan:function (){
                API.get('channels/by/rate_plan/'+this.rate_plan_id)
                    .then((result) => {
                        this.channel_id = result.data.data
                        this.currentTab = this.channel_id
                    })

            }
        }
    }
</script>
