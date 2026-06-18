protected function schedule(Schedule $schedule): void
{
    $schedule->command('auctions:end')->everyMinute();
}
