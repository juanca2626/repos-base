<template>
    <div>
        <b-nav class="fondo-nav" tabs>
            <div v-for="item in items" v-show="hasPermissions(item.permission)">
                <template v-if="item.status==='active'">
                    <b-nav-item @click="tabsStatus(item.link, item.id)" active>
                        <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/> <span class="s-color">{{$t(item.title)}}</span>
                    </b-nav-item>
                </template>
                <template v-else>
                    <b-nav-item @click="tabsStatus(item.link, item.id)">
                        <font-awesome-icon :icon="['fas', item.icon]" class="m-0"/> <span>{{$t(item.title)}}</span>
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
                        id: 0,
                        title: 'Galería',
                        link: '/manage_state/gallery/list',
                        icon: 'images',
                        status: '',
                        permission: [
                            'clientsellers'
                        ]
                    },

                ],
            };
        },
        created: function() {
            if ((this.$route.name === 'LayoutStateGallery') || (this.$route.name === 'ListStateGallery')) {
                this.items[0].status = 'active';
            }
        },
        methods: {
            tabsStatus: function(link, id) {
                for (var i = this.items.length - 1; i >= 0; i--) {
                    if (id == this.items[i].id) {
                        this.items[i].status = 'active';
                    } else {
                        this.items[i].status = '';
                    }
                }
                this.$router.push('/states/' + this.$route.params.state_id + link);

            },
            hasPermissions: function(permission) {
                let flag = false;
                for(var i = 0; i < permission.length; i++){
                    if( this.$can('read', permission[i])){
                        flag = true
                    }
                }
                return flag
            }
        },
    };
</script>

<style lang="stylus">
    .s-color {
        color: red;
    }

    .fondo-nav {
        background-color: #f9fbfc;
    }

</style>
