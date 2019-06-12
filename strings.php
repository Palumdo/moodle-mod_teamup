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
 * String used in javascript loaded by ajax.
 *
 * @package    mod_teamup
 * @copyright  UCLouvain
 * @author     Palumbo Dominique (UCLouvain)
 */
require_once(dirname(__FILE__).'/../../config.php');

echo '{"trans":[';
echo '"'.get_string("oneOption",        "mod_teamup").'",';
echo '"'.get_string("anyOption",        "mod_teamup").'",';
echo '"'.get_string("atleastoneOption", "mod_teamup").'",';
echo '"'.get_string("twoOption",        "mod_teamup").'",';
echo '"'.get_string("threeOption",      "mod_teamup").'",';
echo '"'.get_string("fourOption",       "mod_teamup").'",';
echo '"'.get_string("fiveOption",       "mod_teamup").'",';
echo '"'.get_string("jserror01",        "mod_teamup").'",';
echo '"'.get_string("jserror02",        "mod_teamup").'",';
echo '"'.get_string("jserror03",        "mod_teamup").'",';
echo '"'.get_string("jserror04",        "mod_teamup").'",';
echo '"'.get_string("pleasequestion",   "mod_teamup").'",';
echo '"'.get_string("pleaseatleastonequestion",   "mod_teamup").'",';
echo '"'.get_string("saving",           "mod_teamup").'",';
echo '"'.get_string("saved",            "mod_teamup").'"';
echo ']}';

