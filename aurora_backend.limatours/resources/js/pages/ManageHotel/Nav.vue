<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item @click="tabsStatus(item.link)" active>
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
      return {
        items: [
          {
            id: 1,
            title: 'transaction_report',
            link: '/manage_hotels/transaction_reports',
            icon: 'dot-circle',
            status: '',
          },
          {
            id: 2,
            title: 'information',
            link: '/manage_hotels/informations',
            status: 'active',
          },
          {
            id: 3,
            title: 'configuration',
            link: '/manage_hotels/configurations',
            status: '',
          },
          // {
          //   id:4,
          //   title:'Inventory',
          //   link:'/manage_hotels/transaction_reports',
          //   status:'',
          // },
          // {
          //   id:5,
          //   title:'up_cross_selling',
          //   link:'/manage_hotels/transaction_reports',
          //   status:'',
          // },
          // {
          //   id:6,
          //   title:'configuration',
          //   link:'/manage_hotels/transaction_reports',
          //   status:'',
          // },
          // {
          //   id:7,
          //   title:'bedrooms',
          //   link:'/manage_hotels/transaction_reports',
          //   status:'',
          // },
        ],
      }
    },
    mounted: function () {
      console.log(this.$route.path)
      this.tabsStatus(this.$route.path)
    },
    methods: {
      tabsStatus: function (link) {
        for (var i = this.items.length - 1; i >= 0; i--) {
          if (
            (link === '/manage_hotels' && this.items[i].link === '/manage_hotels/transaction_reports')
            || (link == this.items[i].link)) {
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
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

</style>


