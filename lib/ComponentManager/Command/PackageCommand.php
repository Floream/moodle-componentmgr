<?php

/**
 * Moodle component manager.
 *
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2016 Luke Carrier
 * @license GPL-3.0+
 */

namespace ComponentManager\Command;

use ComponentManager\Command\AbstractCommand;
use ComponentManager\Console\Argument;
use ComponentManager\HttpClient;
use ComponentManager\Moodle;
use ComponentManager\MoodleApi;
use ComponentManager\PackageFormat\PackageFormatFactory;
use ComponentManager\PackageRepository\PackageRepositoryFactory;
use ComponentManager\PackageSource\PackageSourceFactory;
use ComponentManager\Platform\Platform;
use ComponentManager\Task\PackageTask;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Package command.
 *
 * Assembles an entire Moodle instance from the specified project file, then
 * packages it in the specified format.
 */
class PackageCommand extends ProjectAwareCommand {
    /**
     * Help.
     *
     * @var string
     */
    const HELP = <<<HELP
Packages a Moodle site from a project file.
HELP;

    /**
     * Moodle.org plugin and update API.
     *
     * @var MoodleApi
     */
    protected $moodleApi;

    /**
     * HTTP client.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Initialiser.
     *
     * @param PackageRepositoryFactory $packageRepositoryFactory
     * @param PackageSourceFactory     $packageSourceFactory
     * @param PackageFormatFactory     $packageFormatFactory
     * @param MoodleApi                $moodleApi
     * @param Filesystem               $filesystem
     * @param HttpClient               $httpClient
     * @param Platform                 $platform
     * @param LoggerInterface          $logger
     */
    public function __construct(PackageRepositoryFactory $packageRepositoryFactory,
                                PackageSourceFactory $packageSourceFactory,
                                PackageFormatFactory $packageFormatFactory,
                                MoodleApi $moodleApi, Filesystem $filesystem,
                                HttpClient $httpClient, Platform $platform,
                                LoggerInterface $logger) {
        $this->moodleApi = $moodleApi;
        $this->httpClient = $httpClient;

        parent::__construct(
                $packageRepositoryFactory, $packageSourceFactory,
                $packageFormatFactory, $platform, $filesystem, $logger);
    }

    /**
     * @inheritdoc AbstractCommand
     */
    protected function configure() {
        $this
            ->setName('package')
            ->setDescription('Packages a Moodle site from a project file')
            ->setHelp(static::HELP)
            ->setDefinition(new InputDefinition([
                new InputOption(Argument::OPTION_PACKAGE_FORMAT, null,
                                InputOption::VALUE_REQUIRED,
                                Argument::OPTION_PACKAGE_FORMAT_HELP),
                new InputOption(Argument::OPTION_PROJECT_FILE, null,
                                InputOption::VALUE_REQUIRED,
                                Argument::OPTION_PROJECT_FILE_HELP),
                new InputOption(Argument::OPTION_PACKAGE_DESTINATION, null,
                                InputOption::VALUE_REQUIRED,
                                Argument::OPTION_PACKAGE_DESTINATION_HELP),
                new InputOption(Argument::OPTION_ATTEMPTS, null,
                                InputOption::VALUE_REQUIRED,
                                Argument::OPTION_ATTEMPTS_HELP, 0),
                new InputOption(Argument::OPTION_TIMEOUT, null,
                                InputOption::VALUE_REQUIRED,
                                Argument::OPTION_TIMEOUT_HELP, 60),
            ]));
    }

    /**
     * @inheritdoc AbstractCommand
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $projectFilename = $input->getOption(Argument::OPTION_PROJECT_FILE);
        $packageDestination = $this->platform->expandPath(
                $input->getOption(Argument::OPTION_PACKAGE_DESTINATION));
        $packageFormat   = $input->getOption(Argument::OPTION_PACKAGE_FORMAT);

        $tempDirectory       = $this->platform->createTempDirectory();
        $archive             = $tempDirectory
                             . $this->platform->getDirectorySeparator()
                             . 'moodle.zip';
        $destination         = $tempDirectory
                             . $this->platform->getDirectorySeparator() . 'moodle';
        $projectLockFilename = $destination . $this->platform->getDirectorySeparator()
                             . 'componentmgr.lock.json';

        $moodle  = new Moodle($destination, $this->platform);
        $project = $this->getProject($projectFilename, $projectLockFilename);

        $task = new PackageTask(
                $this->moodleApi, $project, $archive, $destination,
                $this->platform, $this->filesystem, $this->httpClient, $moodle,
                $packageFormat, $packageDestination,
                $input->getOption(Argument::OPTION_TIMEOUT),
                $input->getOption(Argument::OPTION_ATTEMPTS));
        $task->execute($this->logger);
    }
}
