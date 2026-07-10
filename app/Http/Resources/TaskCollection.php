<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Wraps a paginated Task collection.
 *
 * Laravel's ResourceCollection automatically detects LengthAwarePaginator
 * and includes pagination metadata (current_page, last_page, total, links)
 * without any manual configuration.
 */
final class TaskCollection extends ResourceCollection
{
    /**
     * The resource class used to transform each item.
     */
    public $collects = TaskResource::class;
}
