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
 * Automatically generated strings for Moodle 2.0 installer
 *
 * Do not edit this file manually! It contains just a subset of strings
 * needed during the very first steps of installation. This file was
 * generated automatically by export-installer.php (which is part of AMOS
 * {@link http://docs.moodle.org/dev/Languages/AMOS}) using the
 * list of strings defined in /install/stringnames.txt.
 *
 * @package   installer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['admindirname'] = 'Cyfeiriadur y Gweinyddwr';
$string['availablelangs'] = 'Y pecynnau iaith sydd ar gael';
$string['chooselanguagehead'] = 'Dewis iaith';
$string['chooselanguagesub'] = 'Dewiswch iaith ar gyfer y broses osod YN UNIG. Gallwch ddewis yr iaith ar gyfer y safle ac ar gyfer defnyddwyr yn nes ymlaen ar sgrin arall.';
$string['dataroot'] = 'Cyfeiriadur Data';
$string['dbprefix'] = 'Llythrennau Blaen Tablau';
$string['dirroot'] = 'Cyfeiriadur Moodle';
$string['environmenthead'] = 'Wrthi\'n profi eich amgylchedd ...';
$string['installation'] = 'Gosod';
$string['langdownloaderror'] = 'Yn anffodus, ni osodwyd yr iaith ganlynol: "{$a}". Bydd y broses osod yn cario ymlaen yn Saesneg.';
$string['memorylimithelp'] = '<p>Mae maint y cof PHP yn eich gweinydd ar hyn o bryd yn {$a}.</p>

<p>Gall hyn arwain at broblemau â\'r cof yn nes ymlaen, yn enwedig 
   os ydych wedi galluogi llawer o fodiwlau a/neu lawer o ddefnyddwyr.</p>

<p>Rydym yn argymell eich bod yn ffurfweddu PHP gyda mwy o gof os yn bosib, megis 40M.  
   Mae sawl ffordd o wneud hyn:</p>
<ol>
<li>Os ydych yn gallu, ceisiwch ail-grynhoi PHP gyda <i>--enable-memory-limit</i>.  
    Bydd hyn yn gadael i Moodle osod maint y cof ei hun.</li>
<li>Os ydych yn gallu mynd i mewn i\'ch ffeil php.ini, gallwch newid y gosodiad <b>memory_limit</b> 
    yn y fan honno i tua 40M. Os nad ydych chi\'n gallu gwneud hyn eich hun, efallai  
    y gallech ofyn i\'ch gweinyddwr wneud hyn i chi.</li>
<li>Ar rai gweinyddion PHP, gallwch greu ffeil .htaccess yng nghyfeiriadur Moodle  
    sy\'n cynnwys y llinell hon:
    <p><blockquote>php_value memory_limit 40M</blockquote></p>
    <p>Fodd bynnag, ar rai gweinyddion bydd hyn yn atal <b>pob</b> tudalen PHP rhag gweithio 
    (bydd gwallau\'n ymddangos pan fyddwch yn edrych ar dudalennau) felly bydd rhaid i chi dynnu\'r ffeil .htaccess file.</p></li>
</ol>';
$string['phpversion'] = 'Fersiwn PHP';
$string['phpversionhelp'] = '<p>Mae angen o leiaf fersiwn PHP 4.3.0 neu 5.1.0 ar Moodle (mae llawer o broblemau gyda 5.0.x).</p>
<p>Ar hyn o bryd, rydych yn rhedeg fersiwn {$a}</p>
<p>Rhaid i chi uwchraddio PHP neu newid i westeiwr â fersiwn diweddarach o PHP!<br/>
(Os oes gennych 5.0.x gallwch hefyd is-raddio i fersiwn 4.4.x)</p>';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = 'Rydych chi\'n gweld y dudalen hon gan eich bod wedi gosod a  
    lansio\'r pecyn <strong>{$a->packname} {$a->packversion}</strong> yn llwyddiannus ar eich cyfrifiadur. Llongyfarchiadau!';
$string['welcomep30'] = 'Mae\'r fersiwn <strong>{$a->installername}</strong> yn cynnwys rhaglenni 
    i greu amgylchedd y gall  <strong>Moodle</strong> weithio ynddo, sef:';
$string['welcomep40'] = 'Mae\'r pecyn hefyd yn cynnwys <strong>Moodle {$a->moodlerelease} ({$a->moodleversion})</strong>.';
$string['welcomep50'] = 'Y trwyddedau perthnasol sy\'n llywodraethu dros yr holl raglenni yn y pecyn hwn. Y pecyn cyflawn yw <strong>{$a->installername}</strong> 
    <a href="http://www.opensource.org/docs/definition_plain.html">open source</a> a chaiff ei ddosbarthu dan y drwydded <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a>.';
$string['welcomep60'] = 'Bydd y tudalennau canlynol yn eich arwain drwy\'r camau syml i  
    ffurfweddu a gosod <strong>Moodle</strong> ar eich cyfrifiadur. Gallwch ddewis derbyn y gosodiadau 
    diofyn, neu gallwch eu newid eich hun ar gyfer eich dibenion chi.';
$string['welcomep70'] = 'Cliciwch y botwm "Nesaf" i fwrw ymlaen i osod <strong>Moodle</strong>.';
$string['wwwroot'] = 'Cyfeiriad ar y we';
