<?php

namespace App\Models;

use App\Events\CanceledSubscriptionEvent;
use App\Events\RenewedSubscriptionEvent;
use App\Events\StartedSubscriptionEvent;
use App\Exceptions\RateLimitException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\NotFoundException;
use Carbon\Carbon;

class Subscription extends BaseModel
{
    use HasFactory;

    const TABLE = 'subscriptions';

    const COLUMN_CLIENT_TOKEN = 'client_token';
    const COLUMN_RECEIPT = 'receipt';
    const COLUMN_STATUS = 'status';
    const COLUMN_EXPIRE_AT = 'expire_at';

    const STATUS_NEW = 'new';
    const STATUS_RENEWED = 'renewed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELED = 'canceled';
    const STATUES = [
        self::STATUS_NEW => self::STATUS_NEW,
        self::STATUS_RENEWED => self::STATUS_RENEWED,
        self::STATUS_EXPIRED => self::STATUS_EXPIRED,
        self::STATUS_CANCELED => self::STATUS_CANCELED,
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

    /**
     * @return Device
     * @throws NotFoundException
     */
    public function getDevice(): Device
    {
        return Device::getByClientToken($this->getClientToken());
    }

    public function getReceipt(): string
    {
        return $this->{self::COLUMN_RECEIPT};
    }
    public function setReceipt(string $value): self
    {
        $this->{self::COLUMN_RECEIPT} = $value;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->{self::COLUMN_STATUS};
    }
    public function setStatus(string $value): self
    {
        $this->{self::COLUMN_STATUS} = $value;
        return $this;
    }

    public function getExpireAt(): Carbon
    {
        return Carbon::parse($this->{self::COLUMN_EXPIRE_AT});
    }
    public function setExpireAt(Carbon $value): self
    {
        $this->{self::COLUMN_EXPIRE_AT} = $value;
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
        return (new Subscription())
            ->where(Subscription::COLUMN_CLIENT_TOKEN, '=', $clientToken)
            ->orderBy(Subscription::COLUMN_ID)
            ->first();
    }


    

    /**
     * @throws NotFoundException|RateLimitException
     */
    public function renew()
    {
        $device = $this->getDevice();
        $result = $device->verify($this->getReceipt());

        $isSucceed  = $result['result'];
        $expiration = null;

        if ($isSucceed) {
            $expiration = $result['expiration'];
            $status     = self::STATUS_RENEWED;

        } else {
            $expiration = $this->getExpireAt();
            $status     = self::STATUS_EXPIRED;

        }

        $this
            ->setStatus($status)
            ->setExpireAt($expiration)
            ->save();
    }

    /**
     * @param string $clientToken
     * @param string $receipt
     * @param Carbon $expireAt
     * @return Subscription
     * @throws NotFoundException
     */
    public static function registerSubscription(string $clientToken, string $receipt, Carbon $expireAt): Subscription
    {
        $subscription = Subscription::getByClientToken($clientToken, false);

        if ($subscription === null) {
            $subscription = new Subscription();
            $subscription = $subscription
                ->setClientToken($clientToken)
                ->setReceipt($receipt)
                ->setStatus(self::STATUS_NEW)
                ->setExpireAt($expireAt);

        } else {
            $subscription = $subscription
                ->setStatus(self::STATUS_RENEWED)
                ->setExpireAt($expireAt);

        }

        $subscription
            ->save();

        return $subscription;
    }

    protected function onCreating(BaseModel $baseModel)
    {
        $baseModel = parent::onCreating($baseModel);
        StartedSubscriptionEvent::dispatch($this);

        return $baseModel;
    }

    protected function onUpdating(BaseModel $baseModel)
    {
        $baseModel = parent::onUpdating($baseModel);

        switch ($this->getStatus()) {
            case self::STATUS_RENEWED:
                RenewedSubscriptionEvent::dispatch($this);
                break;

            case self::STATUS_EXPIRED:
            case self::STATUS_CANCELED:
                CanceledSubscriptionEvent::dispatch($this);
                break;
        }

        return $baseModel;
    }
}
