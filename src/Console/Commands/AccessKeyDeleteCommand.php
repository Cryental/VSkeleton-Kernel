<?php

namespace Volistx\FrameworkKernel\Console\Commands;

use Illuminate\Console\Command;
use Volistx\FrameworkKernel\Repositories\AccessTokenRepository;
use function Laravel\Prompts\text;

class AccessKeyDeleteCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'access-key:delete {--key=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete an access key';
    private AccessTokenRepository $accessTokenRepository;

    /**
     * Create a new AccessKeyDeleteCommand instance.
     *
     * @param AccessTokenRepository $accessTokenRepository The access token repository.
     */
    public function __construct(AccessTokenRepository $accessTokenRepository)
    {
        parent::__construct();
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * Handle the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $token = text(
            label: 'Please specify your access key to delete.',
            default: '',
            required: true
        );

        if (empty($token)) {
            $this->components->error('Please specify your access key to delete.');

            return;
        }

        // Find the access token
        $accessToken = $this->accessTokenRepository->AuthAccessToken($token);

        // Check if the access token exists
        if (!$accessToken) {
            $this->error('The specified access key is invalid.');

            return;
        }

        // Delete the access token
        $this->accessTokenRepository->Delete($accessToken->id);

        $this->info('Your access key is deleted: ' . $token);
    }
}
