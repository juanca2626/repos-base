<template>
    <div class="row">

        <div class="col-sm-12" style="margin-bottom: 20px;">
            <h2>{{role.name}}</h2>
        </div>

        <div class="col-sm-12" style="margin-bottom: 20px; text-align: right;">

            <label class="col-sm-2 col-form-label" for="name">{{$t('roles.active-all')}}</label>
            <c-switch
                      v-model="form.all"   class="mx-1" color="primary"
                      variant="pill"></c-switch>

                <button @click="changeState" class="btn btn-success" >
                    {{$t('roles.apply')}}
                </button>
        </div>

        <div class="col-sm-12">
            <div class="card-columns">
                <div class="card" v-for="permission in permissions">
                    <div class="card-header">
                        {{permission.description}}<br>
                        <small class="text-danger">{{permission.name}}</small>
                    </div>
                    <div class="card-body">
                        <div class="row" v-for="permissionData in permission.data">
                            <div class="col-8">{{permissionData.name}}</div>
                            <div class="col-4 text-right">
                                <c-switch :value="permissions.id" class="mx-1" color="primary"
                                          v-model="form.permissions[permissionData.id]"
                                          variant="pill"></c-switch>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.save')}}
                </button>
                <router-link :to="{ name: 'RolesList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{$t('global.buttons.cancel')}}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import { Switch as cSwitch } from '@coreui/vue'

  export default {
    components: {
      cSwitch
    },
    data: () => {
      return {
        loading: false,
        permissions: [],
        role: {
          name: ''
        },
        form: {
          all : true,
          permissions: []
        }
      }
    },
    mounted () {
      this.fetchData()
    },
    methods: {
      changeState:function(){

          let k;
          for (k in this.form.permissions) {
              this.form.permissions[k] = this.form.all;
          }
      },
      fetchData: function () {
        API.get('permissions/treeView').then((result) => {
          if (result.data.success === true) {
            this.permissions = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
        API.get('roles/' + this.$route.params.id).then((result) => {
          if (result.data.success === true) {
            this.role = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
        API.get('permissions/fromRole/' + this.$route.params.id).then((result) => {
          if (result.data.success === true) {
            let permissionsRemote = result.data.data
            let permissionsLocal = this.form.permissions

            this.form.permissions = { ...permissionsLocal, ...permissionsRemote }
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
      },
      submit: function () {
        this.loading = true

        API.post('roles/' + this.$route.params.id + '/permissions', this.form)
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/roles/list')

              this.$root.$emit('roles_permissions_update')

            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: 'Fetch Error',
                text: result.data.message
              })
            }
          })
          .catch(() => {
            this.loading = false

            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Error al procesar, intente de nuevo por favor'
            })
          })
      }
    }
  }
</script>

<style lang="stylus">
    .table-actions
        display flex

</style>

