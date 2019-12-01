<?php declare(strict_types=1);


namespace Swoft\Swagger\Dto;


use ReflectionException;
use ReflectionProperty;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Db\EntityRegister;
use Swoft\Stdlib\Helper\ObjectHelper;
use Swoft\Stdlib\Helper\PhpHelper;
use Swoft\Swagger\Annotation\Mapping\ApiProperty;
use Swoft\Swagger\Annotation\Mapping\ApiPropertyEntity;
use Swoft\Swagger\Annotation\Mapping\ApiPropertySchema;
use Swoft\Swagger\ApiRegister;
use Swoft\Swagger\Contract\DtoInterface;
use Swoft\Swagger\Exception\DtoException;

/**
 * Class JsonDto
 *
 * @since 2.0
 *
 * @Bean()
 */
class JsonDto implements DtoInterface
{
    /**
     * @param object $object
     *
     * @return string
     * @throws DtoException
     * @throws ReflectionException
     */
    public function encode($object): string
    {
        $data = $this->schemaObjectToArray($object);
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param       $object
     *
     * @param array $fields
     * @param array $unfields
     *
     * @return array
     * @throws DtoException
     * @throws ReflectionException
     */
    private function schemaObjectToArray($object, array $fields = [], array $unfields = []): array
    {
        $data        = [];
        $className   = get_class($object);
        $schemaProps = ApiRegister::getProperties($className);

        foreach ($object as $propName => $propValue) {
            if (!isset($schemaProps[$propName])) {
                continue;
            }

            $schemaAnnotation = $schemaProps[$propName];


            $reflectProperty = new ReflectionProperty($className, $propName);
            $propType        = ObjectHelper::getPropertyBaseType($reflectProperty, true);
            $propValue       = $this->getValueByGetter($object, $propName, $propValue);

            // ApiProperty
            if ($schemaAnnotation instanceof ApiProperty) {
                $name = $schemaAnnotation->getName();
                if (!$this->isFields($name, $fields, $unfields)) {
                    continue;
                }

                $data[$propName] = $this->transferPropertyType($propType, $propValue);
                continue;
            }

            // ApiPropertySchema
            if ($schemaAnnotation instanceof ApiPropertySchema) {
                $name = $schemaAnnotation->getName();
                if (!$this->isFields($name, $fields, $unfields)) {
                    continue;
                }

                $aFields   = $schemaAnnotation->getFields();
                $aUnfields = $schemaAnnotation->getUnfields();

                $data[$name] = $this->schemaObjectToArray($propValue, $aFields, $aUnfields);
                continue;
            }

            // ApiPropertyEntity
            if ($schemaAnnotation instanceof ApiPropertyEntity) {
                $name = $schemaAnnotation->getName();
                if (!$this->isFields($name, $fields, $unfields)) {
                    continue;
                }

                $aFields   = $schemaAnnotation->getFields();
                $aUnfields = $schemaAnnotation->getUnfields();

                // Array
                if ($propType == 'array') {
                    $entityObjects = [];
                    foreach ($propValue as $propObj) {
                        $entityObjects[] = $this->entityObjectToArray($propObj, $aFields, $aUnfields);
                    }
                    $data[$name] = $entityObjects;
                    continue;
                }

                // Not object instance
                if (!empty($propType) && !($propValue instanceof $propType)) {
                    throw new DtoException(
                        sprintf('%s property value is not instanceof %s', $propName, $propType)
                    );
                }

                $data[$name] = $this->entityObjectToArray($propValue, $aFields, $aUnfields);
            }
        }

        return $data;
    }

    private function entityObjectToArray($object, array $fields, array $unfields): array
    {
        $entityArray = [];
        $className   = get_class($object);
        $mapping     = EntityRegister::getMapping($className);
        foreach ($mapping as $attrName => $prop) {
            $propMapName = $prop['pro'];
            $propType    = $prop['type'];

            $notFields  = !empty($fields) && !in_array($propMapName, $fields);
            $isUnfields = !empty($unfields) && in_array($propMapName, $unfields);
            if ($notFields || $isUnfields) {
                continue;
            }

            $propVaue = $this->getValueByGetter($object, $attrName, null);
            $propVaue = $this->transferPropertyType($propType, $propVaue);

            $entityArray[$propMapName] = $propVaue;
        }

        return $entityArray;
    }


    /**
     * @param string $propName
     * @param array  $fields
     * @param array  $unfields
     *
     * @return bool
     */
    private function isFields(string $propName, array $fields, array $unfields): bool
    {
        $notFields  = !empty($fields) && !in_array($propName, $fields);
        $isUnfields = !empty($unfields) && in_array($propName, $unfields);
        if ($notFields || $isUnfields) {
            return false;
        }

        return true;
    }

    /**
     * @param object $object
     * @param string $propName
     * @param mixed  $propValue
     *
     * @return mixed
     */
    private function getValueByGetter($object, string $propName, $propValue)
    {
        $getterMethod = sprintf('get%s', ucfirst($propName));
        if (!method_exists($object, $getterMethod)) {
            return $propValue;
        }

        return PhpHelper::call([$object, $getterMethod]);
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return array|bool|float|int|string
     */
    private function transferPropertyType(string $type, $value)
    {
        switch ($type) {
            // array
            case 'array':
                $value = (array)$value;
                break;

            // bool
            case 'bool':
            case 'boolean':
                $value = (bool)$value;
                break;

            // int
            case 'int':
            case 'integer':
                $value = (int)$value;
                break;

            // double
            case 'double':
            case 'float':
                $value = (float)$value;
                break;

            // string
            case 'string':
                $value = (string)$value;
                break;
        }

        return $value;
    }
}