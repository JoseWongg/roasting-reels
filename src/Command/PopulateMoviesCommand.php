<?php

namespace App\Command;

use App\Services\TmdbService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * This class is used to create the command to populate movies from TMDB API into the database.
 * To execute the command, run the following command in the terminal: php bin/console app:populate-movies
 * This executes the MovieFixtures class.
 * This command does not take any arguments. It can be extended to take arguments (e.g. page number to fetch or use a loop to fetch several pages)
 */
#[AsCommand(
    name: 'app:populate-movies',
    description: 'Fetches movies from TMDB and populates them into the database',
)]
class PopulateMoviesCommand extends Command
{
    private TmdbService $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        parent::__construct();
        $this->tmdbService = $tmdbService;
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command allows you to fetch movies from TMDB and populate them into the database')
            ->setDescription('A command to populate movies from TMDB API into the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Populating Movies from TMDB');

        try {
            $this->tmdbService->fetchAndStoreMovies();
            $io->success('Movies have been successfully fetched and stored.');
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
