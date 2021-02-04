<?php

namespace App\Console\Commands;

use App\Jobs\RenewSubscriptionJob;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;
use Throwable;

class WorkerCommand extends Command
{
    private const PAGE_COUNT = 1000;

    protected $signature = 'worker:run';
    protected $description = 'Worker is responsible to recheck verification of the records which are expired but are not canceled';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function handle()
    {
        $offset = 0;
        do {
            $subscriptions = $this->getExpiredSubscriptions($offset);

            if ($subscriptions->count() > 0) {

                /** @var Subscription $subscription */
                foreach ($subscriptions as $subscription) {
                    RenewSubscriptionJob::dispatch($subscription);
                    $this->info($subscription->getClientToken().' has been dispatched!');

                }

            }

            $offset += $subscriptions->count();

        } while ($subscriptions->count() > 0);

        return true;
    }

    private function getExpiredSubscriptions(int $offset = 0): Collection
    {
        return (new Subscription())
            ->whereIn(Subscription::COLUMN_STATUS, [Subscription::STATUS_NEW, Subscription::STATUS_RENEWED])
            ->where(Subscription::COLUMN_EXPIRE_AT, '<=', Carbon::now())
            ->skip($offset)
            ->take(self::PAGE_COUNT)
            ->get();
    }
}
