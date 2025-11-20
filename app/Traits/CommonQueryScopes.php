<?php


namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
  /**
   * Filter by exact date or date range
   */
  public function scopeFilterByDate(Builder $query, $from = null, $to = null)
  {
    if ($from && $to) {
      return $query->whereBetween('date', [$from, $to]);
    }

    if ($from) {
      return $query->whereDate('date', '>=', $from);
    }

    if ($to) {
      return $query->whereDate('date', '<=', $to);
    }

    return $query;
  }

  /**
   * Search by title
   */
  public function scopeSearchByTitle(Builder $query, $term)
  {
    if (!$term) return $query;

    return $query->where('title', 'LIKE', "%{$term}%");
  }

  /**
   * Optional: combine search on title + description
   */
  public function scopeSearch(Builder $query, $term)
  {
    if (!$term) return $query;

    return $query->where(function ($q) use ($term) {
      $q->where('title', 'LIKE', "%{$term}%")
        ->orWhere('description', 'LIKE', "%{$term}%");
    });
  }
}
