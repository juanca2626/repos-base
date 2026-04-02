<template>
    <div>
        <div class="col-12" style="text-align: right;">
            <router-link
                v-if="showButtonAdd"
                :to="getRouteRoomsAdd()"
                class="btn btn-danger text-right"
                >
                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                {{ $t('global.buttons.add') }}
            </router-link>
            <a
                v-else-if="showButtonAdd"
                class="btn btn-danger text-right disabled"
                style="pointer-events: none; opacity: 0.6; color: white;"
                >
                <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                {{ $t('global.buttons.add') }}
            </a>
        </div>
        <router-view></router-view>
    </div>
</template>
<script>
    import { API } from '../../../../api'
  export default {
    data () {
      return {
        blocked_add_new: false
      }
    },
    computed: {
      showButtonAdd: function () {
        if (this.$route.name === 'RoomsList' && this.$can('create', 'rooms')) {
          return true
        } else {

          return false
        }
      }
    },
    async created() {
        await this.fetchHotelChannels();
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
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

                    // this.blocked_add_new = !!hyperguestChannel;
                }
            } catch (error) {
                console.error("Error fetching hotel channels:", error);
                this.blocked_add_new = false;
            }
        },
      getRouteRoomsAdd: function () {

        return '/hotels/' + this.$route.params.hotel_id + '/manage_hotel/rooms/add'

      },

    }
  }
</script>
<style lang="stylus">

</style>

