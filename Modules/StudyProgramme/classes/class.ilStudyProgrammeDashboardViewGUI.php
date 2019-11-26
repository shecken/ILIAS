<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilStudyProgrammeDashboardViewGUI extends ilBlockGUI {
    const BLOCK_TYPE = "prgsdashboardview";

    /**
     * @var ilLanguage
     */
    protected $il_lng;

    /**
     * @var ilUser
     */
    protected $il_user;

    /**
     * @var ilAccessHandler
     */
    protected $il_access;

    /**
     * @var ilSetting
     */
    protected $il_setting;

    /**
     * @var ilStudyProgrammeUserAssignment[]
     */
    protected $users_assignments;

    /**
     * @var visible_on_pd_mode
     */
    protected $visible_on_pd_mode;

    /**
     * @var show_info_message
     */
    protected $show_info_message;

    public function __construct() {
        global $DIC;



        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $ilSetting = $DIC['ilSetting'];
        $this->il_lng = $lng;
        $this->il_user = $ilUser;
        $this->il_access = $ilAccess;
        $this->il_setting = $ilSetting;
        $this->il_logger = ilLoggerFactory::getLogger('prg');

        // No need to load data, as we won't display this.
        if (!$this->shouldShowThisList()) {
            throw new Exception("List shouldn't be shown");
        }

        $this->readUsersAssignments();
        //check which kind of option is selected in settings
        $this->readVisibleOnPDMode();
        //check to display info message if option "read" is selected
        $this->readToShowInfoMessage();

        // As this won't be visible we don't have to initialize this.
        if (!$this->userHasReadableStudyProgrammes()) {
            throw new Exception('Nothing to show');
        }

        $this->setTitle($this->il_lng->txt("objs_prg"));

        parent::__construct();
    }

    public function getHTML() {
        // TODO: This should be determined from somewhere up in the hierarchy, as
        // this will lead to problems, when e.g. a command changes. But i don't see
        // how atm...
        if (!$this->shouldShowThisList()) {
            return "";
        }

        if (!$this->userHasReadableStudyProgrammes()) {
            return "";
        }
        return parent::getHTML();
    }

    /**
     * @throws ilException
     * @throws ilTemplateException
     */
    public function getDataSectionContent() {
        $content = "";
        $this->il_lng->loadLanguageModule('prg');
        $tpl = new ilTemplate('tpl.dashboard_view.html', false, false, "Modules/StudyProgramme");
        foreach ($this->users_assignments as $assignments) {
            /** @var ilStudyProgrammeUserAssignment $assignment */
            foreach ($assignments as $assignment) {
                if(!$this->isReadable($assignment)) {
                    continue 2;
                }
            }
            ksort($assignments);
            $clone = $assignments;
            /** @var ilStudyProgrammeUserAssignment $current */
            $current = array_shift($clone);
           // $valid = $this->findValid($assignments);
            /** @var ilStudyProgrammeSettings $current_prg_settings */
            $current_prg_settings = $current->getStudyProgramme()->getRawSettings();
            $validation_expires =
                ! is_null($current_prg_settings->getValidityOfQualificationDate()) ||
                $current_prg_settings->getValidityOfQualificationPeriod() != -1;

            /** @var ilStudyProgrammeUserProgress $current_progress */
            $current_progress = $current->getStudyProgramme()->getProgressesOf((int)$this->il_user->getId());
            $current_status = $current_progress->getStatus();

            $points = $current_prg_settings->getPoints();
            $children = ilObjStudyProgramme::getAllChildren((int)$current->getStudyProgramme()->getRefId());
            $max_points = 0;
            /** @var ilObjStudyProgramme $child */
            foreach ($children as $child) {
                $max_points += $child->getRawSettings()->getPoints();
            }

            $minimum_percents = $max_points / 100 * $points;
            $current_percents = $max_points / 100 * $current_progress->getAmountOfPoints();

            $deadline = $current_prg_settings->getDeadlineDate();
            $restart_date = $current->getRestartDate();
            $valid = true;

            if($this->doesNotExpireIsValidAndInProgress($validation_expires, $valid, $current_status)) {
                $content .= $this->printNotExpireIsValidAndInProgress($tpl);
            }

            if($this->doesNotExpireIsValidAndCompleted($validation_expires, $valid, $current_status)) {
                $content .= $this->printNotExpireIsValidAndCompleted($tpl);
            }

            if($this->doesNotExpireIsNotValidAndInProgress($validation_expires, $valid, $current_status)) {
                $content .= $this->printNotExpireIsNotValidAndInProgress($tpl);
            }

            if($this->doesNotExpireIsNotValidAndCompleted($validation_expires, $valid, $current_status)) {
                $content .= $this->printNotExpireIsNotValidAndCompleted($tpl);
            }

            if($this->doesExpireIsValidAndInProgress($validation_expires, $valid, $current_status)) {
                $content .= $this->printExpireIsValidAndInProgress($tpl);
            }

            if($this->doesExpireIsValidAndCompleted($validation_expires, $valid, $current_status)) {
                $content .= $this->printExpireIsValidAndCompleted($tpl);
            }

            if($this->doesExpireIsNotValidAndInProgress($validation_expires, $valid, $current_status)) {
                $content .= $this->printExpireIsNotValidAndInProgress($tpl);
            }

            if($this->doesExpireIsNotValidAndCompleted($validation_expires, $valid, $current_status)) {
                $content .= $this->printExpireIsNotValidAndCompleted($tpl);
            }


            $tpl->setVariable('LABEL_VALID', $this->txt('prg_dash_label_valid'));
            $tpl->setVariable('LABEL_MINIMUM', $this->txt('prg_dash_label_minimum'));
            $tpl->setVariable('LABEL_GAIN', $this->txt('prg_dash_label_gain'));
            $tpl->setVariable('LABEL_STATUS', $this->txt('prg_dash_label_status'));

            $tpl->setVariable('LABEL_FINISH_UNTIL', $this->txt('prg_dash_label_finish_until'));
            $tpl->setVariable('LABEL_RESTART_FROM', $this->txt('prg_dash_label_restart_from'));
        }
        return $content;
    }

    protected function doesNotExpireIsValidAndInProgress($validation_expires, $valid, $current_status) : bool
    {
        return
            ! $validation_expires &&
            $valid &&
            $current_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ;
    }

    protected function doesNotExpireIsValidAndCompleted($validation_expires, $valid, $current_status) : bool
    {
        $status = [ilStudyProgrammeProgress::STATUS_ACCREDITED, ilStudyProgrammeProgress::STATUS_COMPLETED];
        return
            ! $validation_expires &&
            $valid &&
            in_array($current_status, $status)
        ;
    }

    protected function doesNotExpireIsNotValidAndInProgress($validation_expires, $valid, $current_status) : bool
    {
        return
            ! $validation_expires &&
            ! $valid &&
            $current_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ;
    }

    protected function doesNotExpireIsNotValidAndCompleted($validation_expires, $valid, $current_status) : bool
    {
        $status = [ilStudyProgrammeProgress::STATUS_ACCREDITED, ilStudyProgrammeProgress::STATUS_COMPLETED];
        return
            ! $validation_expires &&
            ! $valid &&
            in_array($current_status, $status)
        ;
    }

    protected function doesExpireIsValidAndInProgress($validation_expires, $valid, $current_status) : bool
    {
        return
            $validation_expires &&
            $valid &&
            $current_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ;
    }

    protected function doesExpireIsValidAndCompleted($validation_expires, $valid, $current_status) : bool
    {
        $status = [ilStudyProgrammeProgress::STATUS_ACCREDITED, ilStudyProgrammeProgress::STATUS_COMPLETED];
        return
            $validation_expires &&
            $valid &&
            in_array($current_status, $status)
        ;
    }

    protected function doesExpireIsNotValidAndInProgress($validation_expires, $valid, $current_status) : bool
    {
        return
            $validation_expires &&
            ! $valid &&
            $current_status == ilStudyProgrammeProgress::STATUS_IN_PROGRESS
        ;
    }

    protected function doesExpireIsNotValidAndCompleted($validation_expires, $valid, $current_status) : bool
    {
        $status = [ilStudyProgrammeProgress::STATUS_ACCREDITED, ilStudyProgrammeProgress::STATUS_COMPLETED];
        return
            $validation_expires &&
            ! $valid &&
            in_array($current_status, $status)
        ;
    }

    protected function printNotExpireIsValidAndInProgress(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printNotExpireIsValidAndCompleted(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printNotExpireIsNotValidAndInProgress(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printNotExpireIsNotValidAndCompleted(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printExpireIsValidAndInProgress(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printExpireIsValidAndCompleted(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printExpireIsNotValidAndInProgress(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    protected function printExpireIsNotValidAndCompleted(ilGlobalTemplateInterface $tpl) : string
    {
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    public function getBlockType(): string {
        return self::BLOCK_TYPE;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject(): bool {
        return false;
    }

    public function fillDataSection() {
        assert($this->userHasReadableStudyProgrammes()); // We should not get here.
        $this->tpl->setVariable("BLOCK_ROW", $this->getDataSectionContent());
    }

    protected function userHasReadableStudyProgrammes() {
        if (count($this->users_assignments) == 0) {
            return false;
        }
        foreach ($this->users_assignments as $assignments) {
            foreach ($assignments as $assignment) {
                if ($this->isReadable($assignment)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function readVisibleOnPDMode() {
        $this->visible_on_pd_mode = $this->il_setting->get(ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD);
    }

    protected function hasPermission(ilStudyProgrammeUserAssignment $assignment, $permission) {
        $prg = $assignment->getStudyProgramme();
        return $this->il_access->checkAccess($permission, "", $prg->getRefId(), "prg", $prg->getId());
    }

    protected function readToShowInfoMessage() {
        $viewSettings = new ilPDSelectedItemsBlockViewSettings($GLOBALS['DIC']->user(), (int)$_GET['view']);
        $this->show_info_message = $viewSettings->isStudyProgrammeViewActive();
    }

    protected function isVisible(ilStudyProgrammeUserAssignment $assignment) {
        return $this->hasPermission($assignment,"visible");
    }

    protected function isReadable(ilStudyProgrammeUserAssignment $assignment) {
        if($this->visible_on_pd_mode == ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS){
            return true;
        }

        return $this->hasPermission($assignment,"read");
    }

    protected function shouldShowThisList() {
        global $DIC;
        $ctrl = $DIC->ctrl();
        return ($_GET["cmd"] == "jumpToSelectedItems" ||
                ($ctrl->getCmdClass() == "ildashboardgui" && $ctrl->getCmd() == "show")
            ) && !$_GET["expand"];
    }

    protected function readUsersAssignments() {
        /** @var ilStudyProgrammeUserAssignmentDB $assignments_db */
        $assignments_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        $this->users_assignments = $assignments_db->getDashboardInstancesforUser($this->il_user->getId());
    }

    protected function txt(string $code) : string
    {
        return $this->il_lng->txt($code);
    }
}