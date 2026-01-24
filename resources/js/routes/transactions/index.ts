import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\TransactionController::store
 * @see app/Http/Controllers/TransactionController.php:47
 * @route '/api/transactions'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/transactions',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TransactionController::store
 * @see app/Http/Controllers/TransactionController.php:47
 * @route '/api/transactions'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::store
 * @see app/Http/Controllers/TransactionController.php:47
 * @route '/api/transactions'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TransactionController::store
 * @see app/Http/Controllers/TransactionController.php:47
 * @route '/api/transactions'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TransactionController::store
 * @see app/Http/Controllers/TransactionController.php:47
 * @route '/api/transactions'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
export const pending = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pending.url(options),
    method: 'get',
})

pending.definition = {
    methods: ["get","head"],
    url: '/api/transactions/pending',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
pending.url = (options?: RouteQueryOptions) => {
    return pending.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
pending.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pending.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
pending.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pending.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
    const pendingForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pending.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
        pendingForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pending.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TransactionController::pending
 * @see app/Http/Controllers/TransactionController.php:137
 * @route '/api/transactions/pending'
 */
        pendingForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pending.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pending.form = pendingForm
/**
* @see \App\Http\Controllers\TransactionController::approve
 * @see app/Http/Controllers/TransactionController.php:150
 * @route '/api/transactions/{transaction}/approve'
 */
export const approve = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/api/transactions/{transaction}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TransactionController::approve
 * @see app/Http/Controllers/TransactionController.php:150
 * @route '/api/transactions/{transaction}/approve'
 */
approve.url = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { transaction: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { transaction: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    transaction: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        transaction: typeof args.transaction === 'object'
                ? args.transaction.id
                : args.transaction,
                }

    return approve.definition.url
            .replace('{transaction}', parsedArgs.transaction.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::approve
 * @see app/Http/Controllers/TransactionController.php:150
 * @route '/api/transactions/{transaction}/approve'
 */
approve.post = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TransactionController::approve
 * @see app/Http/Controllers/TransactionController.php:150
 * @route '/api/transactions/{transaction}/approve'
 */
    const approveForm = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TransactionController::approve
 * @see app/Http/Controllers/TransactionController.php:150
 * @route '/api/transactions/{transaction}/approve'
 */
        approveForm.post = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve.url(args, options),
            method: 'post',
        })
    
    approve.form = approveForm
/**
* @see \App\Http\Controllers\TransactionController::reject
 * @see app/Http/Controllers/TransactionController.php:169
 * @route '/api/transactions/{transaction}/reject'
 */
export const reject = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/api/transactions/{transaction}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TransactionController::reject
 * @see app/Http/Controllers/TransactionController.php:169
 * @route '/api/transactions/{transaction}/reject'
 */
reject.url = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { transaction: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { transaction: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    transaction: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        transaction: typeof args.transaction === 'object'
                ? args.transaction.id
                : args.transaction,
                }

    return reject.definition.url
            .replace('{transaction}', parsedArgs.transaction.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::reject
 * @see app/Http/Controllers/TransactionController.php:169
 * @route '/api/transactions/{transaction}/reject'
 */
reject.post = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TransactionController::reject
 * @see app/Http/Controllers/TransactionController.php:169
 * @route '/api/transactions/{transaction}/reject'
 */
    const rejectForm = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TransactionController::reject
 * @see app/Http/Controllers/TransactionController.php:169
 * @route '/api/transactions/{transaction}/reject'
 */
        rejectForm.post = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject.url(args, options),
            method: 'post',
        })
    
    reject.form = rejectForm
/**
* @see \App\Http\Controllers\TransactionController::update
 * @see app/Http/Controllers/TransactionController.php:96
 * @route '/api/transactions/{transaction}'
 */
export const update = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/api/transactions/{transaction}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\TransactionController::update
 * @see app/Http/Controllers/TransactionController.php:96
 * @route '/api/transactions/{transaction}'
 */
update.url = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { transaction: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { transaction: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    transaction: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        transaction: typeof args.transaction === 'object'
                ? args.transaction.id
                : args.transaction,
                }

    return update.definition.url
            .replace('{transaction}', parsedArgs.transaction.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::update
 * @see app/Http/Controllers/TransactionController.php:96
 * @route '/api/transactions/{transaction}'
 */
update.put = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\TransactionController::update
 * @see app/Http/Controllers/TransactionController.php:96
 * @route '/api/transactions/{transaction}'
 */
    const updateForm = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TransactionController::update
 * @see app/Http/Controllers/TransactionController.php:96
 * @route '/api/transactions/{transaction}'
 */
        updateForm.put = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\TransactionController::destroy
 * @see app/Http/Controllers/TransactionController.php:122
 * @route '/api/transactions/{transaction}'
 */
export const destroy = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/transactions/{transaction}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\TransactionController::destroy
 * @see app/Http/Controllers/TransactionController.php:122
 * @route '/api/transactions/{transaction}'
 */
destroy.url = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { transaction: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { transaction: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    transaction: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        transaction: typeof args.transaction === 'object'
                ? args.transaction.id
                : args.transaction,
                }

    return destroy.definition.url
            .replace('{transaction}', parsedArgs.transaction.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\TransactionController::destroy
 * @see app/Http/Controllers/TransactionController.php:122
 * @route '/api/transactions/{transaction}'
 */
destroy.delete = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\TransactionController::destroy
 * @see app/Http/Controllers/TransactionController.php:122
 * @route '/api/transactions/{transaction}'
 */
    const destroyForm = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TransactionController::destroy
 * @see app/Http/Controllers/TransactionController.php:122
 * @route '/api/transactions/{transaction}'
 */
        destroyForm.delete = (args: { transaction: string | number | { id: string | number } } | [transaction: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const transactions = {
    store: Object.assign(store, store),
pending: Object.assign(pending, pending),
approve: Object.assign(approve, approve),
reject: Object.assign(reject, reject),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default transactions