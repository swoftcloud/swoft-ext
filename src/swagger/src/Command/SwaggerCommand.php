<?php declare(strict_types=1);


namespace Swoft\Swagger\Command;

use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Swagger\Exception\SwaggerException;
use Swoft\Swagger\Swagger;

/**
 * Class SwaggerCommand
 *
 * @since 2.0
 *
 * @Command(name="swagger", alias="swg")
 */
class SwaggerCommand
{
    /**
     * @Inject()
     *
     * @var Swagger
     */
    private $swagger;

    /**
     * @CommandOption(name="type", default="json")
     * @CommandOption(name="file", default="@resource/dist/doc//swagger.json")
     * @CommandMapping(name="gen", alias="g")
     *
     * @throws ReflectionException
     * @throws SwaggerException
     */
    public function gen(): void
    {
        $type = input()->getOption('type');
        $file = input()->getOption('file');

        $this->swagger->gen($type, $file);
    }
}