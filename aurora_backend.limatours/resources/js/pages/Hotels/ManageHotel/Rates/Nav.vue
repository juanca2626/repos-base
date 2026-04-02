<template>
    <div class="w-100">
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items">
                <template v-if="item.status=='active'">
                    <b-nav-item
                        @click="tabsStatus(item.link, item.id)"
                        active
                        v-if="item.show"
                        :class="{'disabled-tab': item.id === 2 && blocked_hyperguest}"
                        >
                        <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item
                        @click="tabsStatus(item.link, item.id)"
                        v-if="item.show"
                        :class="{'disabled-tab': item.id === 2 && blocked_hyperguest}"
                        >
                        <span>{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
            </div>
        </b-nav>
    </div>
</template>
<script>
  import { API } from '../../../../api'
  export default {
    data: () => {
      return {
        items: [
          {
            id: 1,
            title: 'Tarifas Negociadas / Diarias',
            link: '/manage_hotel/rates',
            icon: 'dot-circle',
            status: '',
              show:false
          },
          {
            id: 2,
            title: 'Bolsa de Tarifas',
            link: '/manage_hotel/rates/bags',
            status: '',
              show:false

          }
        ],
        blocked_hyperguest: false
      }
    },
    created: function () {

        this.items[0].show = this.showCost
        this.items[1].show = this.showBags

      if (this.$route.name === 'RatesRatesCostList') {
        this.items[0].status = 'active'
      }
      if (this.$route.name === 'BagsLayout' || this.$route.name === 'BagsList' || this.$route.name === 'BagsFormAdd' || this.$route.name === 'BagsFormEdit') {
        this.items[1].status = 'active'
      }
      this.fetchHotelChannels();
    },
  computed: {
      showCost() {
          return  this.$can('read', 'ratescosts')
      },
      showBags() {
          return  this.$can('read', 'bags')
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
            this.$router.push('/hotels/' + this.$route.params.hotel_id +link)

            this.items[0].show = this.showCost
            this.items[1].show = this.showBags
        },
        async fetchHotelChannels() {
            try {
                const hotel_id = this.$route.params.hotel_id;
                const response = await API.get(`hotels/${hotel_id}/channels?lang=${localStorage.getItem('lang')}`);
                if (response.data.data) {
                    this.channels = response.data.data;
                    const hyperguestChannel = this.channels.find(channel =>
                        channel.name === "HYPERGUEST" &&
                        channel.pivot.state === 1 &&
                        channel.pivot.type === "2"
                    );

                    // this.blocked_hyperguest = !!hyperguestChannel;
                }
            } catch (error) {
                console.error("Error fetching hotel channels:", error);
                this.blocked_hyperguest = false;
            }
        },
    }
  }
</script>

<style lang="stylus">
    .s-color
        color red

    .fondo-nav
        background-color #f9fbfc

    .disabled-tab{
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }

    .disabled-tab span {
        color: #6c757d !important;
    }

</style>
