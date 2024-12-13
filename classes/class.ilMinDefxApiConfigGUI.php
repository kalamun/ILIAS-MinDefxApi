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
    foreach ($this->replace_list as $file_path) {
      $file_name = basename($file_path);
      $content = file_get_contents($file_path);
      if (strpos($content, '* edited by MinDefxAPI v') !== false) {
        $this->is_active = true;
        $this->compatible_version = preg_match('#^((\d+\.)+\d+)#', substr($content, strpos($content, '* edited by MinDefxAPI v') + 10, 8))[0];
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
    foreach ($this->replace_list as $copy_to) {
      if ($this->compatible_version) {
        $file_name = basename($file_path);
        $copy_from = __DIR__ . '/../bkup_files/' . $this->compatible_version . '/' . $file_name;

        if (file_exists($copy_from) && file_exists($copy_to)) {
          copy($copy_from, $copy_to);
        }
      }
  }

  protected function disablexApi(): void
  {
    foreach ($this->replace_list as $copy_from) {
      if ($this->compatible_version) {
        $file_name = basename($file_path);
        $copy_to = __DIR__ . '/../bkup_files/' . $this->compatible_version . '/' . $file_name;

        if (file_exists($copy_from) && file_exists($copy_to)) {
          copy($copy_from, $copy_to);
        }
      }
  }

  protected function configure(): void
  {
    global $tpl, $ilCtrl, $lng;


		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
    $form->setTitle($this->plugin->txt("settings"));
    
    if ($this->compatible_version) {

      if (!$this->is_active) {
        $plugin_enabled_heading = new ilFormSectionHeaderGUI();
        $plugin_enabled_heading->setTitle('Your ILIAS version is supported (v. ' . $this->compatible_version . ')');
        $form->addItem($plugin_enabled_heading);
  
        $form->addCommandButton("enablexApi", $lng->txt("Enable"));
      } else {
        $plugin_enabled_heading = new ilFormSectionHeaderGUI();
        $plugin_enabled_heading->setTitle('Active version: ' . $this->compatible_version);
        $form->addItem($plugin_enabled_heading);

        $form->addCommandButton("disablexApi", $lng->txt("Disable"));
      }

    } else {
      $plugin_enabled_heading = new ilFormSectionHeaderGUI();
      $plugin_enabled_heading->setTitle($this->compatible_version);
      $form->addItem($plugin_enabled_heading);
    }

		$tpl->setContent($form->getHTML());
  }

  protected function updateConfigure()/*: void*/
  {
    if (!empty($_POST['plugin_enabled'])) {
      foreach ($this->replace_list as $rule) {
        if (file_exists($rule['path'])) {
          $file_content = file_get_contents($rule['path']);
          
          /* backup */
          if (strpos($file_content, '/* edited by MinDefxApi') === false) {
            copy($rule['path'], dirname($rule['path']) . '/_' . basename($rule['path']));
          }
  
          if (strpos($file_content, '/* edited by MinDefxApi') === false) {
            $file_content = "/* edited by MinDefxApi */\n\n" . $file_content;
          }
  
          $file_content = str_replace($rule['match'], $rule['replace'], $file_content);
          file_put_contents($rule['path'], $file_content);
        }
      }
      
    } else {
      foreach ($this->replace_list as $rule) {
        if (file_exists($rule['path'])) {
          $file_content = file_get_contents($rule['path']);
          
          /* backup */
          if (strpos($file_content, '/* edited by MinDefxApi') === false) {
            move(dirname($rule['path']) . '/_' . basename($rule['path']), $rule['path']);
          }
        }
      }

    }

    self::configure();

    ilUtil::sendSuccess($this->plugin->txt("configuration_saved"), true);

  }
}