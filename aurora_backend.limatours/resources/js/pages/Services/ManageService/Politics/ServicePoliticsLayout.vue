<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <b-tabs>
                    <b-tab v-if="!hasClient" :title="$t('servicesmanageservicepolitics.taxes')" @click="changeTabs('taxes')" active>
                        <template v-if="tabs === 'taxes'">
                            <taxes/>
                        </template>
                    </b-tab>
                    <b-tab :title="$t('servicesmanageservicepolitics.configuration')"
                           @click="changeTabs('configuration')">
                        <template v-if="tabs === 'configuration'">
                            <configuration/>
                        </template>
                    </b-tab>
                </b-tabs>
            </div>
        </div>
    </div>
</template>

<script>
    import ServiceTaxes from './ServiceTaxes'
    import ServiceConfiguration from './ServiceConfiguration'
    import ServiceCancellationPolicy from './ServiceCancellationPolicy'

    export default {
        components: {
            'taxes': ServiceTaxes,
            'configuration': ServiceConfiguration,
            'cancellation_policy': ServiceCancellationPolicy,
        },
        data: () => {
            return {
                tabs: 'taxes',
                hasClient : false
            }
        },
        mounted () {
        },
        computed: {},
        created () {
            this.hasClient = !!(window.localStorage.getItem('client_id') && window.localStorage.getItem('client_id') !== '')
            if(this.hasClient){
                this.tabs = 'configuration'
            }
        },
        methods: {
            changeTabs: function (type) {
                switch (type) {
                    case 'taxes':
                        this.tabs = type
                        break
                    case 'configuration':
                        this.tabs = type
                        break
                    // case 'cancellation_policy':
                    //   this.tabs = type
                    //   break
                }
            }
        }
    }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }

    .s-color {
        color: red;
    }
</style>


