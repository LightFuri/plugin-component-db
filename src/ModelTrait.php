<?php
/**
 * Created for plugin-component-db
 * Datetime: 05.02.2020 16:23
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Components\Db;


use InvalidArgumentException;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Medoo\Medoo;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

trait ModelTrait
{

    protected string $id;

    private array $_data = [];

    private bool $_isNew = true;

    private static array $_loaded = [];

    public function getId(): string
    {
        return $this->id;
    }

    public function __get($name)
    {
        return $this->_data[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function save(): void
    {
        $db = static::db();
        $data = static::serialize($this);

        $this->beforeSave($this->_isNew);
        if ($this->_isNew) {
            $db->insert(static::tableName(), $data);
            $this->_isNew = false;
        } else {
            $where = [
                'id' => $this->id
            ];

            unset($data['id']);

            if ($this instanceof PluginModelInterface) {
                $where['companyId'] = Connector::getReference()->getCompanyId();
                $where['pluginAlias'] = Connector::getReference()->getAlias();
                $where['pluginId'] = Connector::getReference()->getId();

                unset($data['companyId']);
                unset($data['pluginAlias']);
                unset($data['pluginId']);
            }

            $db->update(static::tableName(), $data, $where);
        }
    }

    public function delete(): void
    {
        $where = [
            'id' => $this->id
        ];

        if ($this instanceof PluginModelInterface) {
            $where['companyId'] = Connector::getReference()->getCompanyId();
            $where['pluginAlias'] = Connector::getReference()->getAlias();
            $where['pluginId'] = Connector::getReference()->getId();
        }

        static::db()->delete(static::tableName(), $where);
    }

    protected function beforeSave(bool $isNew): void
    {
    }

    protected function afterFind(): void
    {
    }

    public static function findById(string $id): ?self
    {
        $models = static::findByCondition([
            'id' => $id,
            'LIMIT' => 1,
        ]);

        if (empty($models)) {
            return null;
        }

        return static::deserialize($models[$id]);
    }

    public static function findByIds(array $ids): array
    {
        return static::findByCondition([
            'id' => $ids,
            'LIMIT' => 1,
        ]);
    }

    /**
     * @link https://medoo.in/api/where
     * @param array $where
     * @return array
     * @throws ReflectionException
     */
    public static function findByCondition(array $where): array
    {
        if (is_a(static::class, PluginModelInterface::class, true)) {
            $where['companyId'] = Connector::getReference()->getCompanyId();
            $where['pluginAlias'] = Connector::getReference()->getAlias();
            $where['pluginId'] = Connector::getReference()->getId();
        }

        $data = static::db()->select(
            static::tableName(),
            ['id' => '*'],
            $where
        );

        return array_map(function (array $data) {
            $model = static::deserialize($data);
            $model->_isNew = false;
            $model->afterFind();
            return $model;
        }, $data);
    }

    public static function tableName(): string
    {
        $parts = explode('\\', static::class);
        return end($parts);
    }

    /**
     * Attention!
     * - DO NOT USE `AUTO_INCREMENT`, instead please use UUID `Ramsey\Uuid\Uuid::uuid4()->toString()` for model id
     * - DO NOT USE `PRIMARY KEY` in schema description. It will be generated automatically by `id` or
     *   `companyId` + `pluginAlias` + `pluginId` + `id`
     * - DO NOT USE fields `id`, `companyId`, `pluginAlias` and `pluginId` in schema. It will be generated automatically.
     *
     * @link https://medoo.in/api/create
     * @return array[]
     */
    abstract public static function schema(): array;

    /**
     * @param ModelInterface|PluginModelInterface|SinglePluginModelInterface|ModelTrait $model
     * @return array
     */
    protected static function serialize(self $model): array
    {
        $fields = array_keys(
            array_filter(static::schema(), fn($value) => is_array($value))
        );

        $data = [];
        foreach ($fields as $field) {
            $value = $model->{$field};

            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (!is_null($value) && !is_scalar($value)) {
                throw new InvalidArgumentException("Field '{$field}' of '" . get_class($model) . "' should be scalar or null");
            }

            $data[$field] = $value;
        }

        if (is_a(static::class, PluginModelInterface::class, true)) {
            $data['companyId'] = Connector::getReference()->getCompanyId();
            $data['pluginAlias'] = Connector::getReference()->getAlias();
            $data['pluginId'] = Connector::getReference()->getId();
        }

        if (is_a(static::class, SinglePluginModelInterface::class, true)) {
            $data['id'] = $data['pluginId'];
        }

        return $data;
    }

    /**
     * @param array $data
     * @return static
     * @throws ReflectionException
     */
    protected static function deserialize(array $data): self
    {
        $hashParts = ['id'];
        if (is_a(static::class, PluginModelInterface::class, true)) {
            $hashParts[] = 'companyId';
            $hashParts[] = 'pluginAlias';
            $hashParts[] = 'pluginId';
        }

        $hash = md5(implode('--', $hashParts));
        if (isset(static::$_loaded[$hash])) {
            return static::$_loaded[$hash];
        }

        $fields = array_keys(
            array_filter(static::schema(), fn($value) => is_array($value))
        );

        $class = static::class;
        $reflection = new ReflectionClass($class);

        /** @var ModelInterface|PluginModelInterface|SinglePluginModelInterface|ModelTrait $model */
        $model = $reflection->newInstanceWithoutConstructor();

        foreach ($fields as $field) {
            $model->{$field} = $data[$field];
        }

        static::$_loaded[$hash] = $model;
        return $model;
    }

    protected static function db(): Medoo
    {
        if (is_null(Connector::getReference())) {
            throw new RuntimeException('No company ID', 1001);
        }

        return Connector::db();
    }

}