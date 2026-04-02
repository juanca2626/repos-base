<template>
    <div class="card">
        <div :class="'card-header ' + classCard">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            <span v-html="title"></span>

            <div class="card-header-actions" v-if="showManage">
                <button type="button" @click="backEditTrain" class="btn btn-primary left">
                    <font-awesome-icon :icon="['fas', 'edit']" />
                    {{ $t('global.table.edit') }} Tren
                </button>
            </div>

            <div class="card-header-actions">
                <router-link :to="{ name: 'TrainsAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon" />
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    data () {
      return {
        title: '',
        classCard: '',
        train: '',
        admintrain: ''
      }
    },
    created: function () {
      this.train = "Administrador de Trenes"
      this.admintrain = this.$i18n.t('trains.manage_train')
      this.edittrain = 'Editar Paquete'

      this.$root.$on('updateTitleTrain', (payload) => {
          if( payload.title ){
              this.title = payload.title
          } else {
              this.showTitle()
          }
      })
    },
    computed: {
      showManage () {
        return this.$route.name === 'TrainTextsForm' || this.$route.name === 'TrainConfigurationsLayout' ||
                this.$route.name === 'TrainGalleryManageList' || this.$route.name === 'FixedOutputsList'
      },
      showAdd () {
        return this.$route.name === 'TrainsList' && this.$can('create', 'trains')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      backEditTrain(){
        this.$router.push('/trains/edit/'+this.$route.params.train_id)
      },
      showTitle: function () {
        if (this.$route.path.indexOf('manage_train') !== -1  ){
          this.title = this.admintrain + ' : ' + localStorage.getItem('trainnamemanage')
        } else if(this.$route.path.indexOf('edit') !== -1) {
          this.title = this.edittrain + ' : ' + localStorage.getItem('trainnamemanage')
        }
        else {
          this.title = this.train
          this.classCard = ''
        }

      }
    }
  }
</script>

<style lang="stylus">

</style>


