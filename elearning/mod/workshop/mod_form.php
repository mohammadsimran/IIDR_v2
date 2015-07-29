<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main workshop configuration form
 *
 * The UI mockup has been proposed in MDL-18688
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/dev/lib/formslib.php
 *
 * @package    mod
 * @subpackage workshop
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/filelib.php');

class mod_workshop_mod_form extends moodleform_mod {

    /**
     * Defines the workshop instance configuration form
     *
     * @return void
     */
    function definition() {

        global $CFG, $COURSE;
        $workshopconfig = get_config('workshop');
        $mform = $this->_form;

        // General --------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Workshop name
        $label = get_string('workshopname', 'workshop');
        $mform->addElement('text', 'name', $label, array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Introduction
        $this->add_intro_editor(false, get_string('introduction', 'workshop'));

        // Workshop features ----------------------------------------------------------
        $mform->addElement('header', 'workshopfeatures', get_string('workshopfeatures', 'workshop'));

        $label = get_string('useexamples', 'workshop');
        $text = get_string('useexamples_desc', 'workshop');
        $mform->addElement('checkbox', 'useexamples', $label, $text);
        $mform->addHelpButton('useexamples', 'useexamples', 'workshop');

        $label = get_string('usepeerassessment', 'workshop');
        $text = get_string('usepeerassessment_desc', 'workshop');
        $mform->addElement('checkbox', 'usepeerassessment', $label, $text);
        $mform->addHelpButton('usepeerassessment', 'usepeerassessment', 'workshop');

        $label = get_string('useselfassessment', 'workshop');
        $text = get_string('useselfassessment_desc', 'workshop');
        $mform->addElement('checkbox', 'useselfassessment', $label, $text);
        $mform->addHelpButton('useselfassessment', 'useselfassessment', 'workshop');

        // Grading settings -----------------------------------------------------------
        $mform->addElement('header', 'gradingsettings', get_string('gradingsettings', 'workshop'));

        $grades = workshop::available_maxgrades_list();

        $label = get_string('submissiongrade', 'workshop');
        $mform->addElement('select', 'grade', $label, $grades);
        $mform->setDefault('grade', $workshopconfig->grade);
        $mform->addHelpButton('grade', 'submissiongrade', 'workshop');

        $label = get_string('gradinggrade', 'workshop');
        $mform->addElement('select', 'gradinggrade', $label , $grades);
        $mform->setDefault('gradinggrade', $workshopconfig->gradinggrade);
        $mform->addHelpButton('gradinggrade', 'gradinggrade', 'workshop');

        $label = get_string('strategy', 'workshop');
        $mform->addElement('select', 'strategy', $label, workshop::available_strategies_list());
        $mform->setDefault('strategy', $workshopconfig->strategy);
        $mform->addHelpButton('strategy', 'strategy', 'workshop');

        $options = array();
        for ($i=5; $i>=0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('gradedecimals', 'workshop');
        $mform->addElement('select', 'gradedecimals', $label, $options);
        $mform->setAdvanced('gradedecimals');
        $mform->setDefault('gradedecimals', $workshopconfig->gradedecimals);

        // Submission settings --------------------------------------------------------
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'workshop'));

        $label = get_string('instructauthors', 'workshop');
        $mform->addElement('editor', 'instructauthorseditor', $label, null,
                            workshop::instruction_editors_options($this->context));

        $options = array();
        for ($i=7; $i>=0; $i--) {
            $options[$i] = $i;
        }
        $label = get_string('nattachments', 'workshop');
        $mform->addElement('select', 'nattachments', $label, $options);
        $mform->setDefault('nattachments', 1);

        $options = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $options[0] = get_string('courseuploadlimit') . ' ('.display_size($COURSE->maxbytes).')';
        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'workshop'), $options);
        $mform->setDefault('maxbytes', $workshopconfig->maxbytes);

        $label = get_string('latesubmissions', 'workshop');
        $text = get_string('latesubmissions_desc', 'workshop');
        $mform->addElement('checkbox', 'latesubmissions', $label, $text);
        $mform->addHelpButton('latesubmissions', 'latesubmissions', 'workshop');
        $mform->setAdvanced('latesubmissions');

        // Assessment settings --------------------------------------------------------
        $mform->addElement('header', 'assessmentsettings', get_string('assessmentsettings', 'workshop'));

        $label = get_string('instructreviewers', 'workshop');
        $mform->addElement('editor', 'instructreviewerseditor', $label, null,
                            workshop::instruction_editors_options($this->context));

        $label = get_string('examplesmode', 'workshop');
        $options = workshop::available_example_modes_list();
        $mform->addElement('select', 'examplesmode', $label, $options);
        $mform->setDefault('examplesmode', $workshopconfig->examplesmode);
        $mform->disabledIf('examplesmode', 'useexamples');
        $mform->setAdvanced('examplesmode');

        // Access control -------------------------------------------------------------
        $mform->addElement('header', 'accesscontrol', get_string('accesscontrol', 'workshop'));

        $label = get_string('submissionstart', 'workshop');
        $mform->addElement('date_time_selector', 'submissionstart', $label, array('optional' => true));
        $mform->setAdvanced('submissionstart');

        $label = get_string('submissionend', 'workshop');
        $mform->addElement('date_time_selector', 'submissionend', $label, array('optional' => true));
        $mform->setAdvanced('submissionend');

        $label = get_string('assessmentstart', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentstart', $label, array('optional' => true));
        $mform->setAdvanced('assessmentstart');

        $label = get_string('assessmentend', 'workshop');
        $mform->addElement('date_time_selector', 'assessmentend', $label, array('optional' => true));
        $mform->setAdvanced('assessmentend');

        // Common module settings, Restrict availability, Activity completion etc. ----
        $features = array('groups'=>true, 'groupings'=>true, 'groupmembersonly'=>true,
                'outcomes'=>true, 'gradecat'=>false, 'idnumber'=>false);

        $this->standard_coursemodule_elements();

        // Standard buttons, common to all modules ------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Prepares the form before data are set
     *
     * Additional wysiwyg editor are prepared here, the introeditor is prepared automatically by core
     *
     * @param array $data to be set
     * @return void
     */
    function data_preprocessing(&$data) {
        if ($this->current->instance) {
            // editing an existing workshop - let us prepare the added editor elements (intro done automatically)
            $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            $data['instructauthorseditor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                                'mod_workshop', 'instructauthors', 0,
                                workshop::instruction_editors_options($this->context),
                                $data['instructauthors']);
            $data['instructauthorseditor']['format'] = $data['instructauthorsformat'];
            $data['instructauthorseditor']['itemid'] = $draftitemid;

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            $data['instructreviewerseditor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                                'mod_workshop', 'instructreviewers', 0,
                                workshop::instruction_editors_options($this->context),
                                $data['instructreviewers']);
            $data['instructreviewerseditor']['format'] = $data['instructreviewersformat'];
            $data['instructreviewerseditor']['itemid'] = $draftitemid;
        } else {
            // adding a new workshop instance
            $draftitemid = file_get_submitted_draft_itemid('instructauthors');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructauthors', 0);    // no context yet, itemid not used
            $data['instructauthorseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);

            $draftitemid = file_get_submitted_draft_itemid('instructreviewers');
            file_prepare_draft_area($draftitemid, null, 'mod_workshop', 'instructreviewers', 0);    // no context yet, itemid not used
            $data['instructreviewerseditor'] = array('text' => '', 'format' => editors_get_preferred_format(), 'itemid' => $draftitemid);
        }
    }
}
