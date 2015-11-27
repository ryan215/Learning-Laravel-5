<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Note that $hidden variable is not declared because of a dedicated Transformer that gets called by the controller.
 *
 * Class Resource
 * @package App
 */
class Resource extends Model
{

    /**
     * For now, distro names are used instead of displaying both the ->os and ->os_version parameters.
     *
     * @return string
     */
    public function getDistroAttribute()
    {
        return $this->os . "-" . $this->os_version;
    }

    /**
     * Allows to simplify queries when matching resources within the model.
     *
     * @param $query
     * @param $field
     * @param $value
     * @return mixed
     */
    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

}
