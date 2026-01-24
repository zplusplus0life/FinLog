import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
export const list = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})

list.definition = {
    methods: ["get","head"],
    url: '/api/files/list',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
list.url = (options?: RouteQueryOptions) => {
    return list.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
list.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: list.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
list.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: list.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
    const listForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: list.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
        listForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FileAccessController::list
 * @see app/Http/Controllers/FileAccessController.php:65
 * @route '/api/files/list'
 */
        listForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: list.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    list.form = listForm
/**
* @see \App\Http\Controllers\FileAccessController::upload
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
export const upload = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/api/files/upload',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FileAccessController::upload
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
upload.url = (options?: RouteQueryOptions) => {
    return upload.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::upload
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
upload.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\FileAccessController::upload
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
    const uploadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: upload.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::upload
 * @see app/Http/Controllers/FileAccessController.php:110
 * @route '/api/files/upload'
 */
        uploadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: upload.url(options),
            method: 'post',
        })
    
    upload.form = uploadForm
/**
* @see \App\Http\Controllers\FileAccessController::deleteMethod
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
export const deleteMethod = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/api/files/{filename}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FileAccessController::deleteMethod
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
deleteMethod.url = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return deleteMethod.definition.url
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::deleteMethod
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
deleteMethod.delete = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\FileAccessController::deleteMethod
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
    const deleteMethodForm = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteMethod.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::deleteMethod
 * @see app/Http/Controllers/FileAccessController.php:155
 * @route '/api/files/{filename}'
 */
        deleteMethodForm.delete = (args: { filename: string | number } | [filename: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteMethod.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteMethod.form = deleteMethodForm
/**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
export const serve = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serve.url(args, options),
    method: 'get',
})

serve.definition = {
    methods: ["get","head"],
    url: '/files/{role}/{filename}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
serve.url = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    role: args[0],
                    filename: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        role: args.role,
                                filename: args.filename,
                }

    return serve.definition.url
            .replace('{role}', parsedArgs.role.toString())
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
serve.get = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serve.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
serve.head = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: serve.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
    const serveForm = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: serve.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
        serveForm.get = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serve.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\FileAccessController::serve
 * @see app/Http/Controllers/FileAccessController.php:16
 * @route '/files/{role}/{filename}'
 */
        serveForm.head = (args: { role: string | number, filename: string | number } | [role: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serve.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    serve.form = serveForm
const files = {
    list: Object.assign(list, list),
upload: Object.assign(upload, upload),
delete: Object.assign(deleteMethod, deleteMethod),
serve: Object.assign(serve, serve),
}

export default files