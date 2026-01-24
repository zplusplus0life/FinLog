import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
export const laporan = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: laporan.url(options),
    method: 'get',
})

laporan.definition = {
    methods: ["get","head"],
    url: '/staff/laporan',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
laporan.url = (options?: RouteQueryOptions) => {
    return laporan.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
laporan.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: laporan.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
laporan.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: laporan.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
    const laporanForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: laporan.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:122
 * @route '/staff/laporan'
 */
        laporanForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: laporan.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:122
 * @route '/staff/laporan'
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
const staff = {
    laporan: Object.assign(laporan, laporan),
}

export default staff