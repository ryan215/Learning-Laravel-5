<?php
namespace App\Transformers;

use App\Lease;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class LeaseTransformer extends TransformerAbstract
{
    /**
     * Transforms lease object for API output.
     *
     * @param Lease $lease
     * @return array
     */
    public function transform(Lease $lease)
    {
        return [
            'id' => $lease->uuid,
            'distro' => $lease->resource->distro,
            'local_ip' => $lease->resource->local_ip,
            'credentials' => 'All leased resources are sharing your user credentials - use the same username & password',
            'expires_at' => Carbon::parse($lease->expires_at),
            'links' => [
                ['rel' => 'self', 'uri' => '/leases/' . $lease->uuid],
                ['rel' => 'terminate', 'uri' => '/leases/' . $lease->uuid . '/terminate'],
            ],
        ];
    }
}