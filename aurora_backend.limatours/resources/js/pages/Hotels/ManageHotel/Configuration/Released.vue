<template>
    <div class="container-fluid">
        <div class="row col-lg-12">
            <div class="row col-lg-1">
                <button class="btn btn-success" @click="storeRelease">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                </button>
            </div>
            <div class="row col-lg-11">
                <div class="form-inline col-lg-12">
                    <div class="form-group col-lg-5">
                        <label>Cantidad</label>
                        <input type="text"  class="form-control" v-model="quantity">
                    </div>
                    <div class="form-group col-lg-5">
                        <label >Habitacion</label>
                        <select class="form-control" v-model="room_id">
                            <option :value="room.id" v-for="room in rooms">
                                {{ room.translations[0].value }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row col-lg-12">
                <table class="table table-bordered">
                    <thead>
                    <th>Cantidad</th>
                    <th>Habitacion</th>
                    <th>
                        <font-awesome-icon :icon="['fas', 'list-alt']"></font-awesome-icon>
                    </th>
                    </thead>
                    <tbody>
                    <tr v-for="release in releases">
                        <td>{{ release.quantity }}</td>
                        <td>{{ release.room.translations[0].value }}</td>
                        <td>
                            <button class="btn btn-danger" @click="deleteRelease(release.id)">
                                <font-awesome-icon :icon="['fas', 'trash']"></font-awesome-icon>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from '../../../../api'

  export default {

    data: () => {
      return {
        rooms: [],
        releases: [],
        room_id: null,
        quantity: 0,
      }
    },
    mounted () {
      this.getRooms()
      this.getReleases()
    },
    computed: {},
    created () {

    },
    methods: {
      getRooms: function () {
        API.post('rooms/by/hotel/releases', {
          hotel_id: this.$route.params.hotel_id,
          lang: localStorage.getItem('lang')
        }).then((response) => {
          this.rooms = response.data.data
        })
      },
      getReleases: function () {
        API.get('releases?hotel_id=' + this.$route.params.hotel_id).then((response) => {
          this.releases = response.data
        })
      },
      storeRelease: function () {
        API.post('releases', {
          hotel_id: this.$route.params.hotel_id,
          room_id: this.room_id,
          quantity: this.quantity
        }).then((response) => {
          this.getRooms()
          this.getReleases()
        })
      },
      deleteRelease:function (release_id) {
        API.delete('releases/'+release_id,).then((response) => {
          this.getRooms()
          this.getReleases()
        })
      }
    }
  }
</script>

<style scoped>

</style>
