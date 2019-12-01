<?php declare(strict_types=1);


namespace Swoft\Swagger\Node;

use Swoft\Stdlib\Helper\JsonHelper;

/**
 * Class OpenAPI
 *
 * @since 2.0
 */
class OpenApi extends Node
{
    /**
     * @var string
     */
    protected $openapi = '3.0.0';

    /**
     * @var Info
     */
    protected $info;

    /**
     * @var Server[]
     */
    protected $servers = [];

    /**
     * @var array
     */
    protected $paths;

    /**
     * @var Components
     */
    protected $components;

    /**
     * @param string $openapi
     */
    public function setOpenapi(string $openapi): void
    {
        $this->openapi = $openapi;
    }

    /**
     * @param Info $info
     */
    public function setInfo(Info $info): void
    {
        $this->info = $info;
    }

    /**
     * @return Server[]
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * @param Server[] $servers
     */
    public function setServers(array $servers): void
    {
        $this->servers = $servers;
    }

    /**
     * @param array $paths
     */
    public function setPaths(array $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @param int $flags
     *
     * @return string
     */
    public function toJson(int $flags = null): string
    {
        if ($flags === null) {
            $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        return JsonHelper::encode($this, $flags);
    }

    /**
     * @param Components $components
     */
    public function setComponents(Components $components): void
    {
        $this->components = $components;
    }
}