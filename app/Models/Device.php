<?php

namespace App\Models;

use App\Exceptions\DeviceExistsException;
use App\Exceptions\RateLimitException;
use App\Services\AppleStoreService;
use App\Services\GooglePlayService;
use App\Services\IStoreService;
use Illuminate\Support\Str;

class Device extends BaseModel
{
    const TABLE = 'devices';

    const COLUMN_CLIENT_TOKEN = 'client_token';
    const COLUMN_U_ID = 'u_id';
    const COLUMN_APP_ID = 'app_id';
    const COLUMN_LANG = 'lang';
    const COLUMN_OS = 'os';

    const OS_IOS = 'ios';
    const OS_ANDROID = 'android';
    const OSES = [
        self::OS_IOS => self::OS_IOS,
        self::OS_ANDROID => self::OS_ANDROID,
    ];

    public function getClientToken(): string
    {
        return $this->{self::COLUMN_CLIENT_TOKEN};
    }
    public function setClientToken(string $value): self
    {
        $this->{self::COLUMN_CLIENT_TOKEN} = $value;
        return $this;
    }

    public function getUId(): int
    {
        return $this->{self::COLUMN_U_ID};
    }
    public function setUId(int $value): self
    {
        $this->{self::COLUMN_U_ID} = $value;
        return $this;
    }

    public function getAppId(): int
    {
        return $this->{self::COLUMN_APP_ID};
    }
    public function setAppId(int $value): self
    {
        $this->{self::COLUMN_APP_ID} = $value;
        return $this;
    }

    public function getLanguage(): string
    {
        return $this->{self::COLUMN_LANG};
    }
    public function setLanguage(string $value): self
    {
        $this->{self::COLUMN_LANG} = $value;
        return $this;
    }

    public function getOS(): string
    {
        return $this->{self::COLUMN_OS};
    }
    public function setOS(string $value): self
    {
        $this->{self::COLUMN_OS} = $value;
        return $this;
    }

    protected function getRelatedCacheKeys(): array
    {
        return [
            self::getCacheKey(
                [
                    'token',
                    $this->getClientToken(),
                ]
            ),
        ];
    }

    protected static function getByClientTokenFromDB(string $clientToken): ?self
    {
        return (new Device())
            ->where(Device::COLUMN_CLIENT_TOKEN, '=', $clientToken)
            ->orderBy(Device::COLUMN_ID)
            ->first();
    }

    /**
     * @param int $uId
     * @param int $appId
     * @param string $lang
     * @param string $os
     * @return Device
     * @throws DeviceExistsException
     */
    public static function addNewDevice(int $uId, int $appId, string $lang, string $os): self
    {
        if (self::isDeviceExists($uId, $appId)) {
            throw new DeviceExistsException($uId, $appId);
        }

        $device = new Device();
        $device
            ->setClientToken(Str::uuid())
            ->setUId($uId)
            ->setAppId($appId)
            ->setLanguage($lang)
            ->setOS($os)
            ->save();

        return $device;
    }

    /**
     * @param int $uId
     * @param int $appId
     * @return bool
     */
    private static function isDeviceExists(int $uId, int $appId): bool
    {
        return (new Device())
            ->where(Device::COLUMN_U_ID, '=', $uId)
            ->where(Device::COLUMN_APP_ID, '=', $appId)
            ->exists();
    }


    
}
