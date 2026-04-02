<template>
    <div class="w-100">
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>

<script>

  export default {
    data: () => {
      return {}
    },
    computed: {
      items: function () {
        return [
          {
            id: 1,
            title: 'Costo',
            link: '/trains/' + this.$root.$route.params.train_id + '/manage_train/rates/cost',
            icon: 'dot-circle',
            status: ''
          },
          {
            id: 2,
            title: 'Venta',
            link: '/trains/' + this.$root.$route.params.train_id + '/manage_train/rates/sale',
            status: ''
          }
        ]
      }
    },
    created: function () {
      console.log(this.$route.name)
      if (this.$route.name === 'RatesRatesCostList') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'RatesRatesSale') {
        this.items[1].status = 'active'
      }
    },
    methods: {
      tabsStatus: function (link, id) {
        for (let i = this.items.length - 1; i >= 0; i--) {
          if (id === this.items[i].id) {
            console.log( this.items[i] )
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$router.push(link)
      }
    }
  }
</script>

<style lang="stylus">
    .s-color
        color red

    .fondo-nav
        background-color #f9fbfc
</style>
