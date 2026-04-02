<template>
    <div class="row">
        <div class="col-sm-12">
            <form @submit.prevent="validateBeforeSubmit">
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="code">Code</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="code" maxlength="6" name="code"
                                   placeholder="Código del Usuario"
                                   type="text"
                                   v-model="form.code" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('code')"/>
                                <span v-show="errors.has('code')">{{ errors.first('code') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="name">{{ $t('global.name') }}</label>
                        <div class="col-sm-5">
                            <input class="form-control input" id="name" name="name" placeholder="Nombre del Usuario"
                                   type="text"
                                   v-model="form.name" v-validate="'required'">
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('name')"/>
                                <span v-show="errors.has('name')">{{ errors.first('name') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="address">Area / Equipo</label>
                        <div class="col-sm-5">
                            <v-select :options="teams"
                                      :value="form.department_team_id"
                                      @input="teamChange"
                                      label="name"
                                      placeholder="Area / Equipo"
                                      v-validate="'required'"
                                      v-model="teamSelected" name="teams" id="teams" style="height: 35px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        <span>{{ option.name }}</span>
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        <span>{{ option.name }}</span>
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('teams')"/>
                                <span v-show="errors.has('teams')">{{ errors.first('teams') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="address">Cargo</label>
                        <div class="col-sm-5">
                            <v-select :options="positions"
                                      :value="form.position_id"
                                      @input="positionChange"
                                      label="name"
                                      placeholder="Cargo"
                                      v-validate="'required'"
                                      v-model="positionSelected" name="position" id="position" style="height: 35px;">
                                <template slot="option" slot-scope="option">
                                    <div class="d-center">
                                        <span>{{ option.name }}</span>
                                    </div>
                                </template>
                                <template slot="selected-option" slot-scope="option">
                                    <div class="selected d-center">
                                        <span>{{ option.name }}</span>
                                    </div>
                                </template>
                            </v-select>
                            <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                   style="margin-left: 5px;" v-show="errors.has('teams')"/>
                                <span v-show="errors.has('teams')">{{ errors.first('teams') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <form autocomplete="off">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="address">{{ $t('users.mail') }}</label>
                            <div class="col-sm-5">
                                <input class="form-control input" id="address" name="email"
                                       placeholder="Email del Usuario"
                                       type="text" @change="removeAccents()"
                                       v-model="form.email" v-validate="'required|email'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('email')"/>
                                    <span v-show="errors.has('email')">{{ errors.first('email') }}</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="b-form-group form-group">
                    <form autocomplete="off">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="address">{{ $t('users.password') }}</label>
                            <div class="col-sm-5">
                                <input :class="{'form-control':true }" id="password1" name="password1"
                                       placeholder="Contraseña del Usuario"
                                       type="password" ref="password1"
                                       autocomplete="off"
                                       v-model="form.password" v-validate="'min:6|max:35'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('password')"/>
                                    <span v-show="errors.has('password')">{{ errors.first('password') }}</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="b-form-group form-group">
                    <form autocomplete="off">
                        <div class="form-row">
                            <label class="col-sm-2 col-form-label" for="address">{{
                                    $t('users.repeate_password')
                                }}</label>
                            <div class="col-sm-5">
                                <input :class="{'form-control':true }" id="password2" name="password2"
                                       placeholder="Repetir Contraseña"
                                       type="password" autocomplete="off" data-vv-as="password1"
                                       v-model="form.password2" v-validate="'confirmed:password1'">
                                <div class="bg-danger" style="margin-top: 3px;border-radius: 2px;">
                                    <font-awesome-icon :icon="['fas', 'exclamation-circle']"
                                                       style="margin-left: 5px;" v-show="errors.has('password2')"/>
                                    <span v-show="errors.has('password2')">{{ errors.first('password2') }}</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="rol">{{ $t('users.markets') }}</label>
                        <div class="col-sm-5">
                            <multiselect :clear-on-select="false"
                                         :close-on-select="false"
                                         :multiple="true"
                                         :options="markets"
                                         :preserve-search="true"
                                         :taggable="true"
                                         label="name"
                                         ref="multiselect"
                                         track-by="code"
                                         v-model="marketUser">
                            </multiselect>
                            <!-- @tag="addTag" -->
                        </div>
                    </div>
                </div>
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="rol">{{ $t('users.rol') }}</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="rol" required size="0" v-model="form.role">
                                <option value=""></option>
                                <option :value="rol.value" v-for="rol in roles">
                                    {{ rol.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <!--
                <div class="b-form-group form-group">
                    <div class="form-row">
                        <label class="col-sm-2 col-form-label" for="type">{{ $t('users.user_type') }}</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="type" required size="0" v-model="form.userType">
                                <option value=""></option>
                                <option :value="userType.value" v-for="userType in userTypes">
                                    {{ userType.text }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                -->
                <div class="b-form-group form-group">

                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">¿Es KAM?</label>
                        <div class="col-sm-2">
                            <c-switch :value=true class="mx-1" color="success"
                                      v-model="form.is_kam"
                                      variant="pill">
                            </c-switch>
                        </div>
                        <label class="col-sm-2 col-form-label" title="Business Development Manager">¿Es BDM?</label>
                        <div class="col-sm-2">
                            <c-switch :value=true class="mx-1" color="success"
                                      v-model="form.is_bdm"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="col-sm-2 col-form-label">{{ $t('global.status') }}</label>
                        <div class="col-sm-2">
                            <c-switch :value=true class="mx-1" color="success"
                                      v-model="form.status"
                                      variant="pill">
                            </c-switch>
                        </div>
                        <label class="col-sm-2 col-form-label">Activar envío de correos</label>
                        <div class="col-sm-2">
                            <c-switch :value=true class="mx-1" color="success"
                                      v-model="form.use_email"
                                      variant="pill">
                            </c-switch>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-2 pt-2">Regiones</div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="form-group col-sm-3" v-for="region in businessRegions" :key="region.id">
                                    <label class="col-form-label">
                                        {{ region.description }}
                                        <i class="fas fa-info-circle clickable" :title="getCountriesTooltip(region)"></i>
                                    </label>
                                    <div>
                                        <c-switch
                                            v-model="selectedRegions[`R${region.id}`]"
                                            class="mx-1"
                                            color="success"
                                            variant="pill"
                                        >
                                        </c-switch>
                                    </div>
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
                <button @click="validateBeforeSubmit" class="btn btn-success" type="submit" v-if="!loading">
                    <font-awesome-icon :icon="['fas', 'dot-circle']"/>
                    {{ $t('global.buttons.submit') }}
                </button>
                <router-link :to="{ name: 'UsersList' }" v-if="!loading">
                    <button class="btn btn-danger" type="reset">
                        {{ $t('global.buttons.cancel') }}
                    </button>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import {API} from './../../api'
import Multiselect from 'vue-multiselect'
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css'
import {Switch as cSwitch} from '@coreui/vue'

export default {
    components: {
        vSelect,
        Multiselect,
        cSwitch,
    },
    data: () => {
        return {
            loading: false,
            formAction: 'post',
            rolelenght: 0,
            teamSelected: [],
            positionSelected: [],
            roles: [],
            userTypes: [],
            markets: [],
            marketUser: [],
            codeMarkets: [],
            teams: [],
            positions: [],
            form: {
                id: '',
                code: '',
                name: '',
                email: '',
                password: '',
                password2: '',
                role: '',
                userType: '',
                status: true,
                use_email: true,
                is_kam: false,
                is_bdm: false,
                department_team_id: '',
                position_id: ''
            },
            businessRegions: [],
            selectedRegions: [],
            loadingRegions: false
        }
    },
    mounted() {
        API.get('/departments/teams')
            .then((result) => {
                let teams = []
                result.data.data.forEach(function (item) {
                    teams.push({
                        'id': item.id,
                        'name': item.department + ' - ' + item.team,
                    })
                })
                this.teams = teams
            })

        API.get('/positions')
            .then((result) => {
                this.positions = result.data.data
            })

        if (this.$route.params.id !== undefined) {
            API.get('/users/' + this.$route.params.id)
                .then((result) => {
                    this.form.id = result.data.data.id
                    this.form.code = result.data.data.code
                    this.form.name = result.data.data.name
                    this.form.email = result.data.data.email
                    this.form.status = !!result.data.data.status
                    this.form.use_email = (result.data.data.use_email === 'SI')
                    this.rolelenght = result.data.data.roles.length
                    if (this.rolelenght > 0) {
                        this.form.role = result.data.data.roles[0].id
                    }
                    this.form.userType = result.data.data.user_type_id

                    //markets
                    let arrayMarkets = result.data.data.markets
                    let j = 0
                    let argData = []
                    arrayMarkets.forEach((markets) => {
                        argData[j] = {
                            code: markets.id,
                            name: markets.name
                        }
                        j++
                    })
                    let employee = result.data.data.employee
                    if (employee != null) {
                        if (employee.department_team_id != null && employee.team != null) {
                            this.form.department_team_id = employee.department_team_id
                            this.teamSelected = {
                                'id': employee.team.id,
                                'name': employee.team.department.name + ' - ' + employee.team.name,
                            }
                        }

                        if (employee.position_id != null  && employee.position != null) {
                            this.form.position_id = employee.position_id
                            this.positionSelected = {
                                'id': employee.position.id,
                                'name': employee.position.name,
                            }
                        }

                        this.form.is_kam = !!employee.is_kam
                        this.form.is_bdm = !!employee.is_bdm


                    }

                    this.marketUser = argData
                    this.form.marketUser = argData


                    this.formAction = 'put'
                })
        }
        API.get('/roles/selectBox')
            .then((result) => {
                this.roles = result.data.data
            })
        API.get('/usertypes/selectBox')
            .then((result) => {
                this.userTypes = result.data.data
            })
        //markets
        API.get('/markets/selectbox?lang=' + localStorage.getItem('lang'))
            .then((result) => {
                let mark = result.data.data
                mark.forEach((market) => {
                    this.markets.push({
                        name: market.text,
                        code: market.value
                    })
                })
            }).catch(() => {
            this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('users.error.messages.name'),
                text: this.$t('users.error.messages.connection_error')
            })
        })

        this.loadBusinessRegions()
    },
    methods: {
        removeAccents(){
            this.form.email = this.form.email.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        },
        validateBeforeSubmit() {
            if (this.form.password == '' && this.formAction == 'post') {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('users.title'),
                    text: this.$t('users.error.messages.validate_pass_post')
                })
                return
            }

            if (this.form.password !== '') {
                if (this.form.password !== this.form.password2) {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('users.title'),
                        text: this.$t('users.error.messages.validate_pass')
                    })
                    return
                }
            }

            if (this.form.role == '') {
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('users.title'),
                    text: this.$t('users.error.messages.select_rol')
                })
                return
            }

            const regions = Object.entries(this.selectedRegions).filter(([key, value]) => value == true).map(([key]) => parseInt(key.replace(/^R/, '')));

            if (regions.length === 0){
                this.$notify({
                    group: 'main',
                    type: 'error',
                    title: "Región",
                    text: "Debe seleccionar al menos una región"
                })
                return
            }
            /*
              if (this.form.userType == '') {
                  this.$notify({
                    group: 'main',
                    type: 'error',
                    title: this.$t('users.title'),
                    text: this.$t('users.error.messages.select_type')
                  })
                  return
              }
      */
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.submit()
                } else {
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('global.modules.users'),
                        text: this.$t('users.error.messages.information_complete')
                    })
                    this.loading = false
                }
            })
        },
        teamChange: function (value) {
            let select = value
            if (select != null) {
                this.form.department_team_id = select.id
            } else {
                this.form.department_team_id = ''
            }
        },
        positionChange: function (value) {
            let select = value
            if (select != null) {
                this.form.position_id = select.id
            } else {
                this.form.position_id = ''
            }
        },
        submit() {

            //markets
            let varable = this.marketUser
            let argData = []
            varable.forEach((maarket) => {
                argData.push(maarket.code)
            })
            this.form.status = (this.form.status == false ? 0 : 1)
            this.form.use_email = (this.form.use_email == false ? 'NO' : 'SI')
            this.form.codeMarkets = argData
            this.form.regions = Object.entries(this.selectedRegions).filter(([_, value]) => value == true).map(([key]) => parseInt(key.replace(/^R/, '')));
            this.loading = true;
    
            API({
                method: this.formAction,
                url: (this.$route.params.id !== undefined ? 'users/' + this.$route.params.id : 'users'),
                data: this.form
            })
                .then((result) => {
                    console.log("valor")
                    console.log(result)
                    if (result.data.success === true) {
                        this.$router.push('/users/list')
                    } else {
                        this.$notify({
                            group: 'main',
                            type: 'error',
                            title: this.$t('users.title'),
                            text: this.$t('users.error.messages.information_complete')
                        })

                        this.loading = false
                    }
                })
                .catch((e) => {
                    this.loading = false
                    this.$notify({
                        group: 'main',
                        type: 'error',
                        title: this.$t('users.title'),
                        text: e.data.message
                    })
                })
        },

        getCountriesTooltip(region) {
            if (!region.countries || region.countries.length === 0) {
                return 'Esta región no tiene países asociados';
            }

            return region.countries.map(country => {
                return this.getCountryName(country) + ` (${country.iso})`;
            }).join(', ');
        },
        getCountryName(country) {
            if (country.translations && country.translations.length > 0) {
            const lang = 1;
            const translation = country.translations.find(t => t.language_id == lang);
                return translation ? translation.value : country.iso;
            }
            return country.iso;
        },
        loadBusinessRegions() {
            this.loadingRegions = true;
            API.get('/business_region')
            .then(response => {
                this.businessRegions = response.data.data;
                if (this.$route.params.id) {
                    this.loadUserRegions();
                }
                console.log("aqui esta modificando");
            })
            .catch(error => {
                console.error('Error loading regions:', error);
                this.$notify({
                group: 'main',
                type: 'error',
                title: 'Error',
                text: 'No se pudieron cargar las regiones'
                });
            })
            .finally(() => {
                this.loadingRegions = false;
            });
        },
        loadUserRegions() {
             this.loadingRegions = true;
            API.get(`/users/${this.$route.params.id}/regions`)
            .then(response=>{
                const ids = response.data.data.map(e => e.id);
                this.businessRegions.forEach((e) => {
                    this.selectedRegions[`R${e.id}`] = ids.includes(e.id);
                });
            })
            .catch(err=>{
                console.log(err);
            })
            .finally(() =>{
                this.loadingRegions = false;
            });
        }
    }
}
</script>

<style lang="stylus">
.selected {
    background-color: #41b883 !important;
    border-radius: 4px;
    padding: 3px;
    color: #fff !important;
}
</style>
<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>

