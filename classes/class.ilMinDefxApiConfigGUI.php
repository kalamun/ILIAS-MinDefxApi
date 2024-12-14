<?php
/**
 * Class ilMinDefxApiConfigGUI
 * @author            Roberto Pasini <bonjour@kalamun.net>
 * @ilCtrl_IsCalledBy ilMinDefxApiConfigGUI: ilObjComponentSettingsGUI
 */

 class ilMinDefxApiConfigGUI extends ilPluginConfigGUI {

  const PLUGIN_CLASS_NAME = ilMinDefxApiPlugin::class;
  const CMD_CONFIGURE = "configure";
  const CMD_ENABLE = "enablexApi";
  const CMD_DISABLE = "disablexApi";

  protected $dic;
  protected $plugin;
  protected $lng;
  protected $request;
  protected $user;
  protected $ctrl;
  protected $object;

  protected $compatible_version;
  protected $is_active;
  protected $replace_list;
  protected $is_writable;
  
  public function __construct()
  {
    global $DIC;
    $this->dic = $DIC;
    $this->plugin = ilMinDefxApiPlugin::getInstance();
    $this->lng = $this->dic->language();
    // $this->lng->loadLanguageModule("assessment");
    $this->request = $this->dic->http()->request();
    $this->user = $this->dic->user();
    $this->ctrl = $this->dic->ctrl();
    $this->object = $this->dic->object();
    
    $this->replace_list = [
      "./Modules/CmiXapi/classes/class.ilCmiXapiStatementsGUI.php",
    ];

    $this->detect_version();
  }
  
  public function detect_version() {
    $this->is_writable = true;
    $this->is_active = false;
    $this->compatible_version = false;

    foreach ($this->replace_list as $file_path) {
      if (!is_writable($file_path)) {
        $this->is_writable = false;
      }

      $file_name = basename($file_path);
      $content = file_get_contents($file_path);
      if (strpos($content, '* edited by MinDefxAPI v') !== false) {
        $this->is_active = true;
        preg_match('#^((\d+\.)+\d+)#', substr($content, strpos($content, '* edited by MinDefxAPI v') + 24, 8), $matched_version);
        $this->compatible_version = $matched_version[0];
      } else {
        foreach (glob(__DIR__ . '/../bkup_files/*', GLOB_ONLYDIR) as $path) {
          $bkup_file_content = file_get_contents($path . '/' . $file_name);
          if ($bkup_file_content == $content) {
            $this->compatible_version = basename($path);
          }
        }
      }
    }
  }
  
  public function performCommand(string $cmd):void
  {
    $this->plugin = $this->getPluginObject();

    switch ($cmd)
		{
			case self::CMD_CONFIGURE:
      case self::CMD_DISABLE:
      case self::CMD_ENABLE:
        $this->{$cmd}();
        break;

      default:
        break;
		}
  }

  protected function enablexApi(): void
  {
    foreach ($this->replace_list as $file_path) {
      if ($this->compatible_version) {
        $file_name = basename($file_path);
        $copy_from = __DIR__ . '/../src_files/' . $this->compatible_version . '/' . $file_name;
        $copy_to = $file_path;

        if (file_exists($copy_from) && file_exists($copy_to)) {
          copy($copy_from, $copy_to);
        }
      }
    }
    // ilUtil::sendSuccess($this->plugin->txt("configuration_saved"), true);
    $this->detect_version();
    $this->configure();
  }
  
  protected function disablexApi(): void
  {
    foreach ($this->replace_list as $file_path) {
      if ($this->compatible_version) {
        $file_name = basename($file_path);
        $copy_from = __DIR__ . '/../bkup_files/' . $this->compatible_version . '/' . $file_name;
        $copy_to = $file_path;
        
        if (file_exists($copy_from) && file_exists($copy_to)) {
          copy($copy_from, $copy_to);
        }
      }
    }
    // ilUtil::sendSuccess($this->plugin->txt("configuration_saved"), true);
    $this->detect_version();
    $this->configure();
  }

  protected function configure(): void
  {
    global $tpl, $ilCtrl;


		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
    $form->setTitle($this->plugin->txt("settings"));
    
    if (!$this->is_writable) {
      $plugin_enabled_heading = new ilFormSectionHeaderGUI();
      $plugin_enabled_heading->setTitle($this->plugin->txt('not_writable'));
      $form->addItem($plugin_enabled_heading);

    } elseif ($this->compatible_version) {

      if (!$this->is_active) {
        $plugin_enabled_heading = new ilFormSectionHeaderGUI();
        $plugin_enabled_heading->setTitle($this->plugin->txt('status_supported') . ' (v.' . $this->compatible_version . ')');
        $form->addItem($plugin_enabled_heading);
  
        $form->addCommandButton("enablexApi", $this->plugin->txt("enable"));
      } else {
        $plugin_enabled_heading = new ilFormSectionHeaderGUI();
        $plugin_enabled_heading->setTitle($this->plugin->txt('status_active') . ' (v.' . $this->compatible_version . ')');
        $form->addItem($plugin_enabled_heading);

        $form->addCommandButton("disablexApi", $this->plugin->txt("disable"));
      }

    } else {
      $plugin_enabled_heading = new ilFormSectionHeaderGUI();
      $plugin_enabled_heading->setTitle($this->plugin->txt('compatible_version'));
      $form->addItem($plugin_enabled_heading);
    }

		$tpl->setContent($form->getHTML());
  }
}
