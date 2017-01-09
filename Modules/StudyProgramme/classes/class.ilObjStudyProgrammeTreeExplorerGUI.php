<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
require_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
require_once("./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeSettingsGUI.php");
/**
 * Class ilStudyProgrammeTreeGUI
 * ilObjStudyProgrammeTreeExplorerGUI generates the tree output for StudyProgrammes
 * This class builds the tree with drag & drop functionality and some additional buttons which triggers bootstrap-modals
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjStudyProgrammeTreeExplorerGUI extends ilExplorerBaseGUI
{
	protected $js_study_programme_path = "./Modules/StudyProgramme/templates/js/ilStudyProgramme.js";
	protected $css_study_programme_path = "./Modules/StudyProgramme/templates/css/ilStudyProgrammeTree.css";

	/**
	 * @var array
	 */
	//protected $stay_with_command = array( "", "render", "view", "infoScreen", "performPaste", "cut", "tree_view");

	/**
	 * @var int
	 */
	protected $tree_root_id;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var string css-id of the bootstrap modal dialog
	 */
	protected $modal_id;

	/**
	 * @var array js configuration for the tree
	 */
	protected $js_conf;

	/**
	 * default classes of the tree [key=>class_name]
	 * @var array
	 */
	protected $class_configuration = array(
		'node' => array(
			'node_title' => 'title',
			'node_point' => 'points',
			'node_current' => 'ilHighlighted current_node',
			'node_buttons' => 'tree_button'
		),
		'lp_object' => 'lp-object',
	);

	protected $node_template;

	/**
	 * @param $a_expl_id
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 * @param $a_tree
	 */
	public function __construct($a_tree_root_id, $modal_id, $a_expl_id, $a_parent_obj, $a_parent_cmd)
	{
		global $ilAccess, $lng, $tpl, $ilToolbar, $ilCtrl;

		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);

		$this->tree_root_id = $a_tree_root_id;

		$this->access = $ilAccess;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->toolbar = $ilToolbar;
		$this->ctrl = $ilCtrl;
		$this->modal_id = $modal_id;
		$this->js_conf = array();

		$lng->loadLanguageModule("prg");
	}


	/**
	 * Return node element
	 *
	 * @param ilObjStudyProgramme|ilObject $node
	 *
	 * @return string
	 */
	public function getNodeContent($node)
	{
		global $lng, $ilAccess;

		$current_ref_id = (isset($_GET["ref_id"]))? $_GET["ref_id"] : -1;

		$is_current_node = ($node->getRefId() == $current_ref_id);
		$is_study_programme = ($node instanceof ilObjStudyProgramme);
		$is_root_node = ($is_study_programme && $node->getRoot() == null);

		// show delete only on not current elements and not root
		$is_delete_enabled = ($is_study_programme && ($is_current_node || $is_root_node))? false : $this->checkAccess("delete", $current_ref_id);

		$is_creation_enabled = ($this->checkAccess("create", $current_ref_id));

		$node_config = array(
			'current_ref_id' =>$current_ref_id,
			'is_current_node' => $is_current_node,
			'is_delete_enabled' => $is_delete_enabled,
			'is_creation_enabled' => $is_creation_enabled,
			'is_study_programme' => $is_study_programme,
			'is_root_node' => $is_root_node
		);

		// TODO: find way to remove a-tag around the content, to create valid html
		$tpl = $this->getNodeTemplateInstance();

		$tpl->setCurrentBlock('node-content-block');
		$tpl->setVariable('NODE_TITLE_CLASSES', implode(' ', $this->getNodeTitleClasses($node_config)));
		$tpl->setVariable('NODE_TITLE', $node->getTitle());

		if ($is_study_programme) {
			$tpl->setVariable('NODE_POINT_CLASSES', $this->class_configuration['node']['node_point']);
			$tpl->setVariable('NODE_POINTS', $this->formatPointValue($node->getPoints()));
		}

		// add the tree buttons
		if ($this->checkAccess('write', $node->getRefId())) {
			if ($is_study_programme) {
				$info_button = $this->getNodeLink('ilObjStudyProgrammeSettingsGUI', 'view', array('ref_id'=>$node->getRefId(), 'currentNode'=>$node_config['is_current_node']), ilGlyphGUI::get(ilGlyphGUI::INFO));
				$tpl->setVariable('LINK_HREF', $info_button);
			} else {
				$wrapper = \ilObjectFactoryWrapper::singleton();
				$crs_ref = $wrapper->getInstanceByRefId($node->getRefId());
				$info_button = $this->getNodeLink('ilRepositoryGUI', 'edit', array('ref_id'=>$crs_ref->getTargetRefId()), ilGlyphGUI::get(ilGlyphGUI::INFO));
				$tpl->setVariable('LINK_HREF', $info_button);
			}
		}

		$tpl->parseCurrentBlock('node-content-block');

		return $tpl->get();
	}

	/**
	 * Returns array with all css classes of the title node element
	 *
	 * @param array $node_config
	 *
	 * @return array
	 */
	protected function getNodeTitleClasses($node_config)
	{
		$node_title_classes = array($this->class_configuration['node']['node_title']);
		if ($node_config['is_study_programme']) {
			if ($node_config['is_current_node']) {
				array_push($node_title_classes, $this->class_configuration['node']['node_current']);
			}
		} else {
			array_push($node_title_classes, $this->class_configuration['lp_object']);
		}

		return $node_title_classes;
	}

	/**
	 * Factory method for a new instance of a node template
	 *
	 * @return ilTemplate
	 */
	protected function getNodeTemplateInstance()
	{
		return new ilTemplate("tpl.tree_node_content.html", true, true, "Modules/StudyProgramme");
	}

	/**
	 * Returns formatted point value
	 *
	 * @param $points
	 *
	 * @return string
	 */
	protected function formatPointValue($points)
	{
		return '('. $points ." ".$this->lng->txt('prg_points').')';
	}

	/**
	 * Generate link-element
	 *
	 * @param      $target_class
	 * @param      $cmd
	 * @param      $params  url-params send to the
	 * @param      $content
	 * @param bool $async
	 *
	 * @return string
	 */
	protected function getNodeLink($target_class, $cmd, $params, $content, $async = true)
	{
		foreach ($params as $param_name => $param_value) {
			$this->ctrl->setParameterByClass($target_class, $param_name, $param_value);
		}

		return $this->ctrl->getLinkTargetByClass($target_class, $cmd, '', false, false);
	}

	/**
	 * Return root node of tree
	 *
	 * @return mixed
	 */
	public function getRootNode()
	{
		$node = ilObjStudyProgramme::getInstanceByRefId($this->tree_root_id);
		if ($node->getRoot() != null) {
			return $node->getRoot();
		}
		return $node;
	}

	/**
	 * Get node icon
	 * Return custom icon of OrgUnit type if existing
	 *
	 * @param array $a_node
	 *
	 * @return string
	 */
	public function getNodeIcon($a_node)
	{
		global $ilias;

		$obj_id = ilObject::_lookupObjId($a_node->getRefId());
		if ($ilias->getSetting('custom_icons')) {
			//TODO: implement custom icon functionality
		}

		return ilObject::_getIcon($obj_id, "tiny");
	}


	/**
	 * Returns node link target
	 *
	 * @param mixed $node
	 *
	 * @return string
	 */
	public function getNodeHref($node)
	{
		global $ilCtrl;

		if ($ilCtrl->getCmd() == "performPaste") {
			$ilCtrl->setParameterByClass("ilObjStudyProgrammeGUI", "target_node", $node->getRefId());
		}

		$ilCtrl->setParameterByClass("ilObjStudyProgrammeGUI", "ref_id", $node->getRefId());

		return '#';
	}

	/**
	 * Get childs of node
	 *
	 * @param                  $a_parent_node_id
	 *
	 * @global ilAccess
	 * @internal param int $a_parent_id parent id
	 * @return array childs
	 */
	public function getChildsOfNode($a_parent_node_id)
	{
		global $ilAccess;

		$parent_obj = ilObjectFactoryWrapper::singleton()->getInstanceByRefId($a_parent_node_id);

		$children_with_permission = array();

		// its currently only possible to have children on StudyProgrammes
		if ($parent_obj instanceof ilObjStudyProgramme) {
			$children = ($parent_obj->hasChildren())? $parent_obj->getChildren() : $parent_obj->getLPChildren();

			if (is_array($children)) {
				foreach ($children as $node) {
					if ($this->checkAccess('visible', $node->getRefId())) {
						$children_with_permission[] = $node;
					}
				}
			}
		}

		return $children_with_permission;
	}

	/**
	 * Is node clickable?
	 *
	 * @param mixed            $a_node node object/array
	 *
	 * @global ilAccessHandler $ilAccess
	 * @return boolean node clickable true/false
	 */
	public function isNodeClickable($a_node)
	{
		return true;
	}


	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 *
	 * @return string id of node
	 */
	public function getNodeId($a_node)
	{
		if (!is_null($a_node)) {
			return $a_node->getRefId();
		}
		return null;
	}

	/**
	 * List item start
	 *
	 * @param
	 * @return
	 */
	public function listItemStart($tpl, $a_node)
	{
		$tpl->touchBlock("list_item_start");
		$tpl->touchBlock("tag");
	}


	/**
	 * Returns the output of the complete tree
	 * There are added some additional javascripts before output the parent::getHTML()
	 *
	 * @return string
	 */
	public function getHTML()
	{
		$this->tpl->addCss($this->css_study_programme_path);

		$etpl = new ilTemplate("tpl.simple_tree_view.html", true, true, "Modules/StudyProgramme");
		$root_node = $this->getRootNode();
		if (!$this->getSkipRootNode() &&
			$this->isNodeVisible($this->getRootNode())) {
			$this->listStart($etpl);
			$this->renderNode($this->getRootNode(), $etpl);
			$this->listEnd($etpl);
		} else {
			$childs = $this->getChildsOfNode($this->getNodeId($root_node));
			$childs = $this->sortChilds($childs, $this->getNodeId($root_node));
			$any = false;
			foreach ($childs as $child_node) {
				if ($this->isNodeVisible($child_node)) {
					if (!$any) {
						$this->listStart($etpl);
						$any = true;
					}
					$this->renderNode($child_node, $etpl);
				}
			}
			if ($any) {
				$this->listEnd($etpl);
			}
		}

		return $etpl->get();
	}

	public function renderNode($a_node, $tpl)
	{
		$this->listItemStart($tpl, $a_node);
		if ($this->getNodeIcon($a_node) != "") {
			$tpl->setVariable("ICON", ilUtil::img($this->getNodeIcon($a_node), $this->getNodeIconAlt($a_node))." ");
		}
		$tpl->setVariable("CONTENT", $this->getNodeContent($a_node));

		$this->renderChilds($this->getNodeId($a_node), $tpl);

		$this->listItemEnd($tpl);
	}


	/**
	 * Closes certain node in the tree session
	 * The open nodes of a tree are stored in a session. This function closes a certain node by its id.
	 *
	 * @param int $node_id
	 */
	public function closeCertainNode($node_id)
	{
		if (in_array($node_id, $this->open_nodes)) {
			$k = array_search($node_id, $this->open_nodes);
			unset($this->open_nodes[$k]);
		}
		$this->store->set("on_".$this->id, serialize($this->open_nodes));
	}

	/**
	 * Open certain node in the tree session
	 * The open nodes of a tree are stored in a session. This function opens a certain node by its id.
	 *
	 * @param int $node_id
	 */
	public function openCertainNode($node_id)
	{
		$id = $this->getNodeIdForDomNodeId($node_id);
		if (!in_array($id, $this->open_nodes)) {
			$this->open_nodes[] = $id;
		}
		$this->store->set("on_".$this->id, serialize($this->open_nodes));
	}


	/**
	 * Checks permission of current tree or certain child of it
	 *
	 * @param string $permission
	 * @param null $ref_id
	 *
	 * @return bool
	 */
	protected function checkAccess($permission, $ref_id)
	{
		$checker = $this->access->checkAccess($permission, '', $ref_id);

		return $checker;
	}


	/**
	 * Checks permission of a object and throws an exception if they are not granted
	 *
	 * @param string $permission
	 * @param null $ref_id
	 *
	 * @throws ilException
	 */
	protected function checkAccessOrFail($permission, $ref_id)
	{
		if (!$this->checkAccess($permission, $ref_id)) {
			throw new ilException("You have no permission for ".$permission." Object with ref_id ".$ref_id."!");
		}
	}

	/**
	 * Adds configuration to the study-programme-tree jquery plugin
	 *
	 * @param array $js_conf
	 */
	public function addJsConf($key, $value)
	{
		$this->js_conf[$key] = $value;
	}

	/**
	 * Returns setting of the study-programme-tree
	 *
	 * @param array $js_conf
	 */
	public function getJsConf($key)
	{
		return $this->js_conf[$key];
	}
}
