<template>
    <div class="row">
        <div class="col-sm-12">
            <form>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="slug">Slug</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="slug" name="slug" placeholder="Valor del Slug"
                                   type="text"
                                   v-model="form.slug">
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{$t('translations.translate')}}</label>
                        <div class="col-sm-5">
                            <div class="row" v-for="language in languages">
                                <div class="col-4">
                                    <label>{{language.text}}</label>
                                </div>
                                <div class="col-8">
                                    <input :name="'value-'+language.value" class="form-control" type="text"
                                           v-model="form.values[language.value]"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-6">
            <div slot="footer">
                <img src="/images/loading.svg" v-if="loading" width="40px"/>
                <button @click="submit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{$t('global.buttons.submit')}}
                </button>
                <router-link :to="{ name: 'TranslationsList' }" v-if="!loading">
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

  export default {
    data: () => {
      return {
        languages: [],
        loading: false,
        formAction: 'post',
        form: {
          language_id: 1,
          slug: '',
          values: []
        }
      }
    },
    mounted () {
      if (this.$route.params.id !== undefined) {
        API.get('/translations/' + this.$route.params.id)
          .then((result) => {
            this.form.slug = result.data.data[1].slug

            let values = {}
            Object.keys(result.data.data).map((key) => {
              values[result.data.data[key].language_id] = result.data.data[key].value
            })
            this.form.values = values

            this.formAction = 'put'
          })
      }
      API.get('/language/selectbox')
        .then((result) => {
          let languages = {}
          result.data.data.forEach((item) => {
            languages[item.value] = item
          })

          this.languages = languages
        })
    },
    methods: {
      submit () {
        this.loading = true

        API({
          method: this.formAction,
          url: 'translations' + (this.$route.params.id !== undefined ? '/'+this.$route.params.id : ''),
          data: this.form
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/translations/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('translations.title'),
                text: result.data.message
              })

              this.loading = false
            }
          })
      },
      remove () {
        this.loading = true

        API({
          method: 'DELETE',
          url: 'translations/' + (this.$route.params.id !== undefined ? this.$route.params.id : '')
        })
          .then((result) => {
            if (result.data.success === true) {
              this.$router.push('/translations/list')
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('translations.title'),
                text: result.data.message
              })

              this.loading = false
            }
          })
      }
    }
  }
</script>

<style lang="stylus">

</style>
