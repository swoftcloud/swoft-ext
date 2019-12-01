<?php declare(strict_types=1);


namespace SwoftTest\Swagger\Unit;


use PHPUnit\Framework\TestCase;
use ReflectionException;
use Swoft\Bean\BF;
use Swoft\Db\Exception\DbException;
use Swoft\Swagger\Dto;
use Swoft\Swagger\Exception\DtoException;
use Swoft\Swagger\Exception\SwaggerException;
use Swoft\Swagger\Swagger;
use SwoftTest\Swagger\Testing\Entity\User;
use SwoftTest\Swagger\Testing\Schema\IndexData;
use SwoftTest\Swagger\Testing\Schema\IndexOther;
use SwoftTest\Swagger\Testing\Schema\IndexResponseSchema;

/**
 * Class SwgTest
 *
 * @since 2.0
 */
class SwgTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws SwaggerException
     */
    public function testGen(): void
    {
        // Tmp file
        $tmp = 't.json';

        /* @var Swagger $swagger */
        $swagger = BF::getBean(Swagger::class);
        $swagger->gen(Swagger::JSON, $tmp);

        $content = file_get_contents($tmp);

        unlink($tmp);
        $this->assertEquals('8739038ecfbc553abd6b71bc7e3079ee', md5($content));
    }

    /**
     * @throws ReflectionException
     * @throws SwaggerException
     */
    public function testToJson(): void
    {
        /* @var Swagger $swagger */
        $swagger = BF::getBean(Swagger::class);
        $json    = $swagger->toJson();
        $this->assertEquals('8739038ecfbc553abd6b71bc7e3079ee', md5($json));
    }

    /**
     * @throws ReflectionException
     * @throws SwaggerException
     */
    public function testToYaml(): void
    {
        /* @var Swagger $swagger */
        $swagger = BF::getBean(Swagger::class);
        $yaml    = $swagger->toYaml();
        $this->assertEquals('263e64fcea0df78332b2bb50b9caf0ae', md5($yaml));
    }

    /**
     * @throws DbException
     * @throws DtoException
     */
    public function testDto(): void
    {
        $user = new User();
        $user->setId(12);
        $user->setName('name');
        $user->setPwd('PWD');
        $user->setAge(12);
        $user->setUserDesc('use desc');

        $other = IndexOther::new([
            'id'    => 12,
            'count' => 100,
            'desc'  => 'desc'
        ]);

        $data = IndexData::new([
            'user'  => $user,
            'list'  => [
                $user
            ],
            'other' => $other
        ]);

        $response = IndexResponseSchema::new([
            'data' => $data
        ]);

        /* @var Dto $dto */
        $dto    = bean(Dto::class);
        $result = $dto->encode($response);

        $this->assertEquals('c3db4a1b2eec80277d4c2f5eeb3527a5', md5($result));
    }
}