import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
export const downloadPdf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf.url(options),
    method: 'get',
})

downloadPdf.definition = {
    methods: ["get","head"],
    url: '/api/export/pdf',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
downloadPdf.url = (options?: RouteQueryOptions) => {
    return downloadPdf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
downloadPdf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: downloadPdf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
downloadPdf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: downloadPdf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
    const downloadPdfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: downloadPdf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
        downloadPdfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\ExportController::downloadPdf
 * @see app/Http/Controllers/ExportController.php:12
 * @route '/api/export/pdf'
 */
        downloadPdfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: downloadPdf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    downloadPdf.form = downloadPdfForm
const ExportController = { downloadPdf }

export default ExportController