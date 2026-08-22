<?php
namespace Elsnertech\SocialFeeds\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\CacheInterface;

class ClearCache extends Command
{
    const COMMAND_NAME = 'socialfeeds:cache:clear';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param CacheInterface $cache
     */
    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Clear Instagram Feeds cache');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Clearing Instagram Feeds cache...</info>');
        
        // Clear Instagram cache
        $instagramCacheCleared = 0;
        for ($i = 0; $i < 100; $i++) {
            $cacheKey = 'social_feeds_instagram_' . $i;
            if ($this->cache->remove($cacheKey)) {
                $instagramCacheCleared++;
            }
        }
        
        $output->writeln('<info>Instagram cache entries cleared: ' . $instagramCacheCleared . '</info>');
        $output->writeln('<info>Instagram Feeds cache cleared successfully!</info>');
        
        return 0;
    }
}