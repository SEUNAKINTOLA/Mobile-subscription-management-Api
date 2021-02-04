<?php

namespace App\Jobs;

use App\Exceptions\RateLimitException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exceptions\NotFoundException;
use Illuminate\Bus\Queueable;
use App\Models\Subscription;
use Throwable;
use Log;
use DB;

class RenewSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Subscription */
    protected $subscription;
    public $tries = 5;
    public $maxExceptions = 4;
    public $timeout = 60;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;

        $this->onQueue('default');
    }

    /**
     * @throws Throwable
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $this->subscription->renew();

            DB::commit();
        } catch (RateLimitException $exception) {
            Log::error($exception);
            $this->release(20);

        } catch (NotFoundException $exception) {
            Log::error($exception);

        } catch (Throwable $exception) {
            Log::error($exception);

        } finally {

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

        }
    }
}
