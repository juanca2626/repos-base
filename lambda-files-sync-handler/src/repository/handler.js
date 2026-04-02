import axios from 'axios'

export const handler = async (event) => {
    let response = ''
    let code = 200
    let params = {}

    try
    {
        for (const record of event.Records)
        {
            let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body
            let data = body.payload[0]
            let files = []
            
            data.upload_files.forEach((item) => {
                if(item.error == '')
                {
                    files.push({
                        nroref: data.nroref,
                        folder: data.type_alias,
                        filename: item.name,
                        file_url: item.file_url,
                        parts: item.parts,
                        base64: ''
                    })
                }
            })

            params = {
                'files': files
            }

            let response_upload = await axios.post(`${process.env.BACKEND_URL_S3}upload`, JSON.stringify(params));
            let links = response_upload.data.links
            let new_files = []
            
            links.forEach(async (item, i) => {
                new_files.push({
                    name: data.upload_files[i].name,
                    file_url: item.link
                })
            })

            params = {
                user: data.user,
                files: new_files,
                nroref: data.nroref,
                type_id: data.type_id,
                information_aditional: data.information_aditional,
                flag_hide: data.flag_hide,
                passengers: data.passengers
            }
        
            response = await axios.post(`${process.env.BACKEND_URL_AURORA_PROD}passenger_files`, params)
        }
    }
    catch(err)
    {
        response = JSON.stringify(err)
        code = 500
    }
    finally
    {
        let _response = {
            statusCode: code,
            body: response,
        }
        console.log("Final Response: ", _response)
    }
}