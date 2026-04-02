<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <span class="s-color">{{$t('packagesquote.'+item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <span>{{$t('packagesquote.'+item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>
<script>
  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'cost',
            link: '/quotes/cost',
            status: ''
          },
          {
            id: 2,
            title: 'rates_table',
            link: '/quotes/sale',
            status: ''
          },
          {
            id: 3,
            title: 'blocking',
            link: '/quotes/blocking',
            status: ''
          }
        ]
      }
    },
    created: function () {
      if (this.$route.name === 'PackageCostQuotes' || this.$route.name === 'PackageCostQuoteAddHotel' || this.$route.name === 'PackageCostQuotesForm') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'PackageSaleQuotes') {
        this.items[1].status = 'active'
      }
      if (this.$route.name === 'PackageBlockingQuotes') {
        this.items[2].status = 'active'
      }
    },
    methods: {
      tabsStatus: function (link, id) {
        for (let i = this.items.length - 1; i >= 0; i--) {
          if (id === this.items[i].id) {
            this.items[i].status = 'active'
          } else {
            this.items[i].status = ''
          }
        }
        this.$router.push('/packages/' + this.$route.params.package_id + link)
      }
    }
  }
</script>
<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }
</style>


