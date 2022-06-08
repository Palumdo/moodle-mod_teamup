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
 *
 *
 * @package    mod_teamup fork of mod_teambuilder
 * @copyright  UNSW
 * @author     UNSW
 * @author     Adam Olley
 * modified by  Palumbo Dominique (UCLouvain)
 * modification
    The and & or operations was replaced by

    -Group similar individuals = Form groups whose members are similar to
    defined criteria. Creation of homogeneous groups. Applied to discrete values
    , with no obligation whatsoever numerical.

    -Disperse similar individuals = Distribute qualifying students across groups.
    Applied to discrete values, with no obligation whatsoever numerical.

    -Avoid minorities = Divide students so that at least two students sharing a
    criterion are in the same group (especially for minorities).
    Applied to discrete values, with no obligation whatsoever numerical.

    -Balancing Level = Create groups that are \"right\", whose total forces are
    similar in all groups (usually based on academic results). Applied to
    numerical values (continuous and discrete).

    These rules to create group was definied in the GROUPENG python project.
    GroupEng was written by Thomas Dimiduk while a grad student
      at Harvard (PhD 2016) with Kathryn Dimiduk of Cornell Engineering's
      James McCormick Family Teaching Excellence Institute.
    And apply to teambuilder to become Team Up.
 */
define(['jquery', 'jqueryui', 'core/str'], function($, jqui, str) {
    var maxwidth = 85; // Maximum width of a studentbox in px. initialised as the minimum possible width.
    var initstate; // Initial state of unassigned box. used to restore when syncing between model/view.

    // Teams are identified by their index in these arrays.
    // Contains arrays of students assigned to that team.
    var teamAssignments = [];
    var gteamAssignmentsWeight = [];
    var teamNames = []; // Contains the names of teams.
    // Contains information for rendering students.
    var selectedStudents = [];
    var preventStudentClick = 0;
    var preventDeleteClick = false;
    var strings = {};
    var criterionHTML = '';

    var aDontMove = []; // List of student that cannot move because they meet criteria.
    var aCommand = []; // List of the command enter by the teatcher to create the group.
    var aTeamMaxAgg = []; // Higher number of an answer in a team for a question in case of aggregate...
    var aTeamTrash = [];


    var teamAvg = []; // All related to balance.
    var average = 0;
    var total = 0;
    var ecartType = 0;

    var teamHistory = []; // Futur use managmeent of history or state save.
    var teamGood = 0;

    var bCreated = false;
    var bCriteria = false;
    var bAgg = false;
    var qAgg = -1;

    // *****************************************************************************************************************************
    // Put translation from server to javascript strings
    // *****************************************************************************************************************************
    var buildstrings = function(s) {
        // Strings used for internalization.
        strings.criterionquestion = s[0];
        strings.distribute = s[1];
        strings.aggregate = s[2];
        strings.cluster = s[3];
        strings.balance = s[4];
        strings.createteams = s[5];
        strings.analyzeclustercriterion = s[6];
        strings.analyzeclusterwarning = s[7];
        strings.analyzeclustersuccess = s[8];
        strings.analyzeaggregatewarning = s[9];
        strings.noanswer = s[10];
        strings.analyzedistributesuccess = s[11];
        strings.analyzedistributewarning = s[12];
        strings.analyzedistributecriterion = s[13];
        strings.analyzebalancewarning = s[14];
        strings.total = s[15];
        strings.average = s[16];
        strings.standarddeviation = s[17];
        strings.teamupsuccess = s[18];
        strings.teamupwarning = s[19];
        strings.averagesuccess = s[20];
        strings.averagewarning = s[21];
        strings.bornes = s[22];
        strings.teamupsuccessnbr = s[23];
        strings.distributeLabel = s[24];
        strings.aggregateLabel = s[25];
        strings.clusterLabel = s[26];
        strings.balanceLabel = s[27];
        strings.distributionMode = s[28];
        strings.question = s[29];
        strings.groupTitle = s[30];
        strings.nbGroupSuccess = s[31];
        strings.nbStudent = s[32];
        strings.analyzeaggregatewarningOK = s[33];
        strings.serie = s[34];

        // Small dialog to select criterion.
        // boolOper is kept for historical reason. Inheritance from the teambuilder original module.
        criterionHTML = '<div  style="position: relative;" class="criterionWrapper sortable">';
        criterionHTML += '  <div class="criterion ui-corner-all">';
        criterionHTML += '    <div class="boolOper" style="display:none;">' + strings.distributionMode + '</div>'; // Moved top.
        criterionHTML += '    <div class="operator">' + strings.distributionMode
                                    + ' : <select onChange="$(this).parent().prev().html(this.value);" style="margin-top:4px;">';
        criterionHTML += '      <option  value="' + strings.aggregate + '">' + strings.aggregateLabel + '</option>';
        criterionHTML += '      <option  value="' + strings.distribute + '">' + strings.distributeLabel + '</option>';
        criterionHTML += '      <option  value="' + strings.cluster + '">' + strings.clusterLabel + '</option>';
        criterionHTML += '      <option  value="' + strings.balance + '">' + strings.balanceLabel + '</option>';
        criterionHTML += '    </select></div>';
        criterionHTML += '    <div class="criterionDelete"></div>';
        criterionHTML += '    ' + strings.question + ' : <select class="questions"></select>';
        criterionHTML += '    <div class="answers"></div>';
        criterionHTML += '    <div class="qreport"></div>';
        criterionHTML += '  </div>';
        criterionHTML += '</div>';
    };
    // *****************************************************************************************************************************
    // Initiailize global variables and UI
    // *****************************************************************************************************************************
    var setup = function() {
        $(".stepper").each(function() {

            var spin = "<input id='nbteam' type='number' min='1' style='width:60px;height:26px;margin:10px 5px 0 0;' value='1'>";
            var reload = "<span id='nbstudentsdsp'> / " + $(".student").length + '(' + Math.ceil($(".student").length
                            / parseInt($("#nbteam").val())) + ')' + "</span>";
            var buttons = $(spin + reload);

            $(this).empty();
            $('#placeithere').append(buttons);
            $('#nbstudentsdsp').html(' / ' + $(".student").length + '(' + Math.ceil($(".student").length
                / parseInt($("#nbteam").val())) + ')');

            $("#btcreate").click(function() {
                $('#protectAll').modal('show');
                var x = $('#nbteam').val();
                setTimeout(function() {updateTeams(x);$('#protectAll').modal('hide');}, 250);
            });
        });

        $("#id_namingscheme").change(function() {
            var x = $('#nbteam').val();
            updateTeams(x);
        });


        $("#nbteam").change(function() {
            var x = $('#nbteam').val();
            updateTeams(x);
        });

        $('legend').click(function() {
            $(this).nextAll('div').toggle();
            $(this).hasClass('myHide') ? ($(this).attr("class", "myShow")) : ($(this).attr("class", "myHide"));
        });

        // Compute the max width of a student.
        $(".student").each(function() {
            if ($(this).width() > maxwidth) {
                maxwidth = $(this).width();
            }
        });

        // Apply the max width to all students
        $(".student").each(function() {
            $(this).width(maxwidth);
        });

        $("#unassigned").on("click", ".studentdel", function(evt) {
            if (preventDeleteClick) {
                return;
            }
            evt.stopPropagation();
            $('.studentResponse').remove();
            var studentID = /studentdel-(\d+)/.exec($(this).attr("id"));
            var id = studentID[1];
            aTeamTrash[id] = students[id];
            responses[id] = [];
            delete students[id];
            $('#student-' + id).remove();
            $('#nbstudentsdsp').html(' / ' + $(".student").length + '(' + Math.ceil($(".student").length
                                / parseInt($("#nbteam").val())) + ')');
            initstate = $("#unassigned").html();

            $(".criterionWrapper").each(function() {
                updateRunningCounter($(this).closest(".criterionWrapper").children(".criterion"));
            });
        });

        // Response of the student when user click on it.
        $("#unassigned, #teams").on("mouseup", ".student", function(evt) {
            if (preventStudentClick) {
                preventStudentClick = false;
                return;
            }

            var details = $('<div class="studentResponse ui-corner-all"></div>');
            var studentID = /student-(\d+)/.exec($(this).attr("id"));
            var myResponses = responses[studentID[1]];

            if (myResponses) {
                var detailsTable = $('<table class="mod-teamup-table"></table>');
                details.append(detailsTable);
                for (var i in questions) {
                    var q = questions[i];
                    var qr = []; // Question responses.
                    for (var j in q.answers) {
                        var a = q.answers[j];
                        if ($.inArray(parseInt(a.id), myResponses) != -1) {
                            qr.push(a.answer);
                        }
                    }
                    var row = $('<tr><th scope="row">' + q.question + '</th><td>' + qr.join("<br/>") + '</td></tr>');
                    detailsTable.append(row);
                }
            } else {
                details.html(strings.noanswer);
            }

            $(document.body).append(details);
            details.css("left", evt.pageX);
            details.css("top", evt.pageY);

            var mdevent = function(evt) {
                if (evt.target != details.get(0)) {
                details.remove();
                $(document).unbind('mousedown', mdevent);
                }
            };
            $(document).mousedown(mdevent);

            var moto; // Mouseover timeout.
            var moevent = function(evt) {
                var stresponse = $(evt.target).closest(".studentResponse");
                if (evt.target != this && (stresponse.length == 0 || (stresponse.length > 0
                    && details.get(0) != stresponse.get(0)))) {
                    if (moto == undefined) {
                        moto = setTimeout(function() { details.remove();}, 500);
                    }
                } else {
                    if (moto) {
                        clearTimeout(moto);
                    }
                }
            };
            $(document).mouseover(moevent);
        }); // End display student answer on click.

        // Rename team on double click.
        $("#teams").on("dblclick", ".team > h2", function(evt) {
            var teamHeader = $(evt.target);
            var teamName = teamHeader.html();
            var teamTextBox = $('<input type="text" value="' + teamName + '" />');

            teamTextBox.css('font-size', teamHeader.css('font-size'));
            teamTextBox.width(teamHeader.width());
            teamTextBox.height(teamHeader.height());
            teamTextBox.css('border-width', '0px');

            function textBoxDone() {
                var teamHeader = $("<h2>" + teamTextBox.val() + "</h2>");
                teamHeader.width(teamTextBox.width());
                teamHeader.height(teamTextBox.height());
                teamTextBox.replaceWith(teamHeader);
                teamNames[teamHeader.parent().index()] = teamHeader.html();
            }

            // Conditionally attach the textBox. Done event if you click outside the textbox.
            var mdevent = function(evt) {
                if (evt.target != teamTextBox.get(0)) {
                    textBoxDone();
                    $(document).unbind('mousedown', mdevent);
                }
            };

            $(document).mousedown(mdevent);
            // If you press return.
            teamTextBox.keypress(function(evt) {
                if (evt.keyCode == 13) { // Return character.
                    textBoxDone();
                    $(document).unbind('mousedown', mdevent);
                }
            });

            teamHeader.replaceWith(teamTextBox);
            teamTextBox.focus();
            teamTextBox.select();
        });

        $('#nbteam').change(function() {
            $('#nbstudentsdsp').html(' / ' + $(".student").length + '(' + Math.ceil($(".student").length
                                    / parseInt($("#nbteam").val())) + ')');
            $(".criterionWrapper").each(function() {
                updateRunningCounter($(this).closest(".criterionWrapper").children(".criterion"));
            });
        });

        $('#series').change(function() {
            window.location.search = removeParam('group', window.location.search) + '&group=' + $('#series').val();
        });

        initstate = $("#unassigned").html();
        updateTeams(1);
        addNewCriterion();
        $("#predicate").sortable();
        buttonManager();
    };
    // *****************************************************************************************************************************
    // Remove param from url
    // *****************************************************************************************************************************
    var removeParam = function(key, sourceURL) {
        var rtn = sourceURL.split("?")[0],
            param,
            paramsArr = [],
            queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
        if (queryString !== "") {
            paramsArr = queryString.split("&");
            for (var i = paramsArr.length - 1; i >= 0; i -= 1) {
                param = paramsArr[i].split("=")[0];
                if (param === key) {
                    paramsArr.splice(i, 1);
                }
            }
            rtn = rtn + "?" + paramsArr.join("&");
        }
        return rtn;
    };
    // *****************************************************************************************************************************
    // Update after manual modification of the teams
    // *****************************************************************************************************************************
    var updateTeams = function(numTeams) {
        synchroniseViewToModel();
        var aTeams = ["A-Team", "Agent Carter", "Agents of S.H.I.E.L.D.", "Akatsuki", "All-Star Squadron", "Alpha Flight",
                      "Angel Investigations", "Aqua Teen Hunger Force", "Arabian Knights", "Archer and Armstrong", "Armorines",
                      "Arrancar", "Arrow", "Astro Boy", "Astro City", "Autobots", "Avengers", "Batman", "Battletoads", "Ben 10",
                      "Beware the Batman", "Big Hero 6", "Biker Mice from Mars", "Bionic Six", "Bionic Woman", "Birds of Prey",
                      "Black Scorpion", "Blood Syndicate", "Blue Falcon", "Champions", "Chouseishin", "Crystal Gems", "Darkstars",
                      "Defenders", "Dick Tracy", "Digvbc", "Doom Patrol", "Drak Pack", "Elementals", "Espada 10", "Excalibur",
                      "Experts of Justice", "Fairy Tail Guild", "Fantastic 4", "Fantastic Force", "Fantastic Four",
                      "Fearless Defenders", "Femforce", "Flash", "Flash Gordon", "Force Works", "Forever People",
                      "Freedom Fighters", "Freedom Force", "Freedom Phalanx", "Frenetic Five", "Future Foundation", "Galaxy Angel",
                      "Galaxy Trio", "Gatchaman", "Gatchaman Crowds", "Generation X", "Gen¹³", "Gotei 13", "Gotham",
                      "Green Lantern Corps", "H.A.R.D.Corps", "Harbingers", "Hero Alliance", "Hero Association", "Heroes",
                      "Heroes for Hire", "Inferior Five", "Infinity Inc.", "Inhumans", "Invaders", "JLA", "Jake 2.0", "Jedi",
                      "Jessica Jones", "Jetman", "Justice League", "Justice Machine", "Kekkaishi", "Kickers Inc.", "Kiss (band)",
                      "Knight Sabers", "Lady Blackhawk", "Extraordinary Gentlemen", "Legends of Tomorrow", "Legion of Superheroes",
                      "Libra", "Lieutenant Marvels", "Lois and Clark", "Lone Ranger", "Lone Ranger Rides Again",
                      "Magic Knight Rayearth", "Manhattan Clan", "Manimal", "Marvel Family", "Marvel Knights", "S.H.I.E.L.D.",
                      "Masters of the Universe", "Men in Black", "Metal Men", "Mighty Crusaders", "Mighty Mouse", "Misfits",
                      "Misfits of Science", "Mutant X", "Mystery Men", "Neo-Knights", "Nextwave", "Nightman", "Nova Corps",
                      "Omega Men", "Overwatch", "Planetary", "Power Pack", "Power Rangers", "Preacher", "Psi-Force",
                      "Ronin Warriors", "Runaways", "S.H.I.E.L.D.", "SWAT Kats", "Sailor Senshi", "Sailor Senshi",
                      "Samurai Pizza Cats", "Sarah Solano", "Secret Avengers", "Secret Six", "Section 8", "Sentinels of Magic",
                      "Seven Soldiers of Victory", "Shadowpact", "Shi'ar Imperial Guard", "Shinigami", "Sign Gene", "SilverHawks",
                      "Sky High", "Sora", "Sovereign Seven", "Spectral Knights", "Speedracer", "Spider-Friends", "Squadron Supreme",
                      "Squadron of Justice", "Stormwatch", "Stormwatch", "Straw Hat Pirates", "Street Sharks", "Suicide Squad",
                      "Super Friends", "Super Power Friends", "Super Sentai", "Superboy", "Superfriends", "Supergirl",
                      "Superhuman Samurai", "Superman", "T.H.U.N.D.E.R. Agents", "Team 7", "Team Avatar", "Team Zenith",
                      "Teen Force", "Teen Titans", "Teenage Mutant Ninja Turtles", "Terrific Three", "The Aquabats", "The Atomics",
                      "The Authority", "The Avengers", "The Beetleborgs", "The Care Bears", "The Centurions", "The DNAgents",
                      "The Elite", "The End League", "The Galaxy Rangers", "The Greatest American Hero", "The Herculoids",
                      "The Impossibles", "The Incarnations", "The Incredible Hulk", "The Incredibles", "The Justice Friends",
                      "The Legion of Net.Heroes", "The Loonatics", "The Mighty Ducks", "The Mighty Heroes", "The N Team",
                      "The New League of Heroes", "The New Wave", "The Order of the Phoenix", "The Others", "The Outsiders",
                      "The Powerpuff Girls", "The Six Million Dollar Man", "The Specials", "The Spectacular Spider-man",
                      "The Spider’s Web", "The Strangers", "The Super Capers", "The Super Globetrotters", "The Tick",
                      "The Tomorrow People", "ThunderCats", "Thunderbolts", "Time Squad", "Tokyo Mew Mew", "Toxic Crusaders",
                      "Turbo-Man", "Ultimates", "Ultraforce", "Uncanny X-Men", "Underdog", "Underfist", "VR Troopers", "Watchmen",
                      "West Coast Avengers", "Wetworks", "Wild C.A.T.s", "Wolverine and the X-Men", "Wonder Woman", "X-Factor",
                      "X-Force", "X-Men", "X-Men 2099", "X-Statix", "Yatterman", "Young Allies", "Young Avengers", "Young Justice",
                      "Z Warriors"];

        if ($('#id_namingscheme').val().indexOf('*') > -1) {
            if (teamAssignments.length > numTeams) {
                teamAssignments = teamAssignments.slice(0, numTeams);
            }

            if (teamNames.length > numTeams) {
                teamNames = teamNames.slice(0, numTeams);
            }

            for (var i = 0; i < numTeams; i++) {
                var sTeamName = $('#id_namingscheme').val();
                var sFullTeamName = sTeamName;
                if (sTeamName.indexOf('#') > -1) {
                    sFullTeamName = sTeamName.replace('#', i + 1);
                } else if (sTeamName.indexOf('@') > -1) {
                    var myAlpha = '';
                    var numChar = i;
                    do {
                        numChar = numChar - 26;
                        if (numChar >= 0) {
                            myAlpha += String.fromCharCode('A'.charCodeAt(0) + 0);
                        } else {
                            myAlpha += String.fromCharCode('A'.charCodeAt(0) + numChar + 26);
                        }
                    } while (numChar >= 0);

                    sFullTeamName = sTeamName.replace('@', myAlpha);
                }
                if ($("#sumbox_" + i).data("name") === undefined || $("#sumbox_" + i).data("name").indexOf('dataname') > -1 ) {
                    sFullTeamName = sFullTeamName.replace('*', '');
                } else {
                    sFullTeamName = sFullTeamName.replace('*', $("#sumbox_" + i).data("name"));
                }
                teamNames[i] = sFullTeamName;
            }
        } else {
            // Slice off the end of the teams array if needed.
            if (teamAssignments.length > numTeams) {
                teamAssignments = teamAssignments.slice(0, numTeams);
            }

            // Slice off the end of the names array if needed.
            if (teamNames.length > numTeams) {
                teamNames = teamNames.slice(0, numTeams);
            } else if (teamNames.length < numTeams) {

                var aRand = [];
                for (i = 0; i < aTeams.length; i++) {
                    aRand[i] = i;
                }
                aRand = shuffle(aRand);

                for (i = teamNames.length - 1; i < numTeams; i++) {
                    if ($('#id_namingscheme').val() != '') {
                        sTeamName = $('#id_namingscheme').val();
                    } else {
                        sTeamName = strings.groupTitle;
                    }
                    if (sTeamName.indexOf('#') > -1) {
                        sFullTeamName = sTeamName.replace('#', i + 1);
                    } else if (sTeamName.indexOf('@') > -1) {
                        var myAlpha = '';
                        var numChar = i;
                        do {
                            numChar = numChar - 26;
                            if (numChar >= 0) {
                                myAlpha += String.fromCharCode('A'.charCodeAt(0) + 0);
                            } else {
                                myAlpha += String.fromCharCode('A'.charCodeAt(0) + numChar + 26);
                            }
                        } while (numChar >= 0);

                        sFullTeamName = sTeamName.replace('@', myAlpha);
                    } else if (sTeamName.indexOf('$') > -1) {
                        sFullTeamName = sTeamName.replace('$', aTeams[aRand[i]]);
                    } else {
                        sFullTeamName = sTeamName + (i + 1);
                    }
                    teamNames[i] = sFullTeamName;
                }
            }
        }
        synchroniseModelToView();
    };
    // *****************************************************************************************************************************
    // Reset team variable and synchronise the view
    // *****************************************************************************************************************************
    var resetTeams = function() {
        selectedStudents = [];
        teamAvg = [];
        aTeamMaxAgg = [];
        bCreated = false; // Teams preview reset

        for (var i = 0; i < teamAssignments.length; i++) {
            teamAssignments[i] = [];
        }
        preventDeleteClick  = false;
        synchroniseModelToView();
    };
    // *****************************************************************************************************************************
    // This function give information about questions selected (criterion) and action (aggregate, balance, cluster,...)
    // these information,  should help to see if it's realistic to use it and if they'll have potential problem
    // the info is didplayed at the bottom of the criterion box
    // *****************************************************************************************************************************
    var updateRunningCounter = function(criterion) {
        var c = getCriterionObjectFromView(criterion);
        var boolOp = c.boolOper;
        var nbteam = $('#nbteam').val();
        var ctr = 0;
        var strText = '';

        switch(boolOp) { // CLUSTER
            case strings.cluster:
                for (var i in students) {
                    if (responses[i] === false) { // Don't count students with no response.
                        continue;
                    }
                    if (studentMeetsCriterion(i, c)) {
                        ctr++;
                    }
                }

                strText = strText + strings.analyzeclustercriterion.replace('{nbstudent}',ctr).replace('{nbteam}', nbteam);
                if ((ctr / nbteam) < 2) {
                    strText = strText + strings.analyzeclusterwarning;
                } else {
                    strText = strText + strings.analyzeclustersuccess;
                }
            break;
            // AGGREGATE
            case strings.aggregate:
                var lctr = 0;
                for (var i in students) {
                    if (responses[i] === false) { // Don't count students with no response.
                        continue;
                    }
                    ctr++;
                }

                aAnswer = [];
                var studAvgByTeam = Math.ceil(ctr / nbteam);

                for (var a in questions[c.question].answers) {
                    aAnswer.push(a);
                }
                // Count the number of student that have answers.
                for (var j = 0; j < aAnswer.length; j++) {
                    lctr = 0;
                    for (i in students) {
                        if (responses[i] === false) { // Don't count students with no response.
                            continue;
                        }
                        if (responses[i].indexOf(parseInt(aAnswer[j])) != -1) {
                            lctr++;
                        }
                    }
                    var reste = (lctr % studAvgByTeam);
                    var nbAggTeam = parseInt(lctr / studAvgByTeam);

                    var color = '';
                    if (reste != 0) {
                        color = 'red';
                        strText = strText + strings.analyzeaggregatewarning.replace('{color}', color)
                            .replace('{answer}', questions[c.question].answers[aAnswer[j]].answer)
                            .replace('{nbstudent}', lctr).replace('{reste}', reste)
                            .replace('{nbgroup}', nbAggTeam)
                            .replace('{nbstudentgroup}', studAvgByTeam)
                            .replace('{reste}', reste);
                    } else {
                        color='green';
                        strText = strText + strings.analyzeaggregatewarningOK.replace('{color}', color)
                            .replace('{answer}', questions[c.question].answers[aAnswer[j]].answer)
                            .replace('{nbstudent}', lctr).replace('{reste}', reste)
                            .replace('{nbgroup}', nbAggTeam)
                            .replace('{nbstudentgroup}', studAvgByTeam).replace('{reste}', reste);
                    }
                }
                break;
            // DISTRIBUTE
            case strings.distribute:
                for (i in students) {
                    if (responses[i] === false) { // Don't count students with no response.
                        continue;
                    }
                    ctr++;
                }

                aAnswer = [];
                aAnswer = c.answers;
                for (var j = 0; j < aAnswer.length; j++) {
                    ctr = 0;
                    for (i in students) {
                        if (responses[i] === false) { // Don't count students with no response.
                            continue;
                        }
                        c.answers = [aAnswer[j]];
                        if (studentMeetsCriterion(i, c)) {
                            ctr++;
                        }
                    }

                    if (ctr >= nbteam) {
                        status = strings.analyzedistributesuccess.replace('{nbteam}', nbteam);
                    } else {
                        status = strings.analyzedistributewarning.replace('{nbteam}', nbteam);
                    }
                    strText = strText +
                        strings.analyzedistributecriterion.replace('{answer}', questions[c.question].answers[aAnswer[j]].answer)
                        .replace('{nbstudent}', ctr)
                        .replace('{status}', status);
                }
                break;

            // BALANCE
            case strings.balance:
                var strText = '';
                var aAnswer = [];
                for (a in questions[c.question].answers) {
                    aAnswer.push(a);
                }
                c.answers = aAnswer;
                var nSum = 0;
                var aValues = [];
                for (i in students) {
                    var aRes = responses[i];
                    if (responses[i] === false) { // Don't count students with no response.
                        continue;
                    } else {
                        if (studentMeetsCriterion(i, c)) {
                            ctr++;
                            for (j = 0; j < aAnswer.length; j++) {
                                if (aRes.indexOf(parseInt(aAnswer[j], 10)) !== -1) {
                                    break;
                                }
                            }
                            nSum = nSum + parseInt(questions[c.question].answers[aAnswer[j]].answer);
                            aValues.push(parseInt(questions[c.question].answers[aAnswer[j]].answer));
                            if ($.isNumeric(questions[c.question].answers[aAnswer[j]].answer) == false) {
                                strText = strings.analyzebalancewarning;
                                ctr = 0;
                                break;
                            }
                        }
                    }
                }
                if (strText == '') { // If they don't have a warning of value, display all info on balance
                    strText = strText + '<br>' + strings.total +' : <b>' + nSum + '</b>';
                    var average = nSum / ctr;
                    strText = strText + '<br>' + strings.average + ' : <b>' + Math.ceil(nSum/ctr) + '</b>';
                    var ecart = 0;
                    for (i = 0; i < aValues.length; i++) {
                        ecart += Math.pow(aValues[i] - average, 2);
                    }
                    strText = strText + '<br>' + strings.standarddeviation + ' : <b>' + Math.ceil(Math.sqrt(ecart / ctr)) + '</b>';
                }
                break;
        }

        if (strText.indexOf('<br>') == 0) {
            strText = strText.slice(4);
        }
        criterion.find(".qreport").html(strText);
        criterion.find(".qreport").css("text-align", "left");

        buttonManager();
    };
    // *****************************************************************************************************************************
    // Return the list of responses for a question to put it in a list
    // *****************************************************************************************************************************
    var getResponsesForQuestion = function(questionID, responseContainer) {
        var q = questions[questionID];
        var ul = $("<ul></ul>");
        for (a in q.answers) {
            answer = q.answers[a];
            ul.append('<li><input style="margin:0" type="checkbox" value="' + answer.id + '"> ' + answer.answer + '</input></li>');
        }

        responseContainer.empty().append(ul);

        responseContainer.closest(".criterionWrapper").find("ul input,select.oper").change(function() {
            updateRunningCounter($(this).closest(".criterionWrapper").children(".criterion"));
        });
    };
    // *****************************************************************************************************************************
    // Add a new criterion box with the data and handler
    // *****************************************************************************************************************************
    var addNewCriterion = function() {
        if (!$("#predicate").length) {
            return;
        }
        var criterion = $(criterionHTML);

        // Insert the question data.
        // Questions is defined in the document (by PHP).
        for (var i in questions) {
            var q = questions[i];
            criterion.find(".questions").append('<option value="' + q.id + '">' + q.question.substr(0, 120) + '</option>');
        }

        // Add our behaviours.
        // Question select behaviour.
        criterion.find(".questions").change(function() {
            getResponsesForQuestion(this.value, $(this).nextAll(".answers:first"));
            if (questions[this.value]['type'] == 'one') {
                $(this).next(".oper").children("[value='all']").remove();
            } else {
                $(this).nextAll(".oper:first").children("[value='none']").before('<option value="all">all</option>');
            }
            updateRunningCounter($(this).closest(".criterionWrapper").children(".criterion"));
        });
        criterion.find(".questions *:selected").change();

        // Delete button behaviours.
        criterion.find(".criterion").hover(function() {
            $(this).children('.criterionDelete').fadeIn(100);
        }, function() {
            $(this).children('.criterionDelete').fadeOut(100);
        });

        criterion.find('.criterionDelete').click(function() {
            $(this).hide();
            $(this).closest('.criterionWrapper').slideUp(300, function() {
                $(this).remove();
                buttonManager();
            });
        });

        // Instead of managing the input select for operator. It's the modification of the old field from teambuilder that is used.
        criterion.on('DOMSubtreeModified', '.boolOper', function() {
            var oper = $(this).html();
            if (oper == strings.balance || oper == strings.aggregate) {
                $(this).next().next().next().next().css( "display", "none" );
            } else {
                $(this).next().next().next().next().css( "display", "" );
            }

            // Select the question where balance is possible
            if (oper == strings.balance) {
                criterion.find(".questions").val(getBalanceQuestion());
                criterion.find(".questions").change();
            }

            criterion.find(".qreport").html('');

            updateRunningCounter($(this).closest(".criterionWrapper").children(".criterion"));
        });
        // Show the previously hidden boolOper.
        $("#predicate").append(criterion);
        criterion.slideDown(300);
        criterion.find(".boolOper").html('');
        criterion.find(".boolOper").html(strings.aggregate);
    };
    // *****************************************************************************************************************************
    // Synchronize the view to model (both are in javascript...)
    // loop through student div to generate teamAssignments array
    // *****************************************************************************************************************************
    var synchroniseViewToModel = function() {
        // First clear out our model.
        teamAssignments = [];
        $(".team").each(function() {
            var teamDiv = $(this);
            var teamIndex = $(this).index();
            var assignments = [];
            teamDiv.find(".student").each(function() {
                var studentID = /student-(\d+)/.exec($(this).attr("id"));
                assignments.push(studentID[1]); // Slot 1 contains the group we want.
            });
            teamAssignments[teamIndex] = assignments;
        });

        if (preventDeleteClick == true) {
            $(".studentdel").hide();
        } else {
            $(".studentdel").show();
        }
    };
    // *****************************************************************************************************************************
    // Synchronize the model to view (both are in javascript...)
    // the array teamAssignments will become divs in teams(the summary will be generated and also a table with all students answers)
    // *****************************************************************************************************************************
    var synchroniseModelToView = function() {
        // Reset our view.
        $("#unassigned").html(initstate);
        $("#teams").empty();

        // Create our team views.
        for (var i = 0; i < teamNames.length; i++) {
            var teamDiv = $('<div class="team" id="team-' + i + '" title ="' + teamNames[i] + '" />');
            if ($('#id_namingscheme').val().indexOf('$') > -1) {
                teamDiv.append("<h2 style='height:40px;'>" + teamNames[i] + "</h2>");
            } else {
                teamDiv.append("<h2>" + teamNames[i] + "</h2>");
            }
            teamDiv.width(maxwidth + 20 + 10);
            teamDiv.append('<div class="sortable"></div>');
            $("#teams").append(teamDiv);
        }

        // Get our sortable states happening.
        var sortdict = {
            connectWith: ".sortable",
            start: function() {
                preventStudentClick = true;
            },
            stop: function(event, ui) {
                synchroniseViewToModel();
                ComputeGroupAvg();
                gteamAssignmentsWeight = sortTeamByWeight();
                synchroniseModelToView();
            }
        };

        $("#teams .sortable").sortable(sortdict);
        $("#unassigned .sortable").sortable(sortdict);

        // Now it's time to move our students to our teams.
        var message = "";
        var groupeOK = 0;

        for (i in teamAssignments) {
            var aSum = questions;
            for (k in aSum) {
                var q = aSum[k];
                for (var l in q.answers) {
                    aSum[k].answers[l].count = 0;
                }
            }

            var team = teamAssignments[i];
            var teamDiv = $("#team-" + i + " > div.sortable");
            for (j in team) {
                var studentID = team[j];
                var studentDiv = $("#student-" + studentID);
                var myResponses = responses[studentID];
                if (myResponses) {
                    for (k in questions) {
                        var q = questions[k];
                        nAggSpec  = -1;
                        
                        // Check if the question is in aggregate mode and if the answer is the 
                        // Good one do not count other to that question...
                        for (m in q.answers) {
                            a = q.answers[m];
                            if ($.inArray(parseInt(a.id), myResponses) != -1) {
                                for (var cmdIdx = 0; cmdIdx < aCommand.length; cmdIdx++) {
                                    if (aCommand[cmdIdx][0] == strings.aggregate && aCommand[cmdIdx][2] == q.id) {
                                        if (typeof aTeamMaxAgg[i] !== 'undefined' 
                                                && aTeamMaxAgg[i][cmdIdx] == aSum[k].answers[m].id) {
                                            nAggSpec = aSum[k].answers[m].id;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                        // Count answer at question bt group
                        for (m in q.answers) {
                            var a = q.answers[m];
                            if ($.inArray(parseInt(a.id), myResponses) != -1) {
                                if (nAggSpec != -1) { // In case of aggregate only count the good one
                                    if (nAggSpec == aSum[k].answers[m].id) {
                                        aSum[k].answers[m].count++; // Add the number of time this answer is on the team
                                    }
                                } else {
                                    aSum[k].answers[m].count++;
                                }
                            }
                        }
                    }
                }

                studentDiv.detach();
                teamDiv.append(studentDiv);
                if ($.inArray(studentID, selectedStudents) != -1) {
                    studentDiv.css("color", "green");
                } else {
                    studentDiv.css("color", "");
                }
            }

            // Summary
            // This code generate a kind of report on created teams
            if (teamAssignments[0].length > 0) {
                var gpok = 0; // This variable is positive if they've only students that match the criterion in the team
                message = message + "<div id='sumbox_" + i + "' data-name={dataname_" + i + "} class='{sumbox_" + i + "} {avgbox_" 
                    + i + "} col-sm-4 col-md-3 border-rounded-right mylittlebox'><span style='font-weight:bold;'>" + teamNames[i] 
                    + " (" + team.length + ") </span><br>";
                for (k in aSum) {
                    var q = aSum[k];
                    failed = 0;
                    values = 0;
                    for (l in q.answers) { 
                        var color = getColor(aSum[k].answers[l],i);
                        if (color != '' && color.indexOf('red') != -1) {
                            failed = 1;
                        }
                        if (color != '') {
                            values++;
                        }
                        // Don't display the question that are not in the criteria selection
                        if (color != '') message = message + aSum[k].question + " - " + aSum[k].answers[l].answer +
                            " : <span style='"+color+"'>" + aSum[k].answers[l].count + "</span><br>";

                        if (color != '' && color.indexOf('background-color:green') != -1) {
                            message = message.replace('{avgbox_' + i + '}', aSum[k].answers[l].answer.split(' ').join('_'));
                            message = message.replace('{dataname_' + i + '}', aSum[k].answers[l].answer.split(' ').join('_'));
                        }
                    }
                    // Check if they've failed in group creation
                    if (failed == 0  && values >0) {
                        gpok++;
                        message = message + '<b><span style="color:green">' + aSum[k].question + ' ' + strings.teamupsuccess
                            + '</span></b><br>';
                    } else if (values > 0) {
                        gpok = -10000;
                        message = message + '<b><span style="color:red">'   + aSum[k].question + ' ' + strings.teamupwarning
                            + '</span></b><br>';
                    } else {
                        gpok++;
                    }
                }
                // Check if they've a balance criteria to display average etc. info
                if (typeof teamAvg[i] !== 'undefined') {
                    var aTemp = {};
                    aTemp.id = -1;
                    var color = getColor(aTemp, i);
                    message = message + strings.average + " :<span style='" + color + "'>" + teamAvg[i] + "</span><br>";
                    if (color.indexOf('red') != -1) {
                        message = message + "<b><span style='color:red'>" + strings.averagewarning + "</span></b><br>";
                        gpok = 0;
                    } else {
                        gpok++;
                        message = message + "<b><span style='color:green'>" + strings.averagesuccess + "</span></b><br>";
                    }
                }
                message = message + "</div>";
                if (gpok > 0) {
                    groupeOK++;
                    message = message.replace('{sumbox_' + i + '}', 'box_ok');
                } else {
                    message = message.replace('{sumbox_' + i + '}', 'box_ko');
                }
            }
        }
        if (teamAssignments.length > 0 && teamAssignments[0].length > 0) {
            var nbStudent = 0;
            nbStudent = $(".student").length;
            teamGood = groupeOK;
            var nSuccess = (groupeOK / teamNames.length) * 100;

            var colSuccess = 'red';
            if (nSuccess > 80) { 
                colSuccess = 'orange';
            }

            if (nSuccess == 100) {
                colSuccess = 'green';
            }

            message = message +'<div class="col-sm-4 col-md-3 border-rounded-right">';
            message = message + strings.nbGroupSuccess + ' : <span style="color:' + colSuccess + ';">' + groupeOK + '/'
                + teamNames.length + '</span>';
            message = message + '<br>' + strings.nbStudent + ' : ' + nbStudent;
            message = message + '</div>';
            if (typeof teamAvg[i] !== 'undefined') {
                message = message + '<div class="col-sm-4 col-md-3 border-rounded-right">' + strings.average + ' : ' + average
                    + '<br>' + strings.standarddeviation + ' :' + ecartType + '<br>' + strings.bornes + ':'
                    + (average-(ecartType / 2)).toFixed(2) + '-' + (average + (ecartType / 2)).toFixed(2) + '</div>';
                    
                $('#feedback').html(strings.teamupsuccessnbr + ' : <span style="color:' + colSuccess + ';">' + groupeOK + '/'
                    + teamNames.length + '</span><br>' + strings.nbStudent + ' : ' + nbStudent + '<br>' + strings.average + ' : '
                    + average + '<br>' + strings.standarddeviation + ' :' + ecartType + '<br>' + strings.bornes + ':'
                    + (average - (ecartType / 2)).toFixed(2) + '-' + (average + (ecartType / 2)).toFixed(2));
            } else {
                $('#feedback').html(strings.teamupsuccessnbr + ' : <span style="color:' + colSuccess + ';">' + groupeOK + '/'
                    + teamNames.length + '</span><br>' + strings.nbStudent + ' : '+ nbStudent);
            }
        } else {
            $('#feedback').html('');
        }

        if (preventDeleteClick == true) {
            $(".studentdel").hide();
        } else {
            $(".studentdel").show();
        }

        $("#summary").empty();
        $("#summary").append(message);
    };
    // *****************************************************************************************************************************
    // Return the list of criterion selected to create teams
    // *****************************************************************************************************************************
    var getCriterionObjectFromView = function(view) {
        var criterion = {};

        criterion.question = $(view).children(".questions").find("*:selected").val();
        criterion.answers = [];
        criterion.oper = $(view).children(".oper").find("*:selected").val();
        criterion.boolOper = $(view).children(".boolOper").html();
        $(view).children(".answers").find("input:checked").each(function() {
            criterion.answers.push(this.value);
        });

        return criterion;
    };
    // *****************************************************************************************************************************
    // Build team base on the last name of the student
    // *****************************************************************************************************************************
    var buildTeamsAlpha = function() {
        var str = "";
        $('#nameofgroup').hide();

        var studentByTeam = 0;
        teamAssignments = [];
        var teamAssignmentsOrdered = [];

        resetTeams();
        bCreated = true; // Teams preview created.
        preventDeleteClick = true;

        $("#unassigned .student").each(function() {
            var rslt = /student-(\d+)/.exec(this.id);
            teamAssignmentsOrdered.push(rslt[1]);
        });
        var studentByTeam = Math.ceil(teamAssignmentsOrdered.length / teamNames.length);
        var teamNbr = 0;
        var studentNbr = 0;
        teamAssignments[0] = [];
        for (i = 0; i < teamAssignmentsOrdered.length; i++) {
            teamAssignments[teamNbr][studentNbr] = teamAssignmentsOrdered[i];
            studentNbr++;
            if (studentNbr >= studentByTeam && teamNbr < teamNames.length-1) {
                teamNbr++;
                teamAssignments[teamNbr] = [];
                studentNbr = 0;
            }
        }

        var pad = "00";
        for (i = 0; i < teamNames.length; i++) {
            str = "" + (i + 1);
            teamNames[i] = strings.serie + " " + pad.substring(0, pad.length - str.length) + str;
        }

        synchroniseModelToView();
    };
    // *****************************************************************************************************************************
    // Build the teams bases on criterion selected
    // bReset is used to rrelauch the algorithm without restarting from scratch
    // bPrettify  is used to add in the algorithm the possibility of moving instead of swapping
    // The boolOper are became groupENG operator like (cluster, aggragate, balance, distribute)
    // *****************************************************************************************************************************
    var buildTeams = function(bReset, bPrettify) {
        var nbStudent = 0;

        $('#nameofgroup').show();
        if (bReset == true) {
            resetTeams();
        }
        preventDeleteClick = true;

        synchroniseViewToModel();

        selectedStudents = [];
        aDontMove = []; // Array of student that cannot move

        // Build our predicate based on the UI.
        var predicate = [];
        var criterionGroup = []; // Temp var for running criterion group.

        bCreated = true; // Teams preview created

        $("#predicate .criterionWrapper").each(function() {
            var criterion = getCriterionObjectFromView($(this).children(".criterion"));
            criterion.rule = $(this).find(".boolOper").html();
            criterionGroup.push(criterion);
        });

        unassignedStudents = {};
        assignedStudents = {};
        // Get students ids based on their UI id with a regular expression
        $("#unassigned .student").each(function() {
            var rslt = /student-(\d+)/.exec(this.id);
            unassignedStudents[rslt[1]] = students[rslt[1]];
        });

        $(".team .student").each(function() {
            var rslt = /student-(\d+)/.exec(this.id);
            assignedStudents[rslt[1]] = students[rslt[1]];
        });

        // Get rid of students with no responses.
        $.each(responses, function(k, v) {
            if (v === false) {
                delete assignedStudents[k];
                delete unassignedStudents[k];
            }
            nbStudent++;
        });

        aCommand = []; // Keep the list of command (cluster, etc), id's to look for and question ID
        i = 0;
        bAgg = false;
        for (c in criterionGroup) {
            criterion = criterionGroup[c];
            strLine = "-" + criterion['rule'] + ":" + questions[criterion['question']].question + '<br>value:';
            aCommand[i] = [];
            aCommand[i][0] = criterion['rule']; // Operation Cluster, aggregate,balance,distribute
            aCommand[i][1] = ''; // list of concernee answers
            aCommand[i][2] = criterion['question']; // the id of the question
            for (a in criterion['answers']) {
                answer = questions[criterion['question']].answers[criterion['answers'][a]];
                strLine = strLine + answer.answer + '(' + criterion['answers'][a] + '),';
                aCommand[i][1] = aCommand[i][1] + criterion['answers'][a] + ',';
            }
            // In case of distribute put all answers if none of them are selected
            if (aCommand[i][0] == strings.distribute && aCommand[i][1] == '') {
                for (var qa in questions[criterion['question']].answers) {
                    aCommand[i][1] = aCommand[i][1] + qa + ',';
                }
            }

            // Aggregate get all as answers (to be use to find answer in student answers)
            if (aCommand[i][0] == strings.aggregate) {
                bAgg = true;
                qAgg = i;
                aCommand[i][1] = '';
                $('#aggList').empty();
                $('#aggList').append($('<option>', { value: '',text : '' }));
                for (var qa in questions[criterion['question']].answers) {
                    aCommand[i][1] = aCommand[i][1] + qa + ',';
                    $('#aggList').append($('<option>'
                        ,{ value: questions[criterion['question']].answers[qa].answer.split(' ').join('_')
                        ,text : questions[criterion['question']].answers[qa].answer }));
                }
                $('#aggList').css('display', '');
                $('#aggListTitle').css('display', '');
            }

            // Balance get all as answers (to be use to find answer in student answers)
            if (aCommand[i][0] == strings.balance) {
                for (var qa in questions[criterion['question']].answers) {
                    aCommand[i][1] = aCommand[i][1] + qa + ',';
                }
            }

            aCommand[i][1] = aCommand[i][1].replace(new RegExp("[" + ',' + "]*$"), '');
            i++;
            strLine = strLine.replace(new RegExp("[" + ',' + "]*$"), '');
        }
        nMaxStudent = Math.ceil(nbStudent / teamNames.length);
        // Assign randomly the student to a team
        assignRandomly();
        // Play the algorithm multiple time at least once and max five
        nbTime = parseInt(6 - (nbStudent / 100), 10);
        if (nbTime < 1) {
            nbTime = 1;
        }
        if (nbTime > 5) {
            nbTime = 5;
        }

        for (var nPlay = 0; nPlay < nbTime; nPlay++) {
            // ATTENTION the variable i is used inside each call
            for (i=0; i < aCommand.length; i++) { // Loop criteria
                switch(aCommand[i][0]) {
                    case strings.cluster : // At least 2 or no one don't isolate minorirty...
                        cluster(bPrettify);
                        break;
                    case strings.distribute : // Diversity heterogne
                        distribute(bPrettify);
                        break;
                    case strings.aggregate : // All the same homogene
                        aggregate(bPrettify);
                        break;
                    case strings.balance : // Based on number computing
                        balance(bPrettify);
                        break;
                }
            }
            gteamAssignmentsWeight = sortTeamByWeight();

            if (bPrettify) { // Compact group try that all groups have the same size
                avgStudentGroup = parseInt(nbStudent / teamAssignments.length);
                for(var z = 0; z < 10; z++) {
                    var nMove = 0;
                    for (var j = 0; j < teamAssignments.length; j++) {
                        // team is too big
                        if (teamAssignments[j].length > avgStudentGroup) {
                            for (k = teamAssignments[j].length-1; k >= 0; k--) {
                                // Special case with only aggregate criterion
                                if (bAgg == true && aDontMove.reduce(function(n, val) {
                                                return n + (val === teamAssignments[j][k]);
                                            }, 0) == 1)  {
                                    for (l=0; l < teamAssignments.length; l++) {
                                        if (teamAssignments[l].length - teamAssignments[j].length < -1
                                                && aTeamMaxAgg[l][qAgg] ==  aTeamMaxAgg[j][qAgg]) {
                                            teamAssignments[l].push(teamAssignments[j][k]);
                                            teamAssignments[j].splice(k, 1);
                                            k=-1000000; // Move one by one...
                                            nMove++;
                                            break;
                                        }
                                    }
                                } else if (aDontMove.indexOf(teamAssignments[j][k]) == -1 ) {
                                    for (l=0; l < teamAssignments.length; l++) {
                                        if (teamAssignments[l].length < avgStudentGroup) {
                                            teamAssignments[l].push(teamAssignments[j][k]);
                                            teamAssignments[j].splice(k, 1);
                                            k=-1000000; // Move one by one...
                                            nMove++;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (nMove == 0) {
                        z = 15; // No move no loop... go away ! break should work !
                    }
                    gteamAssignmentsWeight = sortTeamByWeight();
                }

                // Recompute the balance if neccessary)
                for (i = 0; i < aCommand.length; i++) {
                    if (aCommand[i][0] == strings.balance) {
                        balance(bPrettify); // Based on number computed
                        break;
                    }
                }
            }

            aDontMove = []; // Release all the student for next swap
        }
        synchroniseModelToView();
        updateTeams($('#nbteam').val());
    };
    // *****************************************************************************************************************************
    // Execute the balance algorithm. A team must have an average 'score' near the global average more less the half
    //    of the standard deviation (écart type)
    // *****************************************************************************************************************************
    var balance = function(bPrettify) {
        var ecart = 0;
        var nbStudent = 0;
        var nbStu = 0;
        var aNeeded = aCommand[i][1].split(","); // List of needed values
        teamAvg = [];
        average = 0;
        total = 0;
        ecartType = 0;

        nbStudent = ComputeGroupAvg();
        average = Math.round(total/nbStudent);

        // Compute the standard deviation (Square root of the sum of the squares of the subtraction of the student's
        // score-the average gloabal divided by the number of students)
        for (j = 0; j < teamAssignments.length; j++) { // Loop teams
            for (k = 0; k < teamAssignments[j].length; k++) { // Loop students
                for (l = 0; l < aNeeded.length; l++) { // Loop values
                    if (responses[teamAssignments[j][k]].length > 0 && responses[teamAssignments[j][k]].indexOf(
                            parseInt(aNeeded[l], 10)) != -1) {
                   ecart += Math.pow(parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l], 10)].answer, 10) - average, 2);
                        break;
                    }
                }
            }
        }
        ecartType = Math.round(Math.sqrt(ecart / nbStudent));

        // Swap to balance...
        for (j = 0; j < teamAssignments.length; j++) { // Loop teams
            for (k = 0; k < teamAssignments[j].length; k++) { // Loop students
                if (teamAvg[j] > average+(ecartType / 2)) {
                    // The average of team is higher than the average + 50% of the standard deviation
                    for (l = 0; l < aNeeded.length; l++) { // Loop values
                        if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l], 10)) != -1)  {
                            // If student result is bigger than the average find a lighter one...
                            if (parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l], 10)].answer, 10) >  average) { 
                                var oStudent = findSwapBalance(i, j, 0);
                                if (oStudent.length > 0) {
                                    swapStudent(j, k, oStudent);
                                    teamAvg[j] = 0;
                                    nbStu = 0;
                                    // Recompute the average of the current team
                                    for (var m = 0; m < teamAssignments[j].length; m++) { // Loop students
                                        for (var n = 0; n < aNeeded.length; n++) { // Loop values
                                            if (responses[teamAssignments[j][m]].length > 0
                                                    && responses[teamAssignments[j][m]].indexOf(parseInt(aNeeded[n],10)) != -1)  {
                                     teamAvg[j] += parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[n] ,10)].answer ,10);
                                                nbStu++;
                                                break;
                                            }
                                        }
                                    }
                                    teamAvg[j] = teamAvg[j] / nbStu;
                                    // Recompute the average of the giver team
                                    teamAvg[oStudent[1]] = 0;
                                    nbStu = 0;
                                    for (var m = 0; m < teamAssignments[oStudent[1]].length; m++) { // Loop students
                                        for (var n = 0; n < aNeeded.length; n++) { // Loop values
                                            if (responses[teamAssignments[oStudent[1]][m]].length > 0
                                          && responses[teamAssignments[oStudent[1]][m]].indexOf(parseInt(aNeeded[n], 10)) != -1)  {
                           teamAvg[oStudent[1]] += parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[n], 10)].answer, 10);
                                                nbStu++;
                                                break;
                                            }
                                        }
                                    }
                                    teamAvg[oStudent[1]] = teamAvg[oStudent[1]] / nbStu;
                                    if (teamAvg[j] < average+(ecartType/2)) { // Problem solved
                                        k = 1000000; // Go out of the student loop
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else if (teamAvg[j] < average-(ecartType / 2)) {
                    // The average of team is lower than the average - 50% of the standard deviation
                    for (l = 0; l < aNeeded.length; l++) { // Loop values
                        if (responses[teamAssignments[j][k]].length > 0
                                && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l], 10)) != -1) {
                            // If student result is lower than the average find a heavier one...
                            if (parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l], 10)].answer, 10) <  average) {
                                var oStudent = findSwapBalance(i, j, 1);
                                if (oStudent.length > 0) {
                                    swapStudent(j, k, oStudent);
                                    teamAvg[j] = 0;
                                    nbStu = 0;
                                    // Recompute the average of the current team
                                    for (m = 0; m < teamAssignments[j].length; m++) { // Loop students
                                        for (n = 0; n < aNeeded.length; n++) { // Loop values
                                            if (responses[teamAssignments[j][m]].length > 0
                                                    && responses[teamAssignments[j][m]].indexOf(parseInt(aNeeded[n], 10)) != -1) {
                                    teamAvg[j] += parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[n], 10)].answer, 10);
                                                nbStu++;
                                                break;
                                            }
                                        }
                                    }
                                    teamAvg[j] = teamAvg[j] / nbStu;
                                    // Recompute the average of the giver team
                                    teamAvg[oStudent[1]]  = 0;
                                    nbStu = 0;
                                    for (m = 0; m < teamAssignments[oStudent[1]].length; m++) { // Loop students
                                        for (n=0; n < aNeeded.length; n++) { // Loop values
                                            if (responses[teamAssignments[oStudent[1]][m]].length > 0
                                           && responses[teamAssignments[oStudent[1]][m]].indexOf(parseInt(aNeeded[n], 10)) != -1) {
                           teamAvg[oStudent[1]] += parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[n], 10)].answer, 10);
                                                nbStu++;
                                                break;
                                            }
                                        }
                                    }
                                    teamAvg[oStudent[1]] = teamAvg[oStudent[1]] / nbStu;
                                    if (teamAvg[j] > average - (ecartType / 2)) { // Problem solved
                                        k = 1000000; // Go out of the student loop
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    // *****************************************************************************************************************************
    // Execute the aggregate algorithm. A team must have only students with a specific value from a list of skills. HOMOGENICITY
    // *****************************************************************************************************************************
    var aggregate = function(bPrettify) {
        for (var j=0; j < teamAssignments.length; j++) { // Loop teams
            var aNeeded = aCommand[i][1].split(","); // List of needed value
            var maxValueCount = 0;
            var maxValue = 0; // Index of the value with the most representation in the group
            var ValueCount = 0;
            var value = 0;
            // First count the value with the most representation
            // in the responses with locked students
            for (var l = 0; l < aNeeded.length; l++) { // Loop values
                ValueCount = 0;
                for (var k = 0; k < teamAssignments[j].length; k++) { // Loop students
                    value = l;
                    // Check if the student have this value
                    if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l], 10)) != -1)  {
                        ValueCount++;
                    }
                }
                if (ValueCount > maxValueCount) {
                    maxValueCount = ValueCount;
                    maxValue = value;
                }
            }

            if (typeof aTeamMaxAgg[j] === 'undefined') {
                aTeamMaxAgg[j] = [];
            }
            // Save the most representative value for the aggregation in the group
            aTeamMaxAgg[j][i] = parseInt(aNeeded[maxValue], 10); 
            for (var k = 0; k < teamAssignments[j].length; k++) { // Loop students for completing the most represented value
                // Check if the student don't have this value and can be swapped
                if (responses[teamAssignments[j][k]].length > 0 && responses[teamAssignments[j][k]].indexOf(aTeamMaxAgg[j][i]) == -1
                    && aDontMove.indexOf(teamAssignments[j][k]) == -1)  {
                    var oStudent = findSwap(j, aTeamMaxAgg[j][i].toString());
                    if (oStudent.length > 0) {
                        swapStudent(j, k, oStudent);
                        aDontMove.push(teamAssignments[j][k]);
                    }
                } else {
                    if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[maxValue], 10)) != -1) {
                        aDontMove.push(teamAssignments[j][k]);
                        // Upgrade the weight of the already locked student with the right value
                    }
                }
            }

            if (bPrettify == true) { // Try to prettify the group by moving values that are not the dominant
                var aRemove = [];
                for (var k = 0; k < teamAssignments[j].length; k++) {
                    if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(aTeamMaxAgg[j][i]) == -1
                            && aDontMove.indexOf(teamAssignments[j][k]) == -1)  {
                        // The student don't have this value and can be moved
                        var nAnswer = -1;
                        for (var n = 0; n < aNeeded.length; n++) { // Search the answer
                            if (responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[n], 10)) != -1) {
                                nAnswer = parseInt(aNeeded[n], 10);
                                break;
                            }
                        }
                        var group = findMoveAgg(j, nAnswer);
                        if (group > -1) {
                            teamAssignments[group].push(teamAssignments[j][k]); // Add the student to his new group
                            aDontMove.push(teamAssignments[j][k]); // LOCK IT
                            aRemove.splice(0, 0, k); // Store the ids that have to be removed
                        }
                    }
                }
                // Remove moved student
                for (var k = 0; k < aRemove.length; k++) {
                    teamAssignments[j].splice(aRemove[k], 1); // Remove from old group
                }
            }
        }
    }
    // *****************************************************************************************************************************
    // Execute the distribute algorithm. A team must have a student with a specific value from a list of skills. HETEROGENITY
    // *****************************************************************************************************************************
    var distribute = function(bPrettify) {
        for (var j = 0; j < teamAssignments.length; j++) { // Loop teams
            var aNeeded = aCommand[i][1].split(","); // List of needed value
            for (var k = 0; k < teamAssignments[j].length; k++) {
                // First loop inside a team to remove value that are already in locked student
                // Remove values that cannot be move (student in adontmove)
                // as a base for distribute useless to take more of them
                if (aDontMove.indexOf(teamAssignments[j][k]) != -1) { // Can't be remove student already locked
                    for (l=0; l < aNeeded.length; l++) { // Value find and be removed of needed skills, capacity, etc
                        if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l],10)) != -1) {
                            aNeeded.splice(l, 1);
                            break;
                        }
                    }
                }
            }

            // aNeeded contain the missing values to meet the distribute criterion
            for (var k=0; k < teamAssignments[j].length; k++) {
                // Second loop inside the team to find missing value to complete the distribution of 'skills' (values)
                if (aDontMove.indexOf(teamAssignments[j][k]) == -1) { // Can be remove the student are not locked
                    nLength = aNeeded.length-1;
                    for (l = nLength; l >= 0; l--) {
                        if (responses[teamAssignments[j][k]].length > 0
                                && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l], 10)) != -1) {
                            // Value find and can be removed
                            aNeeded.splice(l, 1);
                            aDontMove.push(teamAssignments[j][k]); // The student is now a keeper
                        }
                    }
                }
            }
            // Find missing values
            for (var l = 0; l < aNeeded.length; l++) { // Try to find missing values in other teams
                var oStudent = findSwap(j, aNeeded[l]);
                if (oStudent.length > 0) {
                    for (k = 0; k < teamAssignments[j].length; k++) {
                        if (aDontMove.indexOf(teamAssignments[j][k]) == -1) { // Can be swap
                            swapStudent(j,k,oStudent);
                            break;
                        }
                    }
                } else { // PLAN B (START)
                    // Check if they've an aggregate question
                    if (bAgg == true) {
                        var oStudent = findSwapAgg(j, aNeeded[l]);
                        if (oStudent.length > 0) {
                            for (var k = 0; k < teamAssignments[j].length; k++) { // Can be swap
                                // If yes find a swap student with a weight 1
                                if (aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[j][k]);}, 0) == 1) {
                                    // Do the swap...
                                    swapStudent(j, k, oStudent);
                                    break;
                                }
                            }
                        }
                    }
                } // PLAN B (END)
            }
        }
    }
    // *****************************************************************************************************************************
    // Execute the cluster algorithm. A student with a specific value cannot be alone in a team.
    // *****************************************************************************************************************************
    var cluster = function(bPrettify) {
        var single; // Position of the single student in the team
        for (var j = 0; j < teamAssignments.length; j++) { // Loop througth teams
            single = -1; // -1 mean no one is found
            nCount = 0;
            for (var k = 0; k < teamAssignments[j].length; k++) { // Loop inside a team to get student
                var valueArray = aCommand[i][1].split(","); // List of request value that cannot be single
                for (var l = 0; l<valueArray.length; l++) {
                    if (responses[teamAssignments[j][k]].length > 0
                        && responses[teamAssignments[j][k]].indexOf(parseInt(valueArray[l],10)) > -1) {
                        nCount++;
                        single = k; // keep in memory the single student (he can be also not single !) it's just the last one found
                        if (nCount <= 2) {
                            aDontMove.push(teamAssignments[j][k]);
                            // Don't lock the student if they've already enougth to meet criteria
                        }
                        break;
                    }
                }
            } // Finnish to loop through team

            if (nCount == 1) { // Try to swap or prettify to complete 0 is OK 2 or more is OK too but be alone is so sad
                if (bPrettify == true) { // Try to prettify the group by removing single (move)
                    var group = findMoveCluster(j, aCommand[i][1]);
                    if (group > -1) {
                        teamAssignments[group].push(teamAssignments[j][single]); 
                        // Add the student to his new group (move not a swap)
                        aDontMove.push(teamAssignments[j][single]); // LOCK IT
                        teamAssignments[j].splice(single, 1); // Remove him from old group
                    } else {
                        // If no one was found remove the last student of locked list to free him because the criteria is not meet
                        // In that group
                        if (bAgg == true) {
                            var oStudent = findSwapAgg(j, aCommand[i][1]);
                            if (oStudent.length > 0) {
                                for (var k = 0; k < teamAssignments[j].length; k++) { // Can be swap
                                    // if yes find a swap student with a weight 1
                                    if (aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[j][k]);}, 0) == 1) {
                                        // Do the swap...
                                        swapStudent(j, k, oStudent);
                                        break;
                                    }
                                }
                            } else {
                                // If no one was found, remove the last student of locked list to free him because the criteria
                                // Is not meet in that group
                                aDontMove.pop();
                            }
                        } else {
                            aDontMove.pop();
                            // If no one was found remove the last student of locked list to free him because
                            // The criteria is not meet in that group
                        }
                    }
                } else { // Usual algo try to swap
                    var oStudent = findSwap(j, aCommand[i][1]);
                    if (oStudent.length > 0) {
                        for (var k=0; k < teamAssignments[j].length; k++) { // Find a student in the team that can be swap
                            if (aDontMove.indexOf(teamAssignments[j][k]) == -1) { // Can be swap
                                swapStudent(j, k, oStudent);
                                break;
                            }
                        }
                    } else { // PLAN B
                        // Check if they've an aggregate question
                        if (bAgg == true) {
                            var oStudent = findSwapAgg(j, aCommand[i][1]);
                            if (oStudent.length > 0) {
                                for (k=0; k < teamAssignments[j].length; k++) { // Can be swap
                                    // If yes find a swap student with a weight 1
                                    if (aDontMove.reduce(function(n, val) {
                                                            return n + (val === teamAssignments[j][k]);
                                                        }, 0) == 1) {
                                        // Do the swap...
                                        swapStudent(j, k, oStudent);
                                        break;
                                    }
                                }
                            } else {
                                // If no one was found remove the last student of locked list to free him because the criteria is
                                // Not meet in that group
                                aDontMove.pop();
                            }
                        } else {
                            aDontMove.pop();
                            // If no one was found remove the last student of locked list to free him because the criteria is not
                            // Meet in that group
                        }
                    } // PLAN B  END
                }
            }
        }
    }
    // *****************************************************************************************************************************
    // Equalize the number of students by group
    // *****************************************************************************************************************************
    var equalize = function() {
        var nbStudent = $(".student").length;
        var avgStudentGroup = Math.ceil(nbStudent / teamAssignments.length);

        for (var j = 0; j < teamAssignments.length; j++) {
            if (teamAssignments[j].length > avgStudentGroup) {
                var aRemove = [];
                for (var k = teamAssignments[j].length-1; k >= avgStudentGroup;k--) {
                    for (var l = 0; l < teamAssignments.length; l++) {
                        if ((teamAssignments[l].length < avgStudentGroup || l == teamAssignments.length-1) && l != j) {
                            teamAssignments[l].push(teamAssignments[j][k]);
                            aRemove.push(k); // Store the ids that have to be removed
                            break;
                        }
                    }
                }
                // Remove moved student
                for (var k = 0; k < aRemove.length; k++) {
                    teamAssignments[j].splice(aRemove[k], 1); // Remove from old group
                }
            } else if (teamAssignments[j].length < avgStudentGroup) {
                var aRemove = [];
                for (var k = teamAssignments[j].length; k < avgStudentGroup; k++) {
                    for (var l = 0; l < teamAssignments.length; l++) {
                        if ((teamAssignments[l].length > avgStudentGroup || l == teamAssignments.length-1) && l != j) {
                            teamAssignments[j].push(teamAssignments[l][teamAssignments[l].length-1]);
                            teamAssignments[l].splice(teamAssignments[l].length-1, 1);
                            if (teamAssignments[l].length == 0) {
                                synchroniseModelToView();
                                $('#nbteam').val($('#nbteam').val() - 1);
                                updateTeams($('#nbteam').val());
                                synchroniseViewToModel();
                                equalize();
                                return;
                            }
                            break;
                        }
                    }
                }
            }
        }
        synchroniseModelToView();
    }
    // *****************************************************************************************************************************
    // Swap student from a group to another
    // *****************************************************************************************************************************
    var swapStudent = function(j, k, oStudent) {
        var nTemp = teamAssignments[j][k];
        teamAssignments[j][k] = oStudent[0];
        teamAssignments[oStudent[1]][oStudent[2]] = nTemp;
        aDontMove.push(oStudent[0]);
    }
    // *****************************************************************************************************************************
    // Sort all team by student weight. The weight is compute by the number of usefull criteria
    // *****************************************************************************************************************************
    var sortTeamByWeight = function() {
        // Sort result by weight
        var teamAssignmentsWeight = [];
        for (var j = 0; j < teamAssignments.length; j++) {
            var aStudentgroup = [];
            for (var k = 0; k < teamAssignments[j].length; k++) {
                // Compute the weight
                aStudentgroup[k] = {};
                aStudentgroup[k].id = teamAssignments[j][k];
                aStudentgroup[k].weight = aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[j][k]);}, 0);
            }
            aStudentgroup.sort(function(a,b) {return b.weight - a.weight});
            teamAssignmentsWeight[j] = [];
            for (var k = 0; k < teamAssignments[j].length; k++) {
                teamAssignments[j][k]       = aStudentgroup[k].id;
                teamAssignmentsWeight[j][k] = aStudentgroup[k].weight;
            }
        }
        return teamAssignmentsWeight;
    };
    // *****************************************************************************************************************************
    // Find a student that meet a criteria in another group to swap it with a useless one in the current team
    // *****************************************************************************************************************************
    var findSwap = function(team, findList) {
        for (var i=0; i < teamAssignments.length; i++) {
            if (i != team) { // Don't examine the original team !
                for (var j=0; j < teamAssignments[i].length; j++) {
                    var valueArray = findList.split(",");
                    for (var l = 0; l < valueArray.length; l++) {
                        if (responses[teamAssignments[i][j]].length > 0 
                                && responses[teamAssignments[i][j]].indexOf(parseInt(valueArray[l], 10)) > -1) {
                            if (aDontMove.indexOf(teamAssignments[i][j]) == -1) { // Can be swap
                                return [teamAssignments[i][j], i, j];
                            }
                        }
                    }
                }
            }
        }
        return [];
    };
    // *****************************************************************************************************************************
    // Find a student that meet a criteria in another group to swap it with a useless one in the current team
    // In case if they've an aggregation the swap can be only done in the same kind of team
    // *****************************************************************************************************************************
    var findSwapAgg = function(team, findList) {
        for (var i = 0; i < teamAssignments.length; i++) {
            if (i != team && typeof aTeamMaxAgg[0] !== 'undefined'
                    && aTeamMaxAgg[i][qAgg] == aTeamMaxAgg[team][qAgg]) { // Don't examine the original team !
                for (var j=0; j < teamAssignments[i].length; j++) {
                    var valueArray = findList.split(",");
                    for (var l=0; l<valueArray.length; l++) {
                        if (responses[teamAssignments[i][j]].length > 0
                                && responses[teamAssignments[i][j]].indexOf(parseInt(valueArray[l],10)) > -1) {
                            if (aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[i][j]);}, 0) == 1) {
                                // Can be swap
                                return [teamAssignments[i][j], i, j];
                            }
                        }
                    }
                }
            }
        }
        return [];
    };
    // *****************************************************************************************************************************
    // Find a team that can take the single value help to meet criteria for cluster
    // Find first a team with also a single value else with more than one
    // *****************************************************************************************************************************
    var findMoveCluster = function(team, findList) {
        var aTeam = [];
        for (var i = 0; i < teamAssignments.length; i++) {
            aTeam[i] = 0;
            if (i != team) { // Don't examine the original team !
                for (var j=0; j < teamAssignments[i].length; j++) {
                    var valueArray = findList.split(",");
                    for (var l=0; l<valueArray.length; l++) {
                        if (responses[teamAssignments[i][j]].length > 0
                                && responses[teamAssignments[i][j]].indexOf(parseInt(valueArray[l],10)) > -1) {
                            aTeam[i]++;
                        }
                    }
                }
            }
            if (aTeam[i] == 1) {
                return i; // Find first a team with a single
            }
        }
        for (var i=0; i < teamAssignments.length; i++) { // Find the team with more than a value
            if (aTeam[i] > 0) {
                return i;
            }
        }
        return -1;
    };
    // *****************************************************************************************************************************
    // Find a team that can take the student that meet the criteria for aggregate
    // *****************************************************************************************************************************
    var findMoveAgg = function(team, valueToFind) {
        var aTeam = [];
        for (var i=0; i < teamAssignments.length; i++) { // Loop teams
            aTeam[i] = 0;
            for (var j=0; j < teamAssignments[i].length; j++) { // Loop studentss
                // Don't examine the original team ! count the specified value in the group
                if (i != team && responses[teamAssignments[i][j]].length > 0
                        && responses[teamAssignments[i][j]].indexOf(valueToFind) > -1) {
                    aTeam[i]++;
                }
            }
            if (aTeam[i] > 2) {
                return i; // If they've more than one value it's a possible team
            }
        }
        return -1; // No  team finded
    }
    // *****************************************************************************************************************************
    // Find a value in another team that can help to meet criteria for balance
    // I index of the balance command
    // Team the current team that looking for a student to swap
    // Weight = 0 student under average
    // Weight = 1 student above average
    // *****************************************************************************************************************************
    var findSwapBalance = function(i,team, weight) {
        var aNeeded =  aCommand[i][1].split(","); // List of concerned values
        for (var j = 0; j < teamAssignments.length; j++) { // Loop teams
            for (var k = 0; k < teamAssignments[j].length; k++) { // Loop students
                if (teamAvg[j] < average && weight == 0 && j != team) {
                    // Look for a student with a value under the average and not in the requesting team !
                    for (var l = 0; l < aNeeded.length; l++) { // Loop through values
                        if (responses[teamAssignments[j][k]].length > 0
                                && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l],10)) != -1)  {
                            // If student result is heavier than the average find a lighter one...
                            if (parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l],10)].answer,10) <  average
                                    && aDontMove.indexOf(teamAssignments[j][k]) == -1) {
                                return [teamAssignments[j][k],j,k];
                                // Return the id of the student, and the index of is team and is position in the team
                            } else {
                                if (bAgg == true && typeof aTeamMaxAgg[0] !== 'undefined'
                                      && aTeamMaxAgg[team][qAgg] ==  aTeamMaxAgg[j][qAgg]
                                      && parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l],10)].answer,10) <  average
                                      && aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[j][k]);}, 0) == 1) {
                                    return [teamAssignments[j][k], j, k];
                                    // Return the id of the student, and the index of is team and is position in the team
                                }
                            }
                        }
                    }
                }
                if (teamAvg[j] > average && weight == 1 && j != team) { // Look for a team higherweight
                    for (var l=0; l < aNeeded.length; l++) { // Loop through values
                        if (responses[teamAssignments[j][k]].length > 0
                                && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l],10)) != -1)  {
                            // If student result is lower than the average find an heavier one...
                            if (parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l], 10)].answer, 10) >  average
                                    && aDontMove.indexOf(teamAssignments[j][k]) == -1) {
                                        return [teamAssignments[j][k], j, k];
                                        // Return the id of the student, and the index of is team and is position in the team
                            } else {
                                if (bAgg == true && typeof aTeamMaxAgg[0] !== 'undefined'
                                      && aTeamMaxAgg[team][qAgg] ==  aTeamMaxAgg[j][qAgg]
                                      && parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l],10)].answer,10) >  average
                                      && aDontMove.reduce(function(n, val) {return n + (val === teamAssignments[j][k]);}, 0) == 1) {
                                    return [teamAssignments[j][k], j, k];
                                    // Return the id of the student, and the index of is team and is position in the team
                                }
                            }
                        }
                    }
                }
            }
        }
        return []; // Nothing find ;(
    };
    // *****************************************************************************************************************************
    // Check if a student meets criterion of the request
    // *****************************************************************************************************************************
    var studentMeetsCriterion = function(student, criterion) {
        sr = responses[student]; // Student responses.
        if (sr === false) {
            return false; // Students without a response cannot meet a criterion.
        }

        var ret; // Return value.
        criterion.oper = "any"; // Only any is supported now
        ret = false;
        for (var a in criterion.answers) {
            ans = parseInt(criterion.answers[a]);
            if ($.inArray(ans, sr) != -1) {
                ret = true;
                break;
            }
        }

        return ret;
    };
    // *****************************************************************************************************************************
    // Assign randomly all students to teams
    // Fully from Teambuilder plugin
    // *****************************************************************************************************************************
    var assignRandomly = function() {
        synchroniseViewToModel();
        var unassignedStudents = [];
        $("#unassigned .student").each(function() {
            var rslt = /student-(\d+)/.exec(this.id);
            unassignedStudents.push(rslt[1]);
        });

        unassignedStudents = randomiseArray(unassignedStudents);
        while (unassignedStudents.length > 0) {
            // Get the team(s) with the lowest numbers.
            var lowestTeam = 0;
            var lowestTeams = [];

            // Skip the 0th team since otherwise we compare it to itself.
            for (i=1;i<teamAssignments.length;i++) {
                t = teamAssignments[i];
                lt = teamAssignments[lowestTeam];

                if (t.length < lt.length) {
                    lowestTeam = i;
                    lowestTeams = [];
                } else if (t.length == lt.length) {
                    lowestTeams.push(i);
                }
            }
            lowestTeams.push(lowestTeam);

            // Pick a random team from the list of lowest teams.
            do {
                randomTeam = Math.floor(Math.random() * lowestTeams.length);
                // On the OFF CHANCE that Math.random() produces 1, loop.
            } while (randomTeam >= lowestTeams.length);
            teamAssignments[lowestTeams[randomTeam]].push(unassignedStudents.pop());
        }
        synchroniseModelToView();
    };
    // *****************************************************************************************************************************
    // How this works is, we're going to create an invisible form and submit it
    // Acutally i don't know if we can do that but we'll try.
    // Fully from Teambuilder plugin
    // *****************************************************************************************************************************
    var createGroups = function() {
        synchroniseViewToModel();

        var form = $('<form action="' + window.location + '" method="POST"></form>');

        for (i = 0; i < teamNames.length; i++) {
            var tn = teamNames[i];
            var assign = teamAssignments[i];
            var tnInput = $('<input type="hidden" name="teamnames[' + i + ']" value="' + tn + '" />');
            form.append(tnInput);
            var input = $('<input type="hidden" name="teams[' + i + ']" value="' + assign.join(",") + '" />');
            form.append(input);
        }

        var action = $('<input type="hidden" name="action" value="create-groups" />');
        var name = $('<input type="hidden" name="groupingName" value="' +  $('#groupingName').val() + '" />');
        var grpid = $('<input type="hidden" name="groupingID" value="' +  $('#groupingSelect').val() + '" />');
        var inherit = $('<input type="hidden" name="inheritGroupingName" value="'
            + ($('#inheritGroupingName').is(":checked") ? 1 : 0 ) + '" />');
        var nogrouping = $('<input type="hidden" name="nogrouping" value="' + ($('#nogrouping').is(":checked") ? 1 : 0 ) + '" />');

        form.append(action);
        form.append(name);
        form.append(grpid);
        form.append(inherit);
        form.append(nogrouping);
        $("#createGroupsForm").append(form);
        form.submit();
    };
    // *****************************************************************************************************************************
    // Randomize an array used to create teams first
    // *****************************************************************************************************************************
    var randomiseArray = function(inArray) {
        // Much more random than sort().
        var ret = [];
        var array = inArray.slice(0);
        for (i = array.length; i > 0; i--) {
            index = Math.floor(Math.random() * i);
            ret.push(array[index]);
            array.splice(index, 1);
        }
        return ret;
    };
    // *****************************************************************************************************************************
    // Buttons event listener (All colored buttons at top !)
    // *****************************************************************************************************************************
    var registerEventListeners = function() {
        $('#addnewcriterion').on('click', addNewCriterion);
        $('#buildteams').on('click', function() {
                                        $('#protectAll').modal('show');
                                        setTimeout(function() {
                                                       buildTeams(true,false);buttonManager(); $('#protectAll').modal('hide');
                                                   }, 250);
                                    });

        $('#resetteams').on('click', function() {
                                        resetTeams();
                                        buttonManager();
                                        updateTeams(0);
                                        updateTeams(1);
                                        $('#nbteam').val(1);
                                     });
        $('#replay').on('click', function() {
                                    $('#protectAll').modal('show');
                                    setTimeout(function() {
                                                    buildTeams(false,false);
                                                    buttonManager();$('#protectAll').modal('hide');
                                               }, 250);
                                });
        $('#assignrandomly').on('click', function() {
                                            assignRandomly();buttonManager();
                                         });
        $('#creategroups').on('click', function() {
                                            createGroups();buttonManager();
                                       });
        $('#prettify').on('click', function() {
                                        $('#protectAll').modal('show');
                                        setTimeout(function() {
                                            buildTeams(false,true);
                                            buttonManager(); $('#protectAll').modal('hide');
                                        }, 250);
                                   });
        $('#serie').on('click', function() {
                                    $('#protectAll').modal('show');
                                    setTimeout(function() {
                                                    buildTeamsAlpha();
                                                    buttonManager(); $('#protectAll').modal('hide');
                                               }, 250);
                                });
        $('#dsp_sum_switch').on('click', function(event) {
                                            $('.box_ok').toggle();
                                            event.stopPropagation();
                                         });
        $('#aggList').on('click', function() {
                                    filterOnAgg();
                                  });
        $('#equalize').on('click', function() {
                                    equalize();
                                   });
        $('#deleteallred').on('click', function() {
                                        deleteallred();
                                       });
        $('#keepallred').on('click', function() {
                                        keepallred();
                                     });
    };
    // *****************************************************************************************************************************
    // This function is used to check if a value is good or not based on basic rules in the commands list
    // The aData contain the id of answers of the students of the group (id) and the number of student that answer that in the group
    // *****************************************************************************************************************************
    var getColor = function(aData, team) {
        for (var i = 0; i < aCommand.length; i++) { // Loop criteria of the command list
            switch(aCommand[i][0]) { // Type of critetia
                case strings.cluster : // At least 2 or 0
                    var valueArray = aCommand[i][1].split(",");
                    // List of the values that cannot be alone (usualy gender, race, minority, etc)
                    for (var j = 0; j < valueArray.length; j++) {
                        if (aData.id == parseInt(valueArray[j],10)) {
                            count = getCountForAllSelected(i, team);
                            // Recompute the number on answer in case of multiple criteria check ex (cluster FR=NL in GroupEN)
                            if (count >= 2) {
                                return 'color:green;font-weight:bold;';
                            }
                            if (count == 1) {
                                return 'color:red;font-weight:bold;'; // Someone is alone...
                            }
                        }
                    }
                    break;
                case strings.distribute : // At least 1 of the selected value must be in each team HETEROGENITY
                    var valueArray = aCommand[i][1].split(",");
                    for (var j = 0; j < valueArray.length; j++) {
                        if (aData.id == parseInt(valueArray[j], 10) &&  aData.count > 0) {
                            return 'color:green;font-weight:bold;';
                        }
                        if (aData.id == parseInt(valueArray[j], 10) &&  aData.count == 0) {
                            return 'color:red;font-weight:bold;';
                        }
                    }
                    break;
                case strings.aggregate : // The value must be alone>0 the rest of the answers representation must be 0 HOMOGENICITY
                    if ((','+aCommand[i][1]+',').indexOf(','+aData.id+',') != -1) { // really uggly patch... .try split or map
                        if (typeof aTeamMaxAgg[team] !== 'undefined') { // The group with the most representation is OK
                            if (aData.id == aTeamMaxAgg[team][i]) {
                                return 'background-color:green;color:white;font-weight:bold;font-size:14px;padding:3px;';
                            }
                            if (aData.id != aTeamMaxAgg[team][i] &&  aData.count == 0) {
                                return 'color:green;font-weight:bold;';
                            }
                            if (aData.id != aTeamMaxAgg[team][i] &&  aData.count > 0) {
                                return 'color:red;font-weight:bold;';
                            }
                        }
                    }
                    break;
                case strings.balance : // Tolerate a difference of the gloabal average of the half of the standard deviation
                    if (aData.id == -1) {
                        if (teamAvg[team] >= average-(ecartType / 2) && teamAvg[team] <= average+(ecartType / 2) ) {
                            return 'color:green;font-weight:bold;';
                        }
                        return 'color:red;font-weight:bold;';
                    }
                    break;
            }
        }
        return '';
    };
    // *****************************************************************************************************************************
    // This function compute the average of each group for the BALANCE process. The BALANCE can be only on one question
    // It's also compute the total and the average
    // *****************************************************************************************************************************
    var ComputeGroupAvg = function() {
        var nbStudent = 0; // Count all students
        var nbStu = 0; // Count students by team
        var aNeeded = [];

        // Find the balance command in the list
        for (var i=0; i < aCommand.length; i++) {
            if (aCommand[i][0] == strings.balance) {
                aNeeded   = aCommand[i][1].split(",");
                break;
            }
        }

        // Check if they've a balance in the command list
        if (aNeeded.length == 0) {
            return; // If not return
        }

        teamAvg = [];
        average = 0;
        total = 0;

        for (var j = 0; j < teamAssignments.length; j++) { // Loop teams
            teamAvg[j] = 0;
            nbStu = 0;
            for (var k = 0; k < teamAssignments[j].length; k++) { // Loop students
                for (var l = 0; l < aNeeded.length; l++) { // Loop requested values
                    if (responses[teamAssignments[j][k]].length > 0
                            && responses[teamAssignments[j][k]].indexOf(parseInt(aNeeded[l], 10)) != -1)  {
                        nAnswer = parseInt(questions[aCommand[i][2]].answers[parseInt(aNeeded[l], 10)].answer, 10);
                        total += nAnswer;
                        teamAvg[j] += nAnswer;
                        nbStudent++; // Only count student that answer...
                        nbStu++;
                        break;
                    }
                }
            }
            teamAvg[j] = teamAvg[j] / nbStu;
        }
        // The global average
        average = total / nbStudent;
        return nbStudent;
    };
    // *****************************************************************************************************************************
    // In case of cluster it's possible to select multiple values. And the rule of at least 2 or 0 must be on all selected value
    // This function count all occurrence of selected values
    // *****************************************************************************************************************************
    var getCountForAllSelected = function(index, team) {
        var nCount = 0;
        var aNeeded = aCommand[index][1].split(',');
        for (var x = 0; x < teamAssignments[team].length; x++) {
            for (var l = 0; l < aNeeded.length; l++) {
                if (responses[teamAssignments[team][x]] != false
                        && responses[teamAssignments[team][x]].indexOf(parseInt(aNeeded[l],10)) != -1) {
                    nCount++;
                }
            }
        }
        return nCount;
    };
    // *****************************************************************************************************************************
    // Manage the state of the buttons remated on various status
    // *****************************************************************************************************************************
    var buttonManager = function() {
        bCriteria = checkCriterionValidity();

        if (bCreated) {
            $('#resetteams').prop("disabled", false);
            $('#prettify').prop("disabled", false);
            $('#equalize').prop("disabled", false);
        } else {
            $('#resetteams').prop("disabled", true);
            $('#prettify').prop("disabled", true);
            $('#equalize').prop("disabled", true);
        }

        if (bCriteria) {
            $('#buildteams').prop("disabled", false);
        } else {
            $('#buildteams').prop("disabled", true);
        }

        if ($('#series option').length > 1) {
            $('#serie').hide();
        }
    };
    //******************************************************************************************************************************
    //  Check the validity of criterion
    //******************************************************************************************************************************
    var checkCriterionValidity = function() {
        var criterionGroup = [];
        var aMyCommand = [];

        $("#predicate .criterionWrapper").each(function() {
            var criterion = getCriterionObjectFromView($(this).children(".criterion"));
            criterion.rule = $(this).find(".boolOper").html();
            criterionGroup.push(criterion);
        });

        if (criterionGroup.length == 0) {
            return true;
        }

        for (var c in criterionGroup) {
            criterion = criterionGroup[c];
            aMyCommand = [];
            aMyCommand[0] = criterion['rule']; // Operation Cluster, aggregate,balance,distribute
            aMyCommand[1] = ''; // List of concernee answers
            aMyCommand[2] = criterion['question']; // The id of the question
            for (var a in criterion['answers']) {
                aMyCommand[1] = aMyCommand[1] + criterion['answers'][a] + ',';
            }

            if (aMyCommand[1].length > 0 && aMyCommand[0] != strings.balance) {
                return true;
            }

            // In case of distribute put all answers if none of them are selected
            if (aMyCommand[0] == strings.distribute) {
                return true;
            }

            // Aggregate get all as answers (to be use to find answer in student answers)
            if (aMyCommand[0] == strings.aggregate) {
                return true;
            }

            // Balance get all as answers
            if (aMyCommand[0] == strings.balance) {
                for (var qa in questions[criterion['question']].answers) {
                    if ($.isNumeric(questions[aMyCommand[2]].answers[qa].answer) == false) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    };
    // *****************************************************************************************************************************
    // Restore an saved version of teams of students the previous one or the next one
    // *****************************************************************************************************************************
    var getBalanceQuestion = function() {
        var id = -1;
        for (qa in questions) {
            for (answer in questions[qa].answers) {
                if ($.isNumeric(questions[qa].answers[answer].answer) == true) {
                    id = qa;
                } else {
                    id = -1;
                }
            }
            if(id > -1) return id;
        }
        return id;
    };
    // *****************************************************************************************************************************
    //
    // *****************************************************************************************************************************
    var filterOnAgg = function() {
        var val = $('#aggList').val();
        if (val != '') {
            $('.box_ok').hide();
            $('.box_ko').hide();
            $('.' + val).show();
        } else {
            $('.box_ok').show();
            $('.box_ko').show();
        }
        $('#smnu1').removeClass('active');
        $('#smnu2').removeClass('active');
        $('#smnu1').addClass('active');
    };
    // *****************************************************************************************************************************
    //
    // *****************************************************************************************************************************
    var shuffle = function(array) {
        var tmp, current, top = array.length;
        if(top) {
            while(--top) {
                current = Math.floor(Math.random() * (top + 1));
                tmp = array[current];
                array[current] = array[top];
                array[top] = tmp;
            }
        }
        return array;
    };
    // *****************************************************************************************************************************
    //
    // *****************************************************************************************************************************
    var deleteallred = function() {
        $(".unanswered .studentdel" ).click();
    };
    var keepallred = function() {
        $(".answered  .studentdel" ).click();
    };
    // *****************************************************************************************************************************
    // Restore an saved version of teams of students the previous one or the next one
    // *****************************************************************************************************************************
    var historyGo = function(teamHistoryPos) {
        $('#nbteam').val(JSON.parse(teamHistory[1][0]).length);
        updateTeams(JSON.parse(teamHistory[1][0]).length);

        teamAssignments = JSON.parse(teamHistory[1][0]);
        aTeamMaxAgg = JSON.parse(teamHistory[1][1]);
        teamAvg = JSON.parse(teamHistory[1][2]);
        teamGood = JSON.parse(teamHistory[1][3]);

        synchroniseModelToView();
    };
    // *****************************************************************************************************************************
    // Save the current teams of students
    // *****************************************************************************************************************************
    var historySave = function() {
        teamHistory[1] = [];
        teamHistory[1][0] = JSON.stringify(teamAssignments);
        teamHistory[1][1] = JSON.stringify(aTeamMaxAgg);
        teamHistory[1][2] = JSON.stringify(teamAvg);
        teamHistory[1][3] = JSON.stringify(teamGood);
    };
    //******************************************************************************************************************************
    return {
        init: function() {
            str.get_strings([
                {key: 'criterionquestion', component: 'mod_teamup'},
                {key: 'distribute', component: 'mod_teamup'},
                {key: 'aggregate', component: 'mod_teamup'},
                {key: 'cluster', component: 'mod_teamup'},
                {key: 'balance', component: 'mod_teamup'},
                {key: 'createteams', component: 'mod_teamup'},
                {key: 'analyzeclustercriterion', component: 'mod_teamup'},
                {key: 'analyzeclusterwarning', component: 'mod_teamup'},
                {key: 'analyzeclustersuccess', component: 'mod_teamup'},
                {key: 'analyzeaggregatewarning', component: 'mod_teamup'},
                {key: 'noanswer', component: 'mod_teamup'},
                {key: 'analyzedistributesuccess', component: 'mod_teamup'},
                {key: 'analyzedistributewarning', component: 'mod_teamup'},
                {key: 'analyzedistributecriterion', component: 'mod_teamup'},
                {key: 'analyzebalancewarning', component: 'mod_teamup'},
                {key: 'total', component: 'mod_teamup'},
                {key: 'average', component: 'mod_teamup'},
                {key: 'standarddeviation', component: 'mod_teamup'},
                {key: 'teamupsuccess', component: 'mod_teamup'},
                {key: 'teamupwarning', component: 'mod_teamup'},
                {key: 'averagesuccess', component: 'mod_teamup'},
                {key: 'averagewarning', component: 'mod_teamup'},
                {key: 'bornes', component: 'mod_teamup'},
                {key: 'teamupsuccessnbr', component: 'mod_teamup'},
                {key: 'distributelabel', component: 'mod_teamup'},
                {key: 'aggregatelabel', component: 'mod_teamup'},
                {key: 'clusterlabel', component: 'mod_teamup'},
                {key: 'balancelabel', component: 'mod_teamup'},
                {key: 'distributionmode', component: 'mod_teamup'},
                {key: 'question', component: 'mod_teamup'},
                {key: 'groupTitle', component: 'mod_teamup'},
                {key: 'nbGroupSuccess', component: 'mod_teamup'},
                {key: 'nbStudent', component: 'mod_teamup'},
                {key: 'analyzeaggregatewarningOK', component: 'mod_teamup'},
                {key: 'abc', component: 'mod_teamup'}
            ]).done(function(s) {
                buildstrings(s);
                setup();
                registerEventListeners();
            });
        }
    };
 });
// *********************************************************************************************************************************