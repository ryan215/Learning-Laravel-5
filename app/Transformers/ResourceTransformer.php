<?php
namespace App\Transformers;

class ResourceTransformer
{
    /**
     * Transform resources into distros.
     *
     * @param $resources
     * @return array|static
     */
    public function getDistros($resources)
    {
        $platforms = [];
        foreach ($resources as $resource) {
            $platforms[$resource->os][$resource->os_version] = [
                'distro' => $resource->distro,
                'links' => ['lease' => '/resources/' . $resource->distro . '/lease'],
            ];
        }

        foreach ($platforms as $os => $versions) {
            $platforms[$os] = array_values($versions);
        }

        return $platforms;

    }
}

