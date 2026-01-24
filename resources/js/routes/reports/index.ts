import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import financialA56e88 from './financial'
/**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
export const financial = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: financial.url(options),
    method: 'get',
})

financial.definition = {
    methods: ["get","head"],
    url: '/api/financial-report',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
financial.url = (options?: RouteQueryOptions) => {
    return financial.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
financial.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: financial.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
financial.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: financial.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
    const financialForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: financial.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
        financialForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: financial.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TransactionController::financial
 * @see app/Http/Controllers/TransactionController.php:190
 * @route '/api/financial-report'
 */
        financialForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: financial.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    financial.form = financialForm
const reports = {
    financial: Object.assign(financial, financialA56e88),
}

export default reports