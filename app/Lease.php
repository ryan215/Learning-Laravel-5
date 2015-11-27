<?php
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;

/**
 * Class Lease
 * @package App
 */
class Lease extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uuid', 'user_id', 'resource_id', 'duration', 'expires_at'];

    /**
     * Modifies query to match only leases that belong to the given user.
     * Allows passing userId to simplify unit tests.
     *
     * @param $query
     * @param null $userId
     */
    public function scopeMine($query, $userId = null)
    {
        if (is_null($userId)) {
            $userId = Auth::User()->id;
        }

        $query->where('user_id', '=', $userId);
    }

    /**
     * Modifies query to match only leases that are still active.
     *
     * @param $query
     */
    public function scopeActive($query)
    {
        $query->where('expires_at', '>', Carbon::now());
    }

    /**
     * Combines scopeActive(), scopeMine() & matching a lease by uuid.
     *
     * @param $query
     * @param $leaseId
     */
    public function scopeAccessible($query, $leaseId)
    {
        $query->mine()->active()->where('uuid', '=', $leaseId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resource()
    {
        return $this->belongsTo('App\Resource');
    }

    /**
     * Quick way to set variables for a new lease.
     *
     * @param $resourceId
     * @param null $userId
     * @param null $duration
     * @return array
     */
    static public function stub($resourceId, $userId = null, $duration = null)
    {
        $duration = (is_null($duration)) ? 60 : $duration;

        $userId = (is_null($userId)) ? Auth::user()->id : $userId;
        $leaseParams = [
            'resource_id' => $resourceId,
            'user_id' => $userId,
            'duration' => $duration,
            'expires_at' => Carbon::create()->addMinutes($duration),
        ];

        $leaseParams['uuid'] = Uuid::uuid5(Uuid::NAMESPACE_DNS, $leaseParams['resource_id'] . $leaseParams['user_id']);

        return $leaseParams;
    }
}
