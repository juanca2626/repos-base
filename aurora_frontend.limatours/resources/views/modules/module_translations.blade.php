@extends('layouts.app')
@section('metas')
    @if(Route::currentRouteName() =="modules.translations")
        <meta name="module_id" content="{{ $id }}">
    @endif
@endsection
@section('content')
    <div class="container">
        <a href="/modules/{{$id}}/translations/create" class="btn btn-info">Create Translations</a>
        <button class="btn btn-info" @click="updateTranslations">Actualizar Traducciones</button>
        <vue-ads-table-tree
                :columns="columns"
                :rows="rows"
                :filter="filter"
                :page="page"
                @filter-change="filterChanged"
                @page-change="pageChanged"
                :key="treeTable">
            <template slot="slug" slot-scope="props">
                @{{ props.row.slug }}
                <i class="icon fas fa-trash" v-if="props.row.field"></i>
            </template>
            <template slot="translation" slot-scope="props" v-if="props.row.translate">
                <input type="text" v-model="props.row.translation">
            </template>
        </vue-ads-table-tree>
    </div>
@stop
@section('css')
<style>
.container{
    padding-top: 30px;
}
.vue-ads-flex-grow, .vue-ads-text-sm, .vue-ads-cursor-pointer, .vue-ads-pr-2.vue-ads-leading-loose, .vue-ads-w-6{
    font-size: 14px !important;
}
input{
    font-size: 14px !important;
    border: 1px solid #e2e8f0;
    padding: 5px 10px;
}
</style>

@endsection
@section('js')
    <script>
        new Vue({
            el: '#app',
            data: {
                treeTable: 1,
                module_id: null,
                page: 0,
                filter: '',
                columns: [
                    {
                        property: 'slug',
                        title: 'Slug',
                        direction: null,
                        filterable: true,
                        collapseIcon: true,
                    },
                    {
                        property: 'translation',
                        title: 'Translation',
                        direction: null,
                        filterable: true,
                    },
                ],
                rows: []
            },
            created: function () {
                if (document.head.querySelector('meta[name="module_id"]')) {
                    this.module_id = document.head.querySelector('meta[name="module_id"]').content
                }
            },
            mounted () {
                this.getTranslations()
            },
            methods: {
                getTranslations: function () {
                    axios.get(baseURL+'/translations/'+this.module_id)
                        .then(response => {
                            this.rows = response.data
                            this.updateTable()
                        })
                },
                updateTranslations: function () {
                    axios.put(baseURL+'/translations/'+this.module_id, {
                        translations: this.rows
                    }).then((result) => {

                        this.$toast.success(result.data.message, {
                            // override the global option
                            position: 'top-right'
                        })
                    })
                    this.updateTable()
                },
                updateTable: function () {
                    this.treeTable+=1
                },
                filterChanged (filter) {
                    this.filter = filter;
                },
                pageChanged (page) {
                    this.page = page;
                },
            }
        })
    </script>
@endsection
