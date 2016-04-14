<?php

/**
 * Moodle component manager.
 *
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2016 Luke Carrier
 * @license GPL-3.0+
 */

namespace ComponentManager\PackageRepository;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Package repository factory.
 */
class PackageRepositoryFactory {
    /**
     * Class name format.
     *
     * @var string
     */
    const CLASS_NAME_FORMAT = '\ComponentManager\PackageRepository\%sPackageRepository';

    /**
     * Filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Base directory for package repository caches.
     *
     * @var string
     */
    protected $cacheDirectory;

    /**
     * Initialiser.
     *
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $cacheDirectory
     */
    public function __construct(Filesystem $filesystem, $cacheDirectory) {
        $this->filesystem     = $filesystem;
        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Get package repository.
     *
     * @param string    $id
     * @param \stdClass $options
     *
     * @return \ComponentManager\PackageRepository\PackageRepository
     */
    public function getPackageRepository($id, $options) {
        // @todo diagnose the cause of static::CLASS_NAME_FORMAT not working
        $className = sprintf(self::CLASS_NAME_FORMAT, $id);

        return new $className($this->filesystem, $this->cacheDirectory,
                              $options);
    }
}
