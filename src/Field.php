<?php

namespace Netflex\Structure;

use Carbon\Carbon;
use Netflex\Support\Accessors;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read string $alias
 * @property-read string $type
 * @property-read string $content_field
 * @property-read int $collection_id
 * @property-read string|null $code
 * @property-read int|null $sorting
 */
class Field implements CastsAttributes
{
  use Accessors;

  protected $attributes = [];
  protected $type = 'string';

  public function __construct($attributes = [])
  {
    if (is_string($attributes)) {
      $this->type = $attributes;
    }

    $this->attributes = is_array($attributes) ? $attributes : [];
  }

  public function getIdAttribute($id)
  {
    return (int) $id;
  }

  public function getCollectionIdAttribute($collectionId)
  {
    return (int) $collectionId;
  }

  public function getSortingAttribute($sorting)
  {
    return (int) $sorting;
  }

  public function get($model, $key, $value, $attributes)
  {
    switch ($this->type) {
      case 'checkbox':
        return boolval(intval($value));
      case 'integer':
      case 'entry':
      case 'customer':
        return intval($value);
      case 'float':
        return floatval($value);
      case 'tags':
        return array_values(array_filter(explode(',', $value)));
      case 'entries':
      case 'customers':
        return array_map('intval', array_values(array_filter(explode(',', $value))));
      case 'json':
        return new JSON(json_decode($value, true));
      case 'editor-blocks':
        return new EditorBlocks($value);
      case 'date':
        return $value ? Carbon::parse($value)->startOfDay() : null;
      case 'datetime':
        return $value ? Carbon::parse($value) : null;
      default:
        return $value;
    }
  }

  public function set($model, $key, $value, $attributes)
  {
    switch ($this->type) {
      case 'tags':
      case 'entries':
      case 'customers':
        $value = is_array($value) ? implode(',', $value) : $value;
        break;
      case 'json':
        $value = $value instanceof JSON ? $value->jsonSerialize() : $value;
        break;
      case 'editor-blocks':
        $value = $value instanceof EditorBlocks ? $value->jsonSerialize() : $value;
        break;
      case 'date':
        $value = $value instanceof Carbon ? $value->toDateString() : $value;
        break;
      case 'datetime':
        $value = $value instanceof Carbon ? $value->toDateTimeString() : $value;
        break;
      default:
        break;
    }
    return [$key => $value];
  }
}
