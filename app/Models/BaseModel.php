<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotFoundException;
use Carbon\Carbon;
use Cache;

/**
 * @mixin Builder;
 * @mixin QueryBuilder;
 */
abstract class BaseModel extends Model
{
    use HasFactory;

    const CACHE_TTL = 86400; //1w
    const CACHE_SHORT_TTL = 3600; //1h

    const CACHED_NULL_VALUE = '#NULL#';

    const CONNECTION = 'mysql';
    const TABLE = 'devices';

    const COLUMN_ID = 'id';
    const COLUMN_CREATED_AT = 'created_at';
    const COLUMN_UPDATED_AT = 'updated_at';

    /**
     * @param string $clientToken
     * @param bool $throwException
     * @return          $this|null
     * @throws NotFoundException
     */
    public static function getByClientToken(string $clientToken, bool $throwException = true)
    {
        $cacheKey = static::getCacheKey(
            [
                'token',
                $clientToken
            ]
        );

        $result = Cache::remember($cacheKey, self::CACHE_SHORT_TTL, function () use ($clientToken) {

            $device = static::getByClientTokenFromDB($clientToken);

            if ($device === null) {
                return self::CACHED_NULL_VALUE;
            }

            return $device;

        });

        if ($result === self::CACHED_NULL_VALUE) {

            if ($throwException) {
                throw new NotFoundException($clientToken);

            } else {
                return null;

            }

        }

        return $result;
    }

    public static function getCacheKey(array $sections)
    {
        array_unshift($sections, static::getCacheKeyPrefix());
        return implode(":", $sections);
    }

    /**
     * @param string $clientToken
     * @return $this|null
     */
    protected abstract static function getByClientTokenFromDB(string $clientToken);

    protected static function boot()
    {
        parent::boot();

        static::creating(function (BaseModel $baseModel) {
            return $baseModel->onCreating($baseModel);
        });

        static::updating(function (BaseModel $baseModel) {
            return $baseModel->onUpdating($baseModel);
        });

        static::saved(function (BaseModel $baseModel) {
            return $baseModel->onSaved($baseModel);
        });

        static::deleted(function (BaseModel $baseModel) {
            return $baseModel->onDeleted($baseModel);
        });
    }

    protected function onCreating(BaseModel $baseModel)
    {
        $baseModel->purgeRelatedCaches();
        return $baseModel;
    }

    protected function onUpdating(BaseModel $baseModel)
    {
        $baseModel->purgeRelatedCaches();
        return $baseModel;
    }

    protected function onSaved(BaseModel $baseModel)
    {
        $baseModel->purgeRelatedCaches();
    }

    protected function onDeleted(BaseModel $baseModel)
    {
        $baseModel->purgeRelatedCaches();
    }

    private function purgeRelatedCaches()
    {
        $relatedCacheKeys = $this->getRelatedCacheKeys();

        foreach ($relatedCacheKeys as $index => $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    protected function getRelatedCacheKeys(): array
    {
        return [];
    }

    private static function getEntityItemCacheKey(int $id = 0): string
    {
        return static::getCacheKeyPrefix() . ':' . $id;
    }

    public static function getCacheKeyPrefix(): string
    {
        return static::CONNECTION . ':' . static::TABLE;
    }

    public function getConnectionName()
    {
        return static::CONNECTION;
    }

    public function getTable()
    {
        return static::TABLE;
    }

    public function getId(): int
    {
        return $this->{self::COLUMN_ID};
    }

    public function setId(int $value): self
    {
        $this->{self::COLUMN_ID} = $value;

        return $this;
    }

    public function hasId(): bool
    {
        return $this->{self::COLUMN_ID} !== null;
    }

    public function getCreatedAt(): ?Carbon
    {
        if ($this->{self::COLUMN_CREATED_AT} === null) {
            return null;
        }

        return Carbon::parse($this->{self::COLUMN_CREATED_AT});
    }

    public function getUpdatedAt(): ?Carbon
    {
        if ($this->{self::COLUMN_UPDATED_AT} === null) {
            return null;
        }

        return Carbon::parse($this->{self::COLUMN_UPDATED_AT});
    }
}