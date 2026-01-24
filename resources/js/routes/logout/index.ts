import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::standalone
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:63
 * @route '/logout'
 */
export const standalone = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: standalone.url(options),
    method: 'post',
})

standalone.definition = {
    methods: ["post"],
    url: '/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::standalone
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:63
 * @route '/logout'
 */
standalone.url = (options?: RouteQueryOptions) => {
    return standalone.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::standalone
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:63
 * @route '/logout'
 */
standalone.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: standalone.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::standalone
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:63
 * @route '/logout'
 */
    const standaloneForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: standalone.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::standalone
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:63
 * @route '/logout'
 */
        standaloneForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: standalone.url(options),
            method: 'post',
        })
    
    standalone.form = standaloneForm
const logout = {
    standalone: Object.assign(standalone, standalone),
}

export default logout