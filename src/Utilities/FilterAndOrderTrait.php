<?php

namespace LiviuVoica\LbCms\Utilities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

trait FilterAndOrderTrait
{
    /**
     * Applies filtering, searching, and ordering conditions to a query builder instance.
     *
     * @template TModelClass of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModelClass>  $query  The query builder instance to modify.
     * @param  array<string, string|null>  $params  An associative array of filtering, searching, and ordering parameters.
     * @return Builder<TModelClass> The modified query builder instance.
     */
    public function handleFilterAndOrder(Builder $query, array $params): Builder
    {
        $params = array_filter(
            $params,
            fn ($value) => $value !== null && $value !== ''
        );

        if (empty($params)) {
            Log::info('The current method is expecting a list containing filter, search and order conditions, but none were provided!', [
                'location' => __METHOD__,
                'query' => $query,
                'params' => $params,
            ]);
        }

        $className = get_class($query->getModel());

        foreach ($params as $key => $value) {
            // Cases for order ascendent or descendant
            if (in_array(strtolower($value), ['asc', 'desc'], true)) {
                $method = 'scopeOrderBy'.ucwords(str_replace('_', '', $key));
                if (! method_exists($query->getModel(), $method)) {
                    Log::warning('The target ordering method does not exist in the target class. Please make sure that the method is defined!', [
                        'location' => __METHOD__,
                        'query' => $query,
                        'params' => $params,
                        'target_class' => $className,
                        'target_method' => $method,
                    ]);

                    continue;
                }
                $method = lcfirst(substr($method, 5));
                $query->{$method}($value);

                continue;
            }

            // Cases for filtering
            $method = 'scopeSearchBy'.ucfirst(str_replace('_', '', ucwords($key, '_')));
            if (! method_exists($query->getModel(), $method)) {
                Log::warning('The target filtering method does not exist in the target class. Please make sure that the method is defined!', [
                    'location' => __METHOD__,
                    'query' => $query,
                    'params' => $params,
                    'target_class' => $className,
                    'target_method' => $method,
                ]);

                continue;
            }
            $method = lcfirst(substr($method, 5));
            $query->{$method}($value);
        }

        return $query;
    }
}
