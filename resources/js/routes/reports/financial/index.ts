import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
export const publicMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: publicMethod.url(options),
    method: 'get',
})

publicMethod.definition = {
    methods: ["get","head"],
    url: '/api/public/financial-report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
publicMethod.url = (options?: RouteQueryOptions) => {
    return publicMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
publicMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: publicMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
publicMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: publicMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
    const publicMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: publicMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
        publicMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: publicMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TransactionController::publicMethod
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/public/financial-report'
 */
        publicMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: publicMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    publicMethod.form = publicMethodForm
const financial = {
    public: Object.assign(publicMethod, publicMethod),
}

export default financial