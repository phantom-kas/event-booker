<?php

namespace App\Traits;

trait Filterable
{
    /**
     * Filter by exact date or date range
     */
    

    /**
     * Search in title + description
     */
   

    /**
     * Filter by location (partial match)
     */
    public function scopeFilterByLocation($query, $location)
    {
        return $location ? $query->where('location', 'LIKE', "%{$location}%") : $query;
    }

    /**
     * Only upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->startOfDay());
    }
}