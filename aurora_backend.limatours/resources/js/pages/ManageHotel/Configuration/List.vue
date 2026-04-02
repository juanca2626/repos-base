<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <b-tabs>
                    <!-- @click="statusB('ContactAdd')" -->
                    <b-tab :title="$t('tax')" active>
                        <taxes/>
                    </b-tab>
                    <b-tab :title="$t('minor_policy')">
                        <minor-policy/>
                    </b-tab>
                </b-tabs>
            </div>
        </div>
    </div>
</template>

<script>
  import Taxes from './Taxes'
  import MinorPolicy from './MinorPolicy'

  export default {
    components: {
      'taxes': Taxes,
      'minor-policy': MinorPolicy
    },
    data: () => {
      return {}
    },
    mounted () {
    },
    computed: {},
    created () {

    },
    methods: {}
  }
</script>

<style lang="stylus">
    .marl {
        margin-left: 800px;
    }
</style>


