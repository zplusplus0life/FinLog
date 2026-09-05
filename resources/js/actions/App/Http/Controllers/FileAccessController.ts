import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
export const listFiles = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listFiles.url(options),
    method: 'get',
})

listFiles.definition = {
    methods: ["get","head"],
    url: '/api/files/list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
listFiles.url = (options?: RouteQueryOptions) => {
    return listFiles.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
listFiles.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: listFiles.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
listFiles.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: listFiles.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
    const listFilesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: listFiles.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
        listFilesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listFiles.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FileAccessController::listFiles
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
        listFilesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: listFiles.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    listFiles.form = listFilesForm
/**
* @see \App\Http\Controllers\FileAccessController::uploadFile
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
export const uploadFile = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(options),
    method: 'post',
})

uploadFile.definition = {
    methods: ["post"],
    url: '/api/files/upload',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FileAccessController::uploadFile
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
uploadFile.url = (options?: RouteQueryOptions) => {
    return uploadFile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::uploadFile
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
uploadFile.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadFile.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\FileAccessController::uploadFile
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
    const uploadFileForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: uploadFile.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::uploadFile
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
        uploadFileForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: uploadFile.url(options),
            method: 'post',
        })
    
    uploadFile.form = uploadFileForm
/**
* @see \App\Http\Controllers\FileAccessController::deleteFile
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
export const deleteFile = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFile.url(args, options),
    method: 'delete',
})

deleteFile.definition = {
    methods: ["delete"],
    url: '/api/files/{filename}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FileAccessController::deleteFile
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
deleteFile.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { filename: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    filename: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        filename: args.filename,
                }

    return deleteFile.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::deleteFile
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
deleteFile.delete = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteFile.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\FileAccessController::deleteFile
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
    const deleteFileForm = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteFile.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::deleteFile
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
        deleteFileForm.delete = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteFile.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteFile.form = deleteFileForm
const FileAccessController = { listFiles, uploadFile, deleteFile }

export default FileAccessController