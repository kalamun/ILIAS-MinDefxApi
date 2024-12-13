<?php
/**
 * Class ilMinDefxApiPlugin
 * @author  Kalamun <rp@kalamun.net>
 * @version $Id$
 */

class ilMinDefxApiPlugin extends ilUserInterfaceHookPlugin
{
    const CTYPE = "Services";
    const CNAME = "UIComponent";
    const SLOT_ID = "uihk";
    const PLUGIN_NAME = "MinDefxApi";
    protected static $instance = null;

    public function __construct(
        \ilDBInterface $db,
        \ilComponentRepositoryWrite $component_repository,
        string $id
    )
    {
        parent::__construct($db, $component_repository, $id);
    }

    public static function getInstance() : ilMinDefxApiPlugin
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $component_repository = $DIC['component.repository'];
        $component_factory = $DIC['component.factory'];

        $plugin_info = $component_repository->getComponentByTypeAndName(
            self::CTYPE,
            self::CNAME
        )->getPluginSlotById(self::SLOT_ID)->getPluginByName(self::PLUGIN_NAME);

        self::$instance = $component_factory->getPlugin($plugin_info->getId());

        return self::$instance;
    }

    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }

}
