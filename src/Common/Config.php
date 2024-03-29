<?php
declare(strict_types=1);

namespace Unitiweb\Deploy\Common;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * @var DeployOutput
     */
    protected $output;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var DeployProcess
     */
    protected $process;

    public function __construct(DeployOutput $output, string $configDir = null)
    {
        assert(valid_num_args());

        $this->output = $output;
        $this->configFile = $configDir . '/config.yml';
    }

    /**
     * Check for yml configuration
     */
    public function load()
    {
        assert(valid_num_args());

        // Check to see if the config file exists
        if (!file_exists($this->configFile)) {
            $this->save();
        }

        // Load the config.yml file
        try {
            $config = Yaml::parse(file_get_contents($this->configFile));
        } catch (ParseException $e) {
            $this->output->error("Unable to parse the config YAML string: {$e->getMessage()}");
        }

        // Make sure the config.yml file is a Deploy type
        if (!isset($config['Deploy'])) {
            $this->output->error("The config file appears to not be valid");
        }

        $this->config = $config['Deploy'];
    }

    /**
     * Save changes
     */
    public function save() : bool
    {
        assert(valid_num_args());

        $config = $this->prepareToSave();

        $yaml = Yaml::dump($config, 10, 4, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);

        if (false === file_put_contents($this->configFile, $yaml)) {
            $this->output->error('The configuration file could not be saved');
            return false;
        }

        return true;
    }

    /**
     * Get namespace
     */
    public function getNamespace() : string
    {
        assert(valid_num_args());

        return isset($this->config['Namespace']) ? $this->config['Namespace'] : '';
    }

    /**
     * Set namespace
     */
    public function setNamespace(string $namespace)
    {
        assert(valid_num_args());

        $this->config['Namespace'] = $namespace;
    }

    /**
     * Get shared file paths
     */
    public function getShared() : array
    {
        assert(valid_num_args());

        return isset($this->config['Shared']) && is_array($this->config['Shared']) ? $this->config['Shared'] : [];
    }

    /**
     * Add to shared array
     */
    public function pushShared(string $path)
    {
        assert(valid_num_args());

        if (!isset($this->config['Shared'])) {
            $this->config['Shared'] = [];
        }

        if (!in_array($path, $this->config['Shared'])) {
            array_push($this->config['Shared'], trim($path));
        }
    }

    /**
     * Remove from shared array
     */
    public function popShared(string $path)
    {
        assert(valid_num_args());

        $shared = $this->getShared();

        for ($i = 0; $i < count($shared); $i++) {
            if ($shared[$i] === $path) {
                array_splice($this->config['Shared'], $i, 1);
            }
        }
    }

    /**
     * Get files to remove after deploy
     */
    public function getRemove() : array
    {
        assert(valid_num_args());

        return isset($this->config['Remove']) && is_array($this->config['Remove']) ? $this->config['Remove'] : [];
    }

    /**
     * Add to remove array
     */
    public function pushRemove(string $path)
    {
        assert(valid_num_args());

        if (!isset($this->config['Remove'])) {
            $this->config['Remove'] = [];
        }

        if (!in_array($path, $this->config['Remove'])) {
            array_push($this->config['Remove'], trim($path));
        }
    }

    /**
     * Remove from the remove array
     */
    public function popRemove(string $path)
    {
        assert(valid_num_args());

        $remove = $this->getRemove();

        for ($i = 0; $i < count($remove); $i++) {
            if ($remove[$i] === $path) {
                array_splice($this->config['Remove'], $i, 1);
            }
        }
    }

    /**
     * Get github
     */
    public function getGitHub() : array
    {
        assert(valid_num_args());

        return [
            'Repo' => $this->config['GitHub']['Repo'] ?? null
        ];
    }

    /**
     * Set github
     */
    public function setGitHub(string $key, ?string $value)
    {
        assert(valid_num_args());
        assert(in_array($key, ['Repo']));

        $this->config['GitHub'][$key] = $value;
    }

    /**
     * Get chown
     */
    public function getChown() : array
    {
        assert(valid_num_args());

        return [
            'Pre' => [
                'Group' => $this->config['Chown']['Pre']['Group'] ?? null,
                'Paths' => $this->config['Chown']['Pre']['Paths'] ?? [],
            ],
            'Post' => [
                'Group' => $this->config['Chown']['Post']['Group'] ?? null,
                'Paths' => $this->config['Chown']['Post']['Paths'] ?? [],
            ],
        ];
    }

    /**
     * Set Chown Group
     */
    public function setChownGroup(string $prePost, ?string $group)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));

        $this->config['Chown'][$prePost]['Group'] = $group;
    }

    /**
     * Add path to chown
     */
    public function pushChownPath(string $prePost, string $path)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));

        if (!isset($this->config['Chown'][$prePost]['Paths'])) {
            $this->config['Chown'][$prePost]['Paths'] = [];
        }

        array_push($this->config['Chown'][$prePost]['Paths'], trim($path));
    }

    /**
     * Remove path from chown
     */
    public function popChownPath(string $prePost, ?string $path)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));

        $chown = $this->getChown();

        for ($i = 0; $i < count($chown[$prePost]['Paths']); $i++) {
            if ($chown[$prePost]['Paths'][$i] === $path) {
                array_splice($this->config['Chown'][$prePost]['Paths'], $i, 1);
            }
        }
    }

    /**
     * Get chmod
     */
    public function getChmod() : array
    {
        assert(valid_num_args());

        return [
            'Pre' => [
                'Permission' => $this->config['Chmod']['Pre']['Permission'] ?? null,
                'Paths' => $this->config['Chmod']['Pre']['Paths'] ?? [],
            ],
            'Post' => [
                'Permission' => $this->config['Chmod']['Post']['Permission'] ?? null,
                'Paths' => $this->config['Chmod']['Post']['Paths'] ?? [],
            ],
        ];
    }

    /**
     * Set Chmod Group
     */
    public function setChmodPermission(string $prePost, $permission)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));
        assert(is_string($permission) || is_int($permission || is_null($permission)));

        $this->config['Chmod'][$prePost]['Permission'] = $permission;
    }

    /**
     * Add path to chmod
     */
    public function pushChmodPath(string $prePost, string $path)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));

        if (!isset($this->config['Chmod'][$prePost]['Paths'])) {
            $this->config['Chmod'][$prePost]['Paths'] = [];
        }

        $this->config['Chmod'][$prePost]['Paths'] = $this->config['Chmod'][$prePost]['Paths'] ?? [];
        array_push($this->config['Chmod'][$prePost]['Paths'], trim($path));
    }

    /**
     * Remove path from chown
     */
    public function popChmodPath(string $prePost, string $path)
    {
        assert(valid_num_args());
        assert(in_array($prePost, ['Pre', 'Post']));

        $chown = $this->getChmod();

        for ($i = 0; $i < count($chown[$prePost]['Paths']); $i++) {
            if ($chown[$prePost]['Paths'][$i] === $path) {
                array_splice($this->config['Chmod'][$prePost]['Paths'], $i, 1);
            }
        }
    }

    /**
     * Prepare config array for saving
     */
    protected function prepareToSave() : array
    {
        assert(valid_num_args());

        $config = [
            'Namespace' => $this->getNamespace(),
            'Shared' => $this->getShared(),
            'Remove' => $this->getRemove(),
            'GitHub' => $this->getGitHub(),
            'Chown' => $this->getChown(),
            'Chmod' => $this->getChmod(),
        ];

        return ['Deploy' => $config];
    }
}
