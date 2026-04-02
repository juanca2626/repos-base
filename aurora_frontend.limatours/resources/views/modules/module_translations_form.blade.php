@extends('layouts.app')
@section('metas')
    @if(Route::currentRouteName() =="modules.translations_create")
        <meta name="module_id" content="{{ $id }}">
    @endif
@endsection
@section('content')
    <div class="container">
        <button @click="addNewTranslation">Add New Translation</button>
        <vue-ads-table-tree
                :columns="columns"
                :rows="rows"
                :filter="filter"
                :page="page"
                @filter-change="filterChanged"
                @page-change="pageChanged"
                :key="treeTable">
            <template slot="slug" slot-scope="props">
                <input type="text" v-model="props.row.slug"  v-if="props.row.field">
                <div v-else>@{{ props.row.slug }}</div>
            </template>
            <template slot="translation" slot-scope="props" v-if="props.row.translation!=null">
                <input type="text" v-model="props.row.translation">
            </template>
        </vue-ads-table-tree>
        <button @click="saveTranslations" class="btn btn-success">Save Translations</button>
        <a href="/modules/{{$id}}/translations" class="btn btn-info"> Listado de Traducciones</a>
    </div>
@endsection
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
                array_languages:[],
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
                this.getArrayLanguages()
            },
            methods: {
                getArrayLanguages: function () {
                    axios.get('api/languages/array')
                        .then(response => {
                            this.array_languages = response.data
                            this.rows.push({
                                slug:null,
                                field:true,
                                translation:null,
                                _children:  JSON.parse(JSON.stringify(response.data))
                            })
                            this.updateTable()
                        })
                },
                addNewTranslation: function () {
                    this.rows.push({
                        slug:"",
                        translation:null,
                        field:true,
                        _children: JSON.parse(JSON.stringify(this.array_languages))
                    })
                    this.updateTable()
                },
                saveTranslations: function () {
                    axios.post( baseURL +'/translations/'+this.module_id, {
                        translations: this.rows
                    }).then((result) => {

                        this.$toast.success(result.data.message, {
                            // override the global option
                            position: 'top-right'
                        })
                    })
                    this.rows = []
                    this.getArrayLanguages()
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
