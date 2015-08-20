<?php

/**
 * Moodle component manager.
 *
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2015 Luke Carrier
 * @license GPL v3
 */

namespace ComponentManager\ComponentSource;

/**
 * Git repository component source.
 */
class GitComponentSource extends AbstractComponentSource
        implements ComponentSource {
    /**
     * Git repository URI.
     *
     * @var string
     */
    protected $repositoryUri;

    /**
     * Git tag name.
     *
     * @var string
     */
    protected $tag;

    /**
     * Initialiser.
     *
     * @param string $repositoryUri
     * @param string $tag
     */
    public function __construct($repositoryUri, $tag) {
        $this->repositoryUri = $repositoryUri;
        $this->tag           = $tag;
    }

    /**
     * @override \ComponentManager\ComponentSource\ComponentSource
     */
    public function getName() {
        return 'Git repository';
    }

    /**
     * @override \ComponentManager\ComponentSource\ComponentSource
     */
    public function getType() {
        return 'GitComponentSource';
    }

    /**
     * Get the Git repository URI.
     *
     * @return string
     */
    public function getRepositoryUri() {
        return $this->repositoryUri;
    }

    /**
     * Get the Git tag name.
     *
     * @return string
     */
    public function getTag() {
        return $this->tag;
    }
}
