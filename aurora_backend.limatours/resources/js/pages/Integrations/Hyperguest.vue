<template>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <font-awesome-icon :icon="['fas', 'bars']" class="mr-1"/>
               Hyperguest Suscription
            </div>
            <div class="card-body">
                <div class="VueTables">
                <table class="VueTables__table table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="vueTable_column_actions"> Suscription ID</th>
                        <th  class="vueTable_column_actions">Bearer Token</th>
                        <th  class="vueTable_column_actions">Username</th>
                        <th  class="vueTable_column_actions">Email</th>
                        <th class="vueTable_column_actions">Status</th>
                        <th class="vueTable_column_actions">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ subscription_id }}</td>
                        <td>{{ token }}</td>
                        <td>{{ username }}</td>
                        <td>{{ email }}</td>
                        <td>{{ status }}</td>
                        <td>
                            <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
                                <template slot="button-content">
                                    <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
                                </template>
                                <b-dropdown-item-button @click="getSubscriptionDetails" class="m-0 p-0">
                                    get Subscription Details
                                </b-dropdown-item-button>
                                <b-dropdown-item-button @click="enableSubscription" class="m-0 p-0">
                                    Enable Subscription
                                </b-dropdown-item-button>
                                <b-dropdown-item-button @click="disableSubscription" class="m-0 p-0">
                                    Disable Subscription
                                </b-dropdown-item-button>
                                <b-dropdown-item-button @click="fullAryDataSync" class="m-0 p-0">
                                    Full Ary Data Sync
                                </b-dropdown-item-button>
                                <b-dropdown-item-button @click="updateSubscription" class="m-0 p-0">
                                    Update Subscription
                                </b-dropdown-item-button>
                            </b-dropdown>
                        </td>
                    </tr>
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer">
                <span  v-show="details_subscription!= null">
                  Details subscription:  {{ details_subscription }}
                </span>
                <br>
                <span v-show="response_api_hyperguest!= ''">
                   Response API Hyperguest: {{ response_api_hyperguest }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { API } from './../../api'
import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'

export default {
    props: [],
    components: {
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
    },
    data: () => {
        return {
            subscription_id: '',
            token:'',
            username:'',
            email:'',
            endpoint:'',
            status:'',
            details_subscription:null,
            response_api_hyperguest:''
        }
    },
    computed: {


    },
    mounted: function () {
        API({
            method: 'get',
            url: 'subscriptions/hyperguest/',
        })
            .then((result) => {
                    this.subscription_id = result.data.subscription_id
                    this.token = result.data.token
                    this.username = result.data.username
                    this.email = result.data.email
                    this.endpoint = result.data.endpoint
            })
    },
    methods: {
        getSubscriptionDetails() {
            API({
                method: 'get',
                url: 'subscriptions/status/hyperguest',
            })
                .then((response) => {
                    this.details_subscription = JSON.parse(response.data)
                    this.status = this.details_subscription.status
                    this.response_api_hyperguest = ''
                    console.log(this.details_subscription)
                })
        },
        enableSubscription () {
            API({
                method: 'get',
                url: 'subscriptions/enable/hyperguest',
            })
                .then((response) => {
                    this.details_subscription = null
                    this.response_api_hyperguest = response.data
                    console.log(JSON.parse(response.data))
                    this.fullAryDataSync()
                })
        },
        disableSubscription () {
            API({
                method: 'get',
                url: 'subscriptions/disable/hyperguest',
            })
                .then((response) => {
                    this.details_subscription = null
                    this.response_api_hyperguest = response.data
                    console.log(JSON.parse(response.data))
                })
        },
        fullAryDataSync() {
            API({
                method: 'get',
                url: 'subscriptions/full_ary_data_sync/hyperguest',
            })
                .then((response) => {
                    this.details_subscription = null
                    this.response_api_hyperguest = response.data
                    console.log(JSON.parse(response.data))
                })
        },
        updateSubscription(){
            API({
                method: 'get',
                url: 'subscriptions/update/hyperguest',
            })
                .then((response) => {
                    this.fullAryDataSync()
                    this.details_subscription = null
                    this.response_api_hyperguest = response.data
                    console.log(JSON.parse(response.data))
                })
        }
    }
}
</script>

<style lang="stylus">

</style>
