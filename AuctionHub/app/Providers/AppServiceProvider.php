use App\Services\PaymentProcessor;
use App\Services\StripePaymentProcessor;

public function register(): void
{
    $this->app->when(AuctionController::class)
        ->needs(PaymentProcessor::class)
        ->give(StripePaymentProcessor::class);
}
