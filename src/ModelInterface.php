<?php
/**
 * Created for plugin-component-db
 * Date: 16.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Components\Db;


interface ModelInterface
{

    public function save(): void;

    public function delete(): void;

    public static function findById(string $id): ?self;

    public static function findByIds(array $ids): array;

    public static function findByCondition(array $where): array;

    public static function tableName(): string;

    /**
     * @link https://medoo.in/api/create
     * @return array[]
     */
    public static function schema(): array;

}