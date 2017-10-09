<?php

/**
 * Moodle component manager.
 *
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2016 Luke Carrier
 * @license GPL-3.0+
 */

namespace ComponentManager\PackageRepository;

use ComponentManager\HttpClient;
use ComponentManager\Platform\Platform;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Abstract package repository.
 */
abstract class AbstractPackageRepository {
    /**
     * Filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Platform support library.
     *
     * @var \ComponentManager\Platform\Platform
     */
    protected $platform;

    /**
     * Options.
     *
     * @var \stdClass
     */
    protected $options;

    /**
     * HTTP client.
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * Initialiser.
     *
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param HttpClient                               $httpClient
     * @param \ComponentManager\Platform\Platform      $platform
     * @param \stdClass                                $options
     */
    public function __construct(Filesystem $filesystem, HttpClient $httpClient,
                                Platform $platform, stdClass $options) {
        $this->filesystem = $filesystem;
        $this->httpClient = $httpClient;
        $this->platform   = $platform;
        $this->options    = $options;
    }

    /**
     * Get repository identifier.
     *
     * @return string
     */
    abstract public function getId();
}
