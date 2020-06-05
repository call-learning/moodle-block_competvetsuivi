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
 * Compet Vet Suivi Block
 *
 * @package     block_competvetsuivi
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_competvetsuivi\matrix\matrix;
use local_competvetsuivi\ueutils;
use local_competvetsuivi\utils;

defined('MOODLE_INTERNAL') || die();
/**
 * Compet Vet Suivi Block
 *
 * @package     block_competvetsuivi
 * @copyright   2019 CALL Learning <laurent@call-learning.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_competvetsuivi extends block_base {

    /**
     * General intialiser
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_competvetsuivi');
    }

    /**
     * Get block content : the progress graph
     * @return stdClass|stdObject|null
     * @throws coding_exception
     * @throws dml_exception
     */
    public function get_content() {
        global $USER;

        $this->content = new \stdClass();
        $this->content->text = '';

        $user = $USER;
        if (has_capability('block/competvetsuivi:canseeother', context_system::instance(), $USER)) {
            $foruser = optional_param('foruser', 0, PARAM_INT);
            if ($foruser) {
                $user = \core_user::get_user($foruser);
            }
        }
        $matrixid = utils::get_matrixid_for_user($user->id);

        if ($matrixid) {

            $compidparamname = local_competvetsuivi\renderable\competency_progress_overview::PARAM_COMPID;
            $currentcompid = optional_param($compidparamname, 0, PARAM_INT);

            $matrix = new \local_competvetsuivi\matrix\matrix($matrixid);
            $userdata = local_competvetsuivi\userdata::get_user_data($user->email);
            $matrix->load_data();
            $strandlist = array(matrix::MATRIX_COMP_TYPE_KNOWLEDGE, matrix::MATRIX_COMP_TYPE_ABILITY);
            $lastseenue = local_competvetsuivi\userdata::get_user_last_ue_name($user->email);
            $currentsemester = ueutils::get_current_semester_index($lastseenue, $matrix);
            $currentcomp = null;
            if ($currentcompid) {
                $currentcomp = $matrix->comp[$currentcompid];
            }

            $progressoverview = new \local_competvetsuivi\renderable\competency_progress_overview(
                    $currentcomp,
                    $matrix,
                    $strandlist,
                    $userdata,
                    $currentsemester,
                    $user->id
            );
            $renderer = $this->page->get_renderer('local_competvetsuivi');
            $this->content->text = $renderer->render($progressoverview);
        } else {
            $this->content->text = get_string('userhasnomatrix', 'block_competvetsuivi');
        }

        return $this->content;

    }

    /**
     * Make sure the block is only inserted in the dashboard
     * @return array|bool[]
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * No multiple instances
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * No headers
     * @return bool
     */
    public function hide_header() {
        return true;
    }
}
