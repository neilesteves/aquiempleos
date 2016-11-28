<?php

/**
 * @author Luis Alberto Mayta <slovacus@gmail.com>
 */
class App_Application extends Zend_Application
{
    /**
     *
     * @var Zend_Cache_Core|null
     */
    protected $_configCache;

    /**
     * 
     * @param type $environment
     * @param type $inis
     * @param Zend_Cache_Core $configCache
     */
    public function __construct($environment, $inis = array(),
                                Zend_Cache_Core $configCache = null)
    {

        require_once 'Zend/Loader/Autoloader.php';
        $this->_autoloader  = Zend_Loader_Autoloader::getInstance();
        $this->_configCache = $configCache;
        $path               = APPLICATION_PATH.'/configs/';

        $docker = FALSE;
        if (in_array('docker.php', $inis)) {
            $docker = TRUE;
            unset($inis[9]);
        }
        $options = array();
        if (in_array('application.ini', $inis)) {
            $applicationIni = new Zend_Config_Ini(
                $path."application.ini", $environment
            );
            unset($inis['0']);
        }
        if (in_array('local.ini', $inis)) {
            $applicationIni = new Zend_Config_Ini(
                $path."local.ini", $environment
            );
            unset($inis['0']);
        }
        $options = $applicationIni->toArray();
        foreach ($inis as $value) {
            $iniFile = $path.$value;

            if (is_readable($iniFile)) {
                $config  = $this->_loadConfig($iniFile);
                $options = $this->mergeOptions(
                    $options, $config
                );
            }
        }

        if ($docker && file_exists($path.'docker.php')) {
            require_once $path.'docker.php';
            $options = $this->mergeOptions(
                $options, $configDocker
            );
        }

        $this->setOptions($options);
    }

    protected function _cacheId($file)
    {
        return md5($file.'_'.$this->getEnvironment());
    }

    /**
     * 
     * @param Zend_Config $config
     */
    public function addConfig(Zend_Config $config)
    {
        $this->_loadConfig($config);
    }

    /**
     * 
     * @param type $file
     * @return type
     */
    protected function _loadConfig($file)
    {
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (
            $this->_configCache === null || $suffix == 'php' || $suffix == 'inc'
        ) { //No need for caching those
            return parent::_loadConfig($file);
        }

        $configMTime = filemtime($file);

        $cacheId        = $this->_cacheId($file);
        $cacheLastMTime = $this->_configCache->test($cacheId);
        if (
            $cacheLastMTime !== false && $configMTime < $cacheLastMTime
        ) { //Valid cache?
            return $this->_configCache->load($cacheId, true);
        } else {
            $config = parent::_loadConfig($file);
            $this->_configCache->save($config, $cacheId, array(), null);

            return $config;
        }
    }
}