<template>
    <div>
        <span style="display: block;height: 31px;">{{ translations.quote.label.attach_doc }}</span>
        <div v-if="upload_files.file_url_link == '' ">
            <template v-if="loading">
                <label for="file-2">             
                    <span class="iborrainputfile">       
                        <i class="spinner-grow" ></i>             
                        {{ translations.quote.label.uploading }} ...
                    </span>
                </label>
            </template>
            <template v-else>                
                <input type="file" :name="'file-' + passenger_index" :id="'file-' + passenger_index" class="inputfile inputfile-2" v-on:change="handleFileUploadDocument($event)" />
                <label :for="'file-' + passenger_index" class="btn btn-danger text-white">
                    <span class="iborrainputfile">      
                        {{ translations.quote.label.select_file }}
                    </span>
                </label>
            </template>
        </div>
        <div v-else>
            <a :href="upload_files.file_url_link" v-if="['jpg', 'png', 'jpeg'].includes(upload_files.file_url_ext)" target="_blank">
                <img :src="upload_files.file_url_link" width="150" alt="File" /> 
            </a>
            <a :href="upload_files.file_url_link"  target="_blank" v-else>
                <img src="/images/document.jpg" width="150" alt="File" />
            </a>
            <button type="button" @click="delete_upload()">
                {{ translations.quote.label.delete }}
            </button>
        </div>
    </div>
</template>

<script>
 
export default {
    props: ['folder_aws', 'document', 'passenger_index'],
    data: () => {
        return { 
            folder: '',
            loading: false,
            upload_files: {
                name: '',
                error: '',
                chunks: '',
                parts: '',
                file_url: '',
                file_url_link: '',
                file_url_ext: ''
            },
            translations: { 
                quote: {
                    label: {},
                    btn: {}
                }
            },
            lang: localStorage.getItem('lang')
        }
    },
    created: function () {
    },
    async mounted () {
        this.upload_files.file_url_link = this.document ? this.document :''    
        this.upload_files.file_url_ext =this.getFileExtension(this.document);  
        this.folder =  this.folder_aws ? this.folder_aws :'' 
        await this.setTranslations();  
    },
    methods: {
        async setTranslations() {            
            await axios.get(baseURL + 'translation/' + this.lang + '/slug/quote').then((data) => {
                this.translations.quote = data.data
            })
        },
        async handleFileUploadDocument ($event) {                   
            let target = $event.target          
            if (target.files) { 
                this.loading = true;
                this.upload_files.name = target.files[0].name
                await this.encodeFile64Chunk(target.files[0]);
                await this.sendFilesToS3(); 
                this.loading = false;
            }
        },
        async encodeFile64Chunk (file) {
            const chunkSize = 4 * 1024 * 1024; // 4 MB
		    await this.chunkFile(file, chunkSize);
        },
        async chunkFile (file, chunkSize) {
            await this.encodeFile64(file, chunkSize)
        },
        async encodeFile64 (file, chunkSize) {

            const reader = new FileReader()
            const filePromises = new Promise((resolve, reject) => {

                reader.onloadend = async () => {

                    try {
                        let content = reader.result
                        let regexChunk = new RegExp(`.{1,${chunkSize}}`, 'g')
                        let chunks = content.match(regexChunk)
                        this.upload_files.chunks = chunks
                        this.upload_files.parts = chunks.length
                        
                        for (let i = 1; i <= chunks.length; i++) {
                            const chunk = chunks[i - 1];

                            let response = await this.sendChunkToLambda({
                                filename: this.upload_files.name,
                                part: i,
                                base64_chunk: chunk
                            }) 
                            this.upload_files.file_url = response.link

                            
                        }
                        resolve([]);

                    } catch (err) {
                        reject(err);
                    }


                    
                }
                reader.onerror = (error) => {
                    reject(error);
                };
                reader.readAsDataURL(file)
            });

            await filePromises.then();            


        },    
        async sendChunkToLambda(params){

            return new Promise(function (resolve, reject) {
                var request = new XMLHttpRequest()
                request.open('POST', window.url_s3 + 'chunk/upload', true)
                request.setRequestHeader("Content-Type", "application/json;charset=UTF-8"); 
                request.send(JSON.stringify(params))
                request.onreadystatechange = function () {
                    if (request.readyState < 4) { 

                    } else if (request.readyState === 4) {
                        if (request.status === 200 || request.status < 300) {
                            resolve(JSON.parse(request.response)); 
                        } else { 

                        }
                    }
                }
            });         
        }, 
        async sendFilesToS3(){
            if(this.upload_files.error == '')
            {
                
                let params = {
                    nroref : Math.floor(Math.random() * 99999999),
                    folder : this.folder,
                    filename : this.upload_files.name,
                    file_url : this.upload_files.file_url,
                    parts : this.upload_files.parts,
                    base64 : ''
                }

                let response = await this.sendToS3([params]) 
                this.upload_files.file_url_link = response.links[0].link 
                this.upload_files.file_url_ext =this.getFileExtension(response.links[0].link);
                this.$emit('onResponseFiles', response.links[0].link)
            }            
        },    
        async sendToS3(params){


            return new Promise(function (resolve, reject) {
                var request = new XMLHttpRequest()
                request.open('POST', window.url_s3 + 'upload', true)
                request.setRequestHeader("Content-Type", "application/json;charset=UTF-8"); 
                request.send(JSON.stringify({files: params}))
                request.onreadystatechange = function () {
                    if (request.readyState < 4) { 

                    } else if (request.readyState === 4) {
                        if (request.status === 200 || request.status < 300) {
                            resolve(JSON.parse(request.response)); 
                        } else { 

                        }
                    }
                }
            });  
        },                     
        delete_upload: function (){
            this.upload_files.file_url_link = ''
            this.$emit('onResponseFiles', '') 
        },
        getFileExtension: function (filename){
            if(filename){
                return filename.slice((filename.lastIndexOf(".") - 1 >>> 0) + 2);
            }
            return "";            
        } 
    }
}
</script>

<style lang="scss" scoped>

.inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
}

.inputfile + label {
    max-width: 80%;
    font-size: 1.25rem;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    display: inline-block;
    overflow: hidden;
    padding: 0.625rem 1.25rem;
}

.inputfile + label svg {
    width: 1em;
    height: 1em;
    vertical-align: middle;
    fill: currentColor;
    margin-top: -0.25em;
    margin-right: 0.25em;
}

.inputfile + label svg.icon{
	margin: 0;
    width: 1.3em;
    height: 1.3em;
    display: inline-block;
    fill: none;
}

.iborrainputfile {
	font-size:16px; 
	font-weight:normal;
	font-family: 'Lato';
}

/* style 1 */

.inputfile-1 + label {
    color: #fff;
    background-color: #c39f77;
}

.inputfile-1:focus + label,
.inputfile-1.has-focus + label,
.inputfile-1 + label:hover {
    background-color: #9f8465;
}



.inputfile-2 + label {
    color: #c39f77;
    border: 2px solid currentColor;
}

.inputfile-2:focus + label,
.inputfile-2.has-focus + label,
.inputfile-2 + label:hover {
    color: #9f8465;
}

</style>