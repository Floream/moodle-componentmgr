<?php

/**
 * Moodle component manager.
 *
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2016 Luke Carrier
 * @license GPL-3.0+
 */

namespace ComponentManager\PackageRepository;

use ComponentManager\Component;
use ComponentManager\ComponentSource\GitComponentSource;
use ComponentManager\ComponentSpecification;
use ComponentManager\ComponentVersion;
use Github\Api\Repo;
use Github\Client;
use Psr\Log\LoggerInterface;

/**
 * GitHub package repository.
 */
class GithubPackageRepository extends AbstractPackageRepository
        implements PackageRepository {
    /**
     * GitHub client instance.
     *
     * Lazily loaded -- use {@link getClient()} to ensure it's initialised.
     *
     * @var Client
     */
    protected $client;

    /**
     * @inheritdoc PackageRepository
     */
    public function getId() {
        return 'Github';
    }

    /**
     * @inheritdoc PackageRepository
     */
    public function getName() {
        return 'GitHub package repository';
    }

    /**
     * Get the GitHub client.
     *
     * @return Client
     */
    protected function getClient() {
        if ($this->client === null) {
            $this->client = new Client();
            if (property_exists($this->options, 'token')) {
                $this->client->authenticate(
                        $this->options->token, null, Client::AUTH_HTTP_TOKEN);
            }

        }

        return $this->client;
    }

    /**
     * @inheritdoc PackageRepository
     */
    public function resolveComponent(ComponentSpecification $componentSpecification,
                                     LoggerInterface $logger) {
        /** @var Repo $api */
        $api = $this->getClient()->api('repo');

        list($user, $repositoryName) = explode(
                '/', $componentSpecification->getExtra('repository'));

        $repository = $api->show($user, $repositoryName);

        $refs = array_merge(
                $api->tags($user, $repositoryName),
                $api->branches($user, $repositoryName));

        $versions = [];

        foreach ($refs as $ref) {
            $versions[] = new ComponentVersion(null, $ref['name'], null, [
                new GitComponentSource($repository['clone_url'], $ref['name']),
            ]);
        }

        return new Component($componentSpecification->getName(), $versions,
                             $this);
    }

    /**
     * @inheritdoc PackageRepository
     */
    public function satisfiesVersion($versionSpecification, ComponentVersion $version) {
        return $versionSpecification === $version->getRelease();
    }
}
