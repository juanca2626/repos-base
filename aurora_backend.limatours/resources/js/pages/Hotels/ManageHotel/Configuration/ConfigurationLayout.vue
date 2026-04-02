<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <b-tabs>
                    <b-tab :title="$t('hotelsmanagehotelconfiguration.tax')" @click="changeTabs('taxes')" active>
                      <template v-if="tabs === 'taxes'">
                        <taxes/>
                      </template>
                    </b-tab>
                    <b-tab :title="$t('hotelsmanagehotelconfiguration.minor_policy')"
                           @click="changeTabs('minorPolicy')">
                        <template v-if="tabs === 'minorPolicy'">
                          <minor-policy/>
                      </template>
                    </b-tab>
                    <b-tab :title="$t('hotelsmanagehotelconfiguration.cancellation_policy')"
                           @click="changeTabs('policyCancellation')">
                      <template v-if="tabs === 'policyCancellation'">
                          <cancellation-policy/>
                      </template>
                    </b-tab>
                    <b-tab :title="$t('hotelsmanagehotelconfiguration.policy_rate')" @click="changeTabs('policyRate')">
                        <template v-if="tabs === 'policyRate'">
                          <policy-rate/>
                        </template>
                    </b-tab>
                    <b-tab :title="$t('hotelsmanagehotelconfiguration.released')" @click="changeTabs('released')">
                        <template v-if="tabs === 'released'">
                            <released/>
                        </template>
                    </b-tab>
                </b-tabs>
            </div>
        </div>
    </div>
</template>

<script>
  import Taxes from './Taxes'
  import MinorPolicy from './MinorPolicy'
  import CancellationPolicy from './CancellationPolicy'
  import PolicyRate from './PolicyRate'
  import Released from './Released'

  export default {
    components: {
      'taxes': Taxes,
      'minor-policy': MinorPolicy,
      'cancellation-policy': CancellationPolicy,
      'policy-rate': PolicyRate,
      'released':Released
    },
    data: () => {
      return {
        tabs: 'taxes',
      }
    },
    mounted () {
    },
    computed: {},
    created () {

    },
    methods: {
      changeTabs: function (type) {
        switch (type) {
          case 'taxes':
            this.tabs = type
            break
          case 'minorPolicy':
            this.tabs = type
            break
          case 'policyCancellation':
            this.tabs = type
            break
          case 'policyRate':
            this.tabs = type
            break
          case 'released':
            this.tabs = type
            break
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


