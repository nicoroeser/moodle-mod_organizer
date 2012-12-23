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
 * This file contains forms needed to create new appointments for organizer
 *
 * @package    mod
 * @subpackage organizer
 * @copyright  2011 Ivan Šakić <ivan.sakic3@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// required for the form rendering

require_once("$CFG->libdir/formslib.php");

class mod_organizer_slots_edit_form extends moodleform {

    protected function definition() {
        global $PAGE;
        $PAGE->requires->js('/mod/organizer/js/change.js');

        $defaults = $this->_get_defaults();
        $this->_sethiddenfields();
        $this->_addfields($defaults);
        $this->_addbuttons();
        $this->set_data($defaults);
    }

    private function _get_defaults() {
        global $DB;
        $defaults = array();
        $defset = array('teacherid' => false, 'comments' => false, 'location' => false, 'locationlink' => false,
                'maxparticipants' => false, 'availablefrom' => false, 'teachervisible' => false,
                'isanonymous' => false, 'notificationtime' => false);

        $slotids = $this->_customdata['slots'];

        $defaults['now'] = 0;

        foreach ($slotids as $slotid) {
            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));

            if (!isset($defaults['teacherid']) && !$defset['teacherid']) {
                $defaults['teacherid'] = $slot->teacherid;
                $defset['teacherid'] = true;
            } else {
                if (isset($defaults['teacherid']) && $defaults['teacherid'] != $slot->teacherid) {
                    unset($defaults['teacherid']);
                }
            }
            if (!isset($defaults['comments']) && !$defset['comments']) {
                $defaults['comments'] = $slot->comments;
                $defset['comments'] = true;
            } else {
                if (isset($defaults['comments']) && $defaults['comments'] != $slot->comments) {
                    unset($defaults['comments']);
                }
            }
            if (!isset($defaults['location']) && !$defset['location']) {
                $defaults['location'] = $slot->location;
                $defset['location'] = true;
            } else {
                if (isset($defaults['location']) && $defaults['location'] != $slot->location) {
                    unset($defaults['location']);
                }
            }
            if (!isset($defaults['locationlink']) && !$defset['locationlink']) {
                $defaults['locationlink'] = $slot->locationlink;
                $defset['locationlink'] = true;
            } else {
                if (isset($defaults['locationlink']) && $defaults['locationlink'] != $slot->locationlink) {
                    unset($defaults['locationlink']);
                }
            }
            if (!isset($defaults['maxparticipants']) && !$defset['maxparticipants']) {
                $defaults['maxparticipants'] = $slot->maxparticipants;
                $defset['maxparticipants'] = true;
            } else {
                if (isset($defaults['maxparticipants']) && $defaults['maxparticipants'] != $slot->maxparticipants) {
                    unset($defaults['maxparticipants']);
                }
            }
            //*
            if (!isset($defaults['availablefrom']) && !$defset['availablefrom']) {
                $defaults['availablefrom'] = $slot->availablefrom;
                $defset['availablefrom'] = true;
                if ($slot->availablefrom == 0) {
                    $defaults['now'] = 1;
                }
            } else {
                if (isset($defaults['availablefrom']) && $defaults['availablefrom'] != $slot->availablefrom) {
                    unset($defaults['availablefrom']);
                }
            }
            //*/
            if (!isset($defaults['teachervisible']) && !$defset['teachervisible']) {
                $defaults['teachervisible'] = $slot->teachervisible;
                $defset['teachervisible'] = true;
            } else {
                if (isset($defaults['teachervisible']) && $defaults['teachervisible'] != $slot->teachervisible) {
                    unset($defaults['teachervisible']);
                }
            }
            if (!isset($defaults['isanonymous']) && !$defset['isanonymous']) {
                $defaults['isanonymous'] = $slot->isanonymous;
                $defset['isanonymous'] = true;
            } else {
                if (isset($defaults['isanonymous']) && $defaults['isanonymous'] != $slot->isanonymous) {
                    unset($defaults['isanonymous']);
                }
            }
            if (!isset($defaults['notificationtime']) && !$defset['notificationtime']) {
                $defset['notificationtime'] = true;
                $timeunit = $this->_figure_out_unit($slot->notificationtime);
                $defaults['notificationtime']['number'] = $slot->notificationtime / $timeunit;
                $defaults['notificationtime']['timeunit'] = $timeunit;
            } else {
                if (isset($defaults['notificationtime'])
                        && $defaults['notificationtime']['number'] != $slot->notificationtime / $timeunit) {
                    unset($defaults['notificationtime']);
                }
            }
        }

        return $defaults;
    }

    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'editsubmit', get_string('edit_submit', 'organizer'));
        $buttonarray[] = &$mform->createElement('reset', 'editreset', get_string('revert'),
                array('onclick' => 'resetEditForm()'));

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    private function _sethiddenfields() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        // TODO: might cause crashes!
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ACTION);

        $mform->addElement('hidden', 'warningtext1', get_string('warningtext1', 'organizer'));
        $mform->addElement('hidden', 'warningtext2', get_string('warningtext2', 'organizer'));

        for ($i = 0; $i < count($data['slots']); $i++) {
            $mform->addElement('hidden', "slots[$i]", $data['slots'][$i]);
        }
    }

    private function _addfields($defaults) {
        $mform = $this->_form;

        $mform->addElement('header', 'slotdetails', get_string('slotdetails', 'organizer'));

        $teachers = $this->_load_teachers($defaults);
        if (!isset($defaults['teacherid'])) {
            $teachers[-1] = get_string('teacher_unchanged', 'organizer');
        }

        $group = array();
        $group[] = $mform->createElement('select', 'teacherid', get_string('teacher', 'organizer'), $teachers,
                array('onchange' => 'detectChangeSelect(this);', 'group' => null));

        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('teacherid', isset($defaults['teacherid'])));

        $mform->setType('teacherid', PARAM_INT);

        $mform->addGroup($group, '', get_string('teacher', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_teacherid', 0);

        if (!isset($defaults['teacherid'])) {
            $mform->setDefault('teacherid', -1);
        }

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'teachervisible', get_string('teachervisible', 'organizer'),
                null, array('onclick' => 'detectChange(this);', 'group' => null), array(0, 1));

        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('teachervisible', isset($defaults['teachervisible'])));

        $mform->setDefault('teachervisible', 1);
        $mform->addGroup($group, '', get_string('teachervisible', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_teachervisible', 0);

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'isanonymous', get_string('isanonymous', 'organizer'), null,
                array('onclick' => 'detectChange(this);', 'group' => null), array(0, 1));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('isanonymous', isset($defaults['isanonymous'])));

        $mform->setDefault('isanonymous', 0);
        $mform->addGroup($group, '', get_string('isanonymous', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_isanonymous', 0);

        $group = array();
        $group[] = $mform->createElement('text', 'location', get_string('location', 'organizer'),
                array('size' => '64', 'onkeydown' => 'detectChange(this);', 'group' => null));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('location', isset($defaults['location'])));

        $mform->addGroup($group, 'locationgroup', get_string('location', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_location', 0);

        $group = array();
        $group[] = $mform->createElement('text', 'locationlink', get_string('locationlink', 'organizer'),
                array('size' => '64', 'onkeydown' => 'detectChange(this);', 'group' => null));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('locationlink', isset($defaults['locationlink'])));

        $mform->addGroup($group, 'locationlinkgroup', get_string('locationlink', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_locationlink', 0);

        if (!is_group_mode()) {
            $group = array();
            $group[] = $mform->createElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'),
                    array('size' => '3', 'onkeydown' => 'detectChange(this);', 'group' => null));
            $group[] = $mform->createElement('static', '', '',
                    $this->_warning_icon('maxparticipants', isset($defaults['maxparticipants'])));

            $mform->addGroup($group, 'maxparticipantsgroup', get_string('maxparticipants', 'organizer'), SPACING, false);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('maxparticipants', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'maxparticipants', 1);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('maxparticipants', PARAM_INT);
            $mform->setType('mod_maxparticipants', PARAM_INT);
        }

        $now = $defaults['now'];

        $group = array();
        if ($now) {
            $group[] = $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer'),
                    null, array('onchange' => 'detectChangeDuration(this);', 'group' => null, 'disabled' => true));
        } else {
            $group[] = $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer'),
                    null, array('onchange' => 'detectChangeDuration(this);', 'group' => null));
        }
        $group[] = $mform->createElement('static', '', '',
                get_string('relative_deadline_before', 'organizer') . '&nbsp;&nbsp;&nbsp;'
                        . get_string('relative_deadline_now', 'organizer'));
        $group[] = $mform->createElement('checkbox', 'now', get_string('relative_deadline_now', 'organizer'), null,
                array('onchange' => 'detectChange(this);toggleAvailableFrom(this);', 'group' => null));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('availablefrom', isset($defaults['availablefrom'])));

        $mform->setDefault('availablefrom', '');
        $mform->addGroup($group, 'availablefromgroup', get_string('availablefrom', 'organizer'), SPACING, false);

        $availablefromgroup = $mform->getElement('availablefromgroup')->getElements();
        $availablefrom = $availablefromgroup[0]->getElements();
        $availablefrom[1]->removeOption(1);

        $mform->addElement('hidden', 'mod_availablefrom', 0);

        $group = array();
        $group[] = $mform->createElement('duration', 'notificationtime', get_string('notificationtime', 'organizer'),
                null, array('onchange' => 'detectChangeDuration(this);', 'group' => null), array(0, 1));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('notificationtime', isset($defaults['notificationtime'])));

        $mform->setDefault('notificationtime', '');
        $mform->addGroup($group, 'notificationtimegroup', get_string('notificationtime', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_notificationtime', 0);

        $notificationtimegroup = $mform->getElement('notificationtimegroup')->getElements();
        $notificationtime = $notificationtimegroup[0]->getElements();
        $notificationtime[1]->removeOption(1);

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        $group = array();
        $group[] = $mform->createElement('textarea', 'comments', get_string('appointmentcomments', 'organizer'),
                array('wrap' => 'virtual', 'rows' => '10', 'cols' => '60', 'onkeydown' => 'detectChange(this);'));
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('comments', isset($defaults['comments'])));

        $mform->setDefault('comments', '');
        $mform->addGroup($group, '', get_string('appointmentcomments', 'organizer'), SPACING, false);
        $mform->addElement('hidden', 'mod_comments', 0);
    }

    private function _converts_to_int($value) {
        if (is_numeric($value)) {
            if (intval($value) == floatval($value)) {
                return true;
            }
        }
        return false;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['mod_maxparticipants'] != 0
                && (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0)) {
            $errors['maxparticipantsgroup'] = get_string('err_posint', 'organizer');
        }

        if ($data['mod_notificationtime'] != 0
                && (!$this->_converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0)) {
            $errors['notificationtimegroup'] = get_string('err_posint', 'organizer');
        }

        if ($data['mod_location'] != 0 && (!isset($data['location']) || $data['location'] === '')) {
            $errors['locationgroup'] = get_string('err_location', 'organizer');
        }

        return $errors;
    }

    private function _load_teachers() {
        list($cm, $course, $organizer, $context) = get_course_module_data();

        $teachersraw = get_users_by_capability($context, 'mod/organizer:addslots');

        $teachers = array();
        foreach ($teachersraw as $teacher) {
            $a = new stdClass();
            $a->firstname = $teacher->firstname;
            $a->lastname = $teacher->lastname;
            $name = get_string('fullname_template', 'organizer', $a) . " ({$teacher->email})";
            $teachers[$teacher->id] = $name;
        }

        return $teachers;
    }

    private function _warning_icon($name, $noshow = false) {
        global $CFG;
        if (!$noshow) {
            $warningname = $name . '_warning';
            $text = get_string('warningtext1', 'organizer');
            $columnicon = '<img src="' . $CFG->wwwroot . '/mod/organizer/pix/warning.png" title="' . $text . '" alt="'
                    . $text . '" name="' . $warningname . '" />';
            return $columnicon;
        } else {
            return '';
        }
    }

    private function _figure_out_unit($time) {
        if ($time % 86400 == 0) {
            return 86400;
        } else if ($time % 3600 == 0) {
            return 3600;
        } else if ($time % 60 == 0) {
            return 60;
        } else {
            return 1;
        }
    }
}