<template>
    <div>
        <b-dropdown class="mt-2 ml-2 mb-0" dropright size="sm">
            <template slot="button-content">
                <font-awesome-icon :icon="['fas', 'bars']" class="ml-1 p-0"/>
            </template>
            <div v-for="(option) in options">
                <router-link :to="'/'+option.link+custom_id" class="nav-link m-0 p-0"
                             v-if="option.type_action==='custom'">
                    <b-dropdown-item-button
                        @click="option.hasOwnProperty('callback') && emitCallback(option.callback)"
                        class="m-0 p-0">
                        <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                        {{ btnText(option) }}
                    </b-dropdown-item-button>
                </router-link>
                <router-link :to="'/'+option.link+id" class="nav-link m-0 p-0" v-if="option.type_action==='link'">
                    <b-dropdown-item-button
                        @click="option.hasOwnProperty('callback') && emitCallback(option.callback)"
                        class="m-0 p-0">
                        <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                        {{ btnText(option) }}
                    </b-dropdown-item-button>
                </router-link>
                <b-dropdown-item-button
                    @click="showModal(option)"
                    class="m-0 p-0" v-if="option.type_action==='button'">
                    <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                    {{ btnText(option) }}
                </b-dropdown-item-button>

                <template v-if="option.type_action==='editButton'">
                    <b-dropdown-item-button
                        @click="edit"
                        class="m-0 p-0" v-if="option.type_action==='editButton'">
                        <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                        {{ btnText(option) }}
                    </b-dropdown-item-button>
                </template>
                <router-link :to="option.link+id+option.link2" v-if="option.type_action==='manageLink'">
                    <b-dropdown-item-button
                        @click="option.hasOwnProperty('callback') && emitCallback(option.callback)"
                        class="m-0 p-0">
                        <font-awesome-icon :icon="['fas', option.icon]" class="m-0"/>
                        {{ btnText(option) }}
                    </b-dropdown-item-button>
                </router-link>
            </div>
        </b-dropdown>

        <b-modal :title="option.type" centered ref="my-modal" size="sm" :no-close-on-backdrop=true
                 :no-close-on-esc=true>

            <p class="text-center">{{ option.msn }} {{ name }} ?</p>

            <p class="text-center" v-if="false">
                Espere por favor <img src="/images/loading.svg" width="40px"/>
            </p>

            <div slot="modal-footer">
                <button @click="option.hasOwnProperty('callback_delete') && emitCallbackDelete(option.callback_delete)"
                        class="btn btn-success">{{ $t('global.buttons.accept') }}
                </button>
                <button @click="hideModal()" class="btn btn-danger">{{ $t('global.buttons.cancel') }}</button>
            </div>
        </b-modal>

    </div>
</template>
<script>
import BDropDown from 'bootstrap-vue/es/components/dropdown/dropdown'
import BDropDownItemButton from 'bootstrap-vue/es/components/dropdown/dropdown-item-button'
import BModal from 'bootstrap-vue/es/components/modal/modal'

export default {
    components: {
        'b-dropdown': BDropDown,
        'b-dropdown-item-button': BDropDownItemButton,
        BModal
    },
    data: () => {
        return {
            option: { type: '', msn: '' },
        }
    },
    methods: {
        emitCallback (callback) {
            this.$emit(callback)
        },
        emitCallbackDelete (callback_delete) {
            this.$emit(callback_delete)
            this.hideModal()
        },
        emitCallbackConfirm (callback_confirm) {
            this.$emit(callback_confirm)
        },
        btnText (option) {
            return option.text !== '' ? option.text : this.$i18n.t('global.buttons.' + option.type)
        },
        showModal (option) {
            this.option = option

            // Si no requiere confirmación, emite directo y no abre modal
            if (option && option.confirm === false) {
                // Prioriza 'callback'; si no hay, intenta 'callback_delete'
                const cb = option.callback || option.callback_delete
                if (cb) this.$emit(cb)
                return
            }

            // Comportamiento normal con modal
            this.option.msn = option.msn ? option.msn : option.type
            this.$refs['my-modal'].show()
        },
        hideModal () {
            this.$refs['my-modal'].hide()
        },
        edit () {
            this.$emit('edit', true)
        },
    },
    props: ['id', 'options', 'name', 'custom_id']
}
</script>
<style>
.dropdown-toggle::after {
    display: none;
}
</style>




