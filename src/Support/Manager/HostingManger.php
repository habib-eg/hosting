<?php

namespace Habib\Hosting\Support\Manager;

use Habib\Hosting\Cpanel\Cpanel;

use Illuminate\Support\Manager;

class HostingManger extends Manager
{
    /**
     * Get the default hosting driver name.
     *
     * @return array
     */
    public function getDefaultDriver()
    {
        return $this->config->get('servers.'.$this->getDriver().'.'.$this->getDefaultServer()) ?? [];
    }

    /**
     * Get the default hosting driver name.
     *
     * @return string
     */
    public function getDefaultServer()
    {
        return $this->config->get('servers.default.server');
    }

    /**
     * Set the default hosting driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->config->set('servers.default.driver', $name);
    }
    /**
     * Set the default hosting driver name.
     *
     * @param string $name
     * @return void
     */
    public function setDefaultServer($name)
    {
        $this->config->set('servers.default.server', $name);
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @return mixed
     */
    protected function callCustomCreator($driver)
    {
        return $this->buildSession(parent::callCustomCreator($driver));
    }

    public function getDriver()
    {
        return $this->config->get('servers.default.driver');
    }
}
