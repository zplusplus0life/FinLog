import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
export const laporan = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: laporan.url(options),
    method: 'get',
})

laporan.definition = {
    methods: ["get","head"],
    url: '/admin/laporan',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
laporan.url = (options?: RouteQueryOptions) => {
    return laporan.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
laporan.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: laporan.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
laporan.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: laporan.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
    const laporanForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: laporan.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
        laporanForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: laporan.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:60
 * @route '/admin/laporan'
 */
        laporanForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: laporan.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    laporan.form = laporanForm
const admin = {
    laporan: Object.assign(laporan, laporan),
}

export default admin