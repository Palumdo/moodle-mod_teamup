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
 * Lang strings for the teamup module.
 *
 * @package    mod_teamup
 * @copyright  UCL
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addnewcriterion']            = '+ Ajouter un critère';
$string['addanewquestion']            = 'Ajouter une nouvelle question à choix multiple';
$string['addnewquestion']             = 'Ajouter au questionnaire';
$string['addnewsubcriterion']         = 'Ajouter nouveau sous critère';
$string['addtogrouping']              = 'Ajouter au groupement';
$string['all']                        = 'tous';
$string['allstudents']                = 'Tous les étudiants';
$string['and']                        = 'ET';
$string['answertype']                 = 'Restriction sur les réponses';
$string['answers']                    = 'Réponses';
$string['any']                        = 'certains';
$string['assignrandomly']             = 'Assigner aléatoirement';
$string['buildteams']                 = 'Composer les groupes';
$string['confirmgroupbuilding']       = 'Êtes-vous sûr de vouloir créer vos groupes maintenant ?';
$string['criterionquestion']          = 'Concerne les étudiants ayant répondu {question} :';
$string['creategroups']               = 'Créer les groupes dans Moodle';
$string['dontassigngrouptogrouping']  = 'N\'assignez pas les groupes à ce groupement';
$string['import']                     = 'Importer';
$string['importquestionsfrom']        = 'Importer les questions de';
$string['intro']                      = 'Introduction';
$string['intro_help']                 = 'Message d\'introduction à votre activité de choix assisté de groupe';
$string['modulename']                 = 'Formation de Groupes Assistée (Team Up)';
$string['modulenameplural']           = 'Team Up';
$string['modulename_help']            = 'Ce module est un outil pour affecter des étudiants à des groupes Moodle, créés en fonction de leurs réponses à une série de questions que vous spécifiez.
L\'idée est de formuler des questions à choix multiple avec d\'éventuelles restrictions afin de répartir les étudiants dans les groupes sur base de 4 logiques :
* grouper les individus semblables
* disperser les individus semblables
* éviter les minorités
* équilibrer le niveau (sur base d\'une réponse chiffrée).
L\'outil distribue les étudiants de manière égale parmi un nombre spécifié de groupes. 
Ce plugin est un fork du module Moodle <a href=\"https://moodle.org/plugins/mod_teambuilder\" target=\"_blank\"> Team Builder </a> dont notre module copie l\'interface. 
L\'algorithme de répartition et ses options s\'inspirent ceux du projet Open Source <a href=\"https://www.groupeng.org/GroupENG\" target=\"_blank\"> GroupEng </a>. ';
$string['name']                       = 'Nom';
$string['noeditingafteropentime']     = 'Vous ne pouvez pas éditer le questionnaire d\'un Team up s\'il a déjà été ouvert.';
$string['none']                       = 'aucun';
$string['noneedtocomplete']           = 'Vous n\'avez pas besoin de remplir ce questionnaire Team Up.';
$string['notopen']                    = 'Ce questionnaire Team Up n\'est pas ouvert.';
$string['numberofteams']              = 'Nombre de groupes';
$string['or']                         = 'OU';
$string['pluginadministration']       = 'Administration de Team Up';
$string['pluginname']                 = 'Team Up';
$string['prefixteamnames']            = 'Préfixer les noms des groupes avec le nom du groupement';
$string['prioritize']                 = 'Prioriser';
$string['prioritizeequal']            = 'Nombre de groupes égaux #';
$string['prioritizemostcriteria']     = 'la plupart des critères rencontrés';
$string['preview']                    = 'Prévisualisation';
$string['questionnaire']              = 'Questionnaire';
$string['resetteams']                 = 'Réinitialiser';
$string['savequestionnaire']          = 'Enregistrer le questionnaire';
$string['selectany']                  = 'Aucune';
$string['selectatleastone']           = 'Au moins une coche';
$string['selectone']                  = 'Exactement 1 coche';
$string['selecttwo']                  = 'Exactement 2 coches';
$string['selectthree']                = 'Exactement 3 coches';
$string['selectfour']                 = 'Exactement 4 coches';
$string['selectfive']                 = 'Exactement 5 coches';
$string['teamup']                     = 'Team Up';
$string['teamup:addinstance']         = 'Ajouter un nouveau module teamup';
$string['teamup:build']               = 'Créer des groupes à partir des réponses du questionnaire';
$string['teamup:create']              = 'Créer un questionnaire';
$string['teamup:respond']             = 'Répondre au Questionnaire';
$string['unassignedtoteams']          = 'Non affecté aux groupes';
$string['distribute']                 = 'Distri';
$string['aggregate']                  = 'aggregate';
$string['cluster']                    = 'cluster';
$string['balance']                    = 'balance';
$string['replay']                     = 'Rejoue sans reset';
$string['bidon']                      = 'bidon #';
$string['presentation']               = '<h3>Présentation du module</h3>
<p>
L\'activité  Formation de groupes assitée (Team Up) permet de composer un questionnaire avec des questions à choix multiple avec possibilité de restrictions sur les réponses. 

<p>
Le premier onglet de l\'activité, <b>Questionnaire</b>, permet de créer les questions pour les étudiants.<br>
<b>Prévisualisation des questions</b>, le second onglet, permet de voir le formulaire auquel les étudiants vont devoir répondre. <br>
Le dernier onglet,<b>Prévisualiser</b>, permet la création des groupes par l\'enseignant.<br>
</p>

<p>
La création des groupes se fait en deux étapes. La première étape est une simulation. 
Pendant la simulation, il est possible de modifier les critères, les réordonner et de déplacer les étudiants manuellement d\'un groupe à l\'autre. 
Et l\'étape suivante est la création effective des groupes dans Moodle.<br>
Il ne faut donc pas oublier d\'appuyer sur <button type=\"button\" class=\"creategroups\" style=\"font-size: 1.0em;\" id=\"\">Créer les groupes dans Moodle</button> pour finaliser la création.<br>
</p>

<p>
Il y a quatre opérateurs de base pour créer les groupes.<br>
<table class="mod-teamup-table">
  <tr><td>Grouper les individus semblables</td><td>= Former des groupes dont les membres sont similaires concernant des critères définis. Création de groupes homogènes. Appliqué à des valeurs discrètes, sans obligation qu\'elles soient numériques.</td></tr>
  <tr><td>Disperser les individus semblables</td><td>= Répartir les étudiants répondant à un critère à travers les groupes. Appliqué à des valeurs discrètes, sans obligation qu\'elles soient numériques.</td></tr>
  <tr><td>Eviter les minorités</td><td>= Répartir les étudiants de manière à ce qu\'au moins deux étudiants partageant un critère soient dans le même groupe (notamment concernant les minorités). Appliqué à des valeurs discrètes, sans obligation qu\'elles soient numériques.</td></tr>
  <tr><td>Equilibrer le niveau</td><td>= Créer des groupes qui soient "justes", dont les forces totales sont similaires dans tous les groupes (généralement basé sur des résultats académiques). Appliqué à des valeurs numériques (continues et discrètes).</td></tr>
</table>
</p>

<p>
Lorsque vous prévisualisez une répartition, vous pouvez cliquez sur le bloc associé à un étudiant, vous voir ses informations et réponses dans une info-bulle.<br>
Si un étudiant ne doit pas entrer dans le répartition, vous pouvez le supprimer en cliquant sur la croix à côté de son nom.<br>
</p>
<u>La barre d\'action :</u><br>
Nombre de groupes :<input id="nbteam" min="1" style="width:40px;height:21px;margin-top:5px;margin-right:5px;" value="31" type="number" disabled="">31 / 123(4)</span>&nbsp;<button type="button" id="buildteams" class=""><strong>Prévisualisation</strong></button>&nbsp;<button type="button" id="resetteams" class="">Réinitialiser</button>&nbsp;<button type="button" id="prettify" style="">Optimiser</button>&nbsp;<button type="button" id="serie"">Série</button>&nbsp;<button type="button" id="equalize" style="">Egaliser</button>
<ul>
  <li>Le nombre de groupes détermine le nombre d\'étudiants approximatif par groupe ex: 123 étudiants dans 31 groupes donnent 4. Indiqué entre parenthèses à côté du nombre d\'étudiants.</li>
  <li>Prévisualiser : Ce bouton crée les groupes selon les critères dans la prévisualisation.</li>
  <li>Réinitialiser : Ce bouton remet tous les étudiants hors des groupes dans la partie <b>non affecté aux groupes</b></li>
  <li>Optimiser : Ce bouton essaye d\'améliorer la répartition des groupes en fonction des critères. Le succès n\'est pas garanti mais vous pouvez répéter plusieurs fois l\'opération.</li>     
  <li>Egaliser : Force le nombre d\'étudiants par groupes  indépendamment des critères basés sur le nombre entre parenthèses. Parfois nécessaire après une optimisation.</li>
</ul>
</p>
';
$string['createteams']                = 'Créer les groupes';
$string['save']                       = 'Sauver';
$string['prettify']                   = 'Optimiser';
$string['abc']                        = 'Série';
$string['groupcreationsuccess']       = 'Les groupes ont été créés avec succès.';
$string['analyzeclustercriterion']    = 'Le nombre d\'étudiants répondant à ces critères est de <b>{nbstudent}</b> répartis dans <b>{nbteam}</b> groupes.';
$string['analyzeclusterwarning']      = '<br><span style="color:red;">Attention, il ne pourra pas avoir deux étudiants dans tous les groupes avec ces critères.</span>';
$string['analyzeclustersuccess']      = '<br>Il  pourra y avoir deux étudiants dans tous les groupes avec ces critères.';
$string['analyzeaggregatewarning']    = '<br><span style="color:{color};"> Critère {answer} : <b>{nbstudent}</b>=> Nombre de groupes probables : {nbgroup} composé de {nbstudentgroup} étudiants avec {reste} étudiants éparpillés.</span>';
$string['analyzeaggregatewarningOK']    = '<br><span style="color:{color};"> Critère {answer}</td><td>: <b>{nbstudent}</b>=>Nombre de groupes probables : {nbgroup} composé de {nbstudentgroup} étudiants.</span>';
//$string['analyzeaggregatewarning']    = '<tr style="color:{color};"><td> Critère {answer}</td><td>:<b>{nbstudent}</b>=></td><td>Nombre de groupes probables :{nbgroup}</td><td>&nbsp;composé de {nbstudentgroup} étudiants avec {reste} étudiants éparpillés</td></tr>';
//$string['analyzeaggregatewarningOK']  = '<tr style="color:{color};"><td> Critère {answer}</td><td>:<b>{nbstudent}</b>=></td><td>Nombre de groupes probables :{nbgroup}</td><td>&nbsp;composé de {nbstudentgroup} étudiants</td></tr>';
$string['noanswer']                   = 'Cet étudiant n\'a pas répondu.';
$string['analyzedistributesuccess']   = 'Pas de problème pour le distribuer dans <b>{nbteam}</b> groupes';
$string['analyzedistributewarning']   = '<span style="color:red;">Attention problème pour distribuer dans <b>{nbteam}</b> groupes</span>';
$string['analyzedistributecriterion'] = '<br>Critère {answer} : <b>{nbstudent}</b> => <b>{status}</b>';
$string['analyzebalancewarning']      = '<span style="color:red;">Le résultat n\'est pas  numérique, choisissez une question appropriée, s.v.p.</span>';
$string['total']                      = 'Total';
$string['average']                    = 'Moyenne';
$string['standarddeviation']          = 'Ecart-type';
$string['teamupsuccess']              = 'groupement réalisé avec succès';
$string['teamupwarning']              = 'groupement défaillant';
$string['averagewarning']             = 'La moyenne est trop écartée de la moyenne globale';
$string['averagesuccess']             = 'La moyenne est assez proche de la moyenne globale';
$string['bornes']                     = 'Bornes';
$string['teamupsuccessnbr']           = 'Nombre de groupes réalisés avec succès';
$string['summary']                    = 'Résumé';
$string['distributionmode']           = 'Mode de distribution';
$string['distributelabel']            = 'Disperser les individus semblables';
$string['aggregatelabel']             = 'Grouper les individus semblables';
$string['clusterlabel']               = 'Eviter les minorités';
$string['balancelabel']               = 'Equilibrer le niveau';
$string['question']                   = 'Question';
$string['pleasewait']                 = 'Merci de votre patience';
$string['groupTitle']                 = 'Groupe';
$string['previewQuestion']            = 'Prévisualisation des questions';
$string['nbGroupSuccess']             = 'Nombre de groupes réalisés avec succès';
$string['nbStudent']                  = 'Nombre d\'étudiants';
$string['reportDetail']               = 'Rapport détaillé';
$string['groupNoOptimal']             = 'Groupes non optimaux';
$string['aggFilter']                  = 'Filtrer sur ';
$string['bankQuestion']               = 'Banque de questions';
$string['equalize']                   = 'Egaliser';
$string['groupName']                  = 'Nom des groupes';
$string['groupSchemaName']            = 'Schéma de dénomination du groupe';
$string['helpserie']                  = '<p>#
                                          Le module de création de groupes peut être utilisé pour créer des séries.<br>
                                          Les séries sont des groupes d\'étudiants créer avec comme seul critère l\'ordre alphabétique.<br>
                                          Ces groupes sont préfixés par le terme "Série" ex:"Série 01"<br>
                                          L\'utilité des groupes séries est qu\'ils sont utilisés comme filtres sur la liste des étudiants.<br>
                                          Par défaut, vous créez les groupes sur l\'ensemble des étudiants du cours.<br> 
                                          Les séries permettent de les créer sur un sous-groupe d\'étudiants particuliés.<br>
                                          C\'est utile pour de grosses classes, entre autres.<br>
                                          Il est possible de créer des séries sois-même indépendamment du bouton série.<br>
                                          A la création du groupe dans Moodle, quand le nom de ce groupe vous est demandé. Commencez le nom du groupe par Série ex:"Série Classe réelle".<br>
                                          Cella vous permet de réduire le mombre de particpant au cours en tenant compte des assistants ou d\'étudiants qui ne sont effectivement pas présent.<br>
                                          Une fois les séries créées, le bouton série disparait. Pour le faire réapparaitre si néccessaire, il faut supprimer l\'ensemble des groupes séries.<br>
                                          Mais, il reste toujours possible de les créer en préfixant le nom du groupe par Série.<br>
                                        </p>';
$string['helpserie_help']             = 'Concept de série';
$string['answersSubmitted']           = 'Vos réponses ont été envoyées.';


$string['oneOption']                  = 'Sélectionnez <strong> un </strong> des éléments suivants:';
$string['anyOption']                  = 'Sélectionnez l\'un (ou aucun) des éléments suivants:';
$string['atleastoneOption']           = 'Sélectionnez <strong> au moins un </strong> des éléments suivants:';
$string['twoOption']                  = 'Sélectionnez deux des éléments suivants:';
$string['threeOption']                = 'Sélectionnez trois des éléments suivants:';
$string['fourOption']                 = 'Sélectionnez quatre des éléments suivants:';
$string['fiveOption']                 = 'Sélectionnez cinq des éléments suivants:';

$string['deleteAllRed']               = 'Supprimer tous les étudiants sans réponse';
$string['keepAllRed']                 = 'Garder uniquement les étudiants sans réponse';
$string['equalizeHelp']               = 'Force le nombre d\'étudiants par groupes  indépendamment des critères basés sur le nombre entre parenthèses. Parfois nécessaire après une optimisation';
$string['prettifyHelp']               = 'Ce bouton essaye d\'améliorer la répartition des groupes en fonction des critères. Le succès n\'est pas garanti mais vous pouvez répéter plusieurs fois l\'opération';

$string['namingscheme_help']          = 'Le caractère arobase (@) peut être utilisé pour créer des groupes contenant des lettres. Par exemple, « Groupe @ » générera des groupes nommés « Groupe A », « Groupe B », « Groupe C », etc.

Le caractère dièse (#) peut être utilisé pour créer des groupes contenant des nombres. Par exemple, « Groupe # » générera des groupes nommés « Groupe 1 », « Groupe 2 », « Groupe 3 », etc.

si vous utilisez un critère pour grouper les individus semblables, vous pouvez faire apparaître l\'option associée dans le nom du groupe en utilisant le caractère "*", exemple de schéma de dénomination de groupe conseillé : "Groupe # - *"
(Pensez à définir des options assez courtes pour ce critère pour éviter les noms de groupe trop long.)';