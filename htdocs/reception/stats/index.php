<?php
/* Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2018	   Quentin Vial-Gouteyron    <quentin.vial-gouteyron@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *     \file       htdocs/reception/stats/index.php
 *     \ingroup    reception
 *     \brief      Page with reception statistics
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/reception/class/reception.class.php';
require_once DOL_DOCUMENT_ROOT.'/reception/class/receptionstats.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';

$WIDTH=DolGraph::getDefaultGraphSizeForStats('width');
$HEIGHT=DolGraph::getDefaultGraphSizeForStats('height');

$userid=GETPOST('userid', 'int');
$socid=GETPOST('socid', 'int');
// Security check
if ($user->societe_id > 0)
{
    $action = '';
    $socid = $user->societe_id;
}

$nowyear=strftime("%Y", dol_now());
$year = GETPOST('year')>0?GETPOST('year'):$nowyear;
//$startyear=$year-2;
$startyear=$year-1;
$endyear=$year;

$langs->load("reception");
$langs->load("other");
$langs->load("companies");


/*
 * View
 */

$form=new Form($db);

llxHeader();

print load_fiche_titre($langs->trans("StatisticsOfReceptions"), $mesg);


dol_mkdir($dir);

$stats = new ReceptionStats($db, $socid, $mode, ($userid>0?$userid:0));

// Build graphic number of object
$data = $stats->getNbByMonthWithPrevYear($endyear, $startyear);
//var_dump($data);exit;
// $data = array(array('Lib',val1,val2,val3),...)


if (!$user->rights->societe->client->voir || $user->societe_id)
{
    $filenamenb = $dir.'/receptionsnbinyear-'.$user->id.'-'.$year.'.png';
    if ($mode == 'customer') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsnbinyear-'.$user->id.'-'.$year.'.png';
    if ($mode == 'supplier') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsnbinyear-'.$user->id.'-'.$year.'.png';
}
else
{
    $filenamenb = $dir.'/receptionsnbinyear-'.$year.'.png';
    if ($mode == 'customer') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsnbinyear-'.$year.'.png';
    if ($mode == 'supplier') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsnbinyear-'.$year.'.png';
}

$px1 = new DolGraph();
$mesg = $px1->isGraphKo();
if (! $mesg)
{
    $px1->SetData($data);
    $px1->SetPrecisionY(0);
    $i=$startyear;$legend=array();
    while ($i <= $endyear)
    {
        $legend[]=$i;
        $i++;
    }
    $px1->SetLegend($legend);
    $px1->SetMaxValue($px1->GetCeilMaxValue());
    $px1->SetMinValue(min(0, $px1->GetFloorMinValue()));
    $px1->SetWidth($WIDTH);
    $px1->SetHeight($HEIGHT);
    $px1->SetYLabel($langs->trans("NbOfReceptions"));
    $px1->SetShading(3);
    $px1->SetHorizTickIncrement(1);
    $px1->SetPrecisionY(0);
    $px1->mode='depth';
    $px1->SetTitle($langs->trans("NumberOfReceptionsByMonth"));

    $px1->draw($filenamenb, $fileurlnb);
}

// Build graphic amount of object
/*
$data = $stats->getAmountByMonthWithPrevYear($endyear,$startyear);
//var_dump($data);
// $data = array(array('Lib',val1,val2,val3),...)

if (!$user->rights->societe->client->voir || $user->societe_id)
{
    $filenameamount = $dir.'/receptionsamountinyear-'.$user->id.'-'.$year.'.png';
    if ($mode == 'customer') $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsamountinyear-'.$user->id.'-'.$year.'.png';
    if ($mode == 'supplier') $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsamountinyear-'.$user->id.'-'.$year.'.png';
}
else
{
    $filenameamount = $dir.'/receptionsamountinyear-'.$year.'.png';
    if ($mode == 'customer') $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsamountinyear-'.$year.'.png';
    if ($mode == 'supplier') $fileurlamount = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsamountinyear-'.$year.'.png';
}

$px2 = new DolGraph();
$mesg = $px2->isGraphKo();
if (! $mesg)
{
    $px2->SetData($data);
    $i=$startyear;$legend=array();
    while ($i <= $endyear)
    {
        $legend[]=$i;
        $i++;
    }
    $px2->SetLegend($legend);
    $px2->SetMaxValue($px2->GetCeilMaxValue());
    $px2->SetMinValue(min(0,$px2->GetFloorMinValue()));
    $px2->SetWidth($WIDTH);
    $px2->SetHeight($HEIGHT);
    $px2->SetYLabel($langs->trans("AmountOfReceptions"));
    $px2->SetShading(3);
    $px2->SetHorizTickIncrement(1);
    $px2->SetPrecisionY(0);
    $px2->mode='depth';
    $px2->SetTitle($langs->trans("AmountOfReceptionsByMonthHT"));

    $px2->draw($filenameamount,$fileurlamount);
}
*/

/*
$data = $stats->getAverageByMonthWithPrevYear($endyear, $startyear);

if (!$user->rights->societe->client->voir || $user->societe_id)
{
    $filename_avg = $dir.'/receptionsaverage-'.$user->id.'-'.$year.'.png';
    if ($mode == 'customer') $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsaverage-'.$user->id.'-'.$year.'.png';
    if ($mode == 'supplier') $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsaverage-'.$user->id.'-'.$year.'.png';
}
else
{
    $filename_avg = $dir.'/receptionsaverage-'.$year.'.png';
    if ($mode == 'customer') $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstats&file=receptionsaverage-'.$year.'.png';
    if ($mode == 'supplier') $fileurl_avg = DOL_URL_ROOT.'/viewimage.php?modulepart=receptionstatssupplier&file=receptionsaverage-'.$year.'.png';
}

$px3 = new DolGraph();
$mesg = $px3->isGraphKo();
if (! $mesg)
{
    $px3->SetData($data);
    $i=$startyear;$legend=array();
    while ($i <= $endyear)
    {
        $legend[]=$i;
        $i++;
    }
    $px3->SetLegend($legend);
    $px3->SetYLabel($langs->trans("AmountAverage"));
    $px3->SetMaxValue($px3->GetCeilMaxValue());
    $px3->SetMinValue($px3->GetFloorMinValue());
    $px3->SetWidth($WIDTH);
    $px3->SetHeight($HEIGHT);
    $px3->SetShading(3);
    $px3->SetHorizTickIncrement(1);
    $px3->SetPrecisionY(0);
    $px3->mode='depth';
    $px3->SetTitle($langs->trans("AmountAverage"));

    $px3->draw($filename_avg,$fileurl_avg);
}
*/


// Show array
$data = $stats->getAllByYear();
$arrayyears=array();
foreach($data as $val) {
	if (! empty($val['year'])) {
		$arrayyears[$val['year']]=$val['year'];
	}
}
if (! count($arrayyears)) $arrayyears[$nowyear]=$nowyear;

$h=0;
$head = array();
$head[$h][0] = DOL_URL_ROOT . '/commande/stats/index.php?mode='.$mode;
$head[$h][1] = $langs->trans("ByMonthYear");
$head[$h][2] = 'byyear';
$h++;

$type='reception_stats';

complete_head_from_modules($conf, $langs, null, $head, $h, $type);

dol_fiche_head($head, 'byyear', $langs->trans("Statistics"), -1);


print '<div class="fichecenter"><div class="fichethirdleft">';


//if (empty($socid))
//{
	// Show filter box
	print '<form name="stats" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="mode" value="'.$mode.'">';

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="liste_titre" colspan="2">'.$langs->trans("Filter").'</td></tr>';
	// Company
	print '<tr><td class="left">'.$langs->trans("ThirdParty").'</td><td class="left">';
	if ($mode == 'customer') $filter='s.client in (1,2,3)';
	if ($mode == 'supplier') $filter='s.fournisseur = 1';
	print $form->select_company($socid, 'socid', $filter, 1, 0, 0, array(), 0, '', 'style="width: 95%"');
	print '</td></tr>';
	// User
	print '<tr><td class="left">'.$langs->trans("CreatedBy").'</td><td class="left">';
	print $form->select_dolusers($userid, 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth300');
	print '</td></tr>';
	// Year
	print '<tr><td class="left">'.$langs->trans("Year").'</td><td class="left">';
	if (! in_array($year, $arrayyears)) $arrayyears[$year]=$year;
	if (! in_array($nowyear, $arrayyears)) $arrayyears[$nowyear]=$nowyear;
	arsort($arrayyears);
	print $form->selectarray('year', $arrayyears, $year, 0);
	print '</td></tr>';
	print '<tr><td class="center" colspan="2"><input type="submit" name="submit" class="button" value="'.$langs->trans("Refresh").'"></td></tr>';
	print '</table>';
	print '</form>';
	print '<br><br>';
//}

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre" height="24">';
print '<td class="center">'.$langs->trans("Year").'</td>';
print '<td class="right">'.$langs->trans("NbOfReceptions").'</td>';
/*print '<td class="center">'.$langs->trans("AmountTotal").'</td>';
print '<td class="center">'.$langs->trans("AmountAverage").'</td>';*/
print '</tr>';

$oldyear=0;
foreach ($data as $val)
{
	$year = $val['year'];
	while (! empty($year) && $oldyear > $year+1)
	{ // If we have empty year
		$oldyear--;


		print '<tr class="oddeven" height="24">';
		print '<td class="center"><a href="'.$_SERVER["PHP_SELF"].'?year='.$oldyear.'&amp;mode='.$mode.'">'.$oldyear.'</a></td>';

		print '<td class="right">0</td>';
		/*print '<td class="right">0</td>';
		print '<td class="right">0</td>';*/
		print '</tr>';
	}

	print '<tr class="oddeven" height="24">';
	print '<td class="center">';
	if ($year) print '<a href="'.$_SERVER["PHP_SELF"].'?year='.$year.'&amp;mode='.$mode.'">'.$year.'</a>';
	else print $langs->trans("ValidationDateNotDefinedEvenIfReceptionValidated");
	print '</td>';
	print '<td class="right">'.$val['nb'].'</td>';
	/*print '<td class="right">'.price(price2num($val['total'],'MT'),1).'</td>';
	print '<td class="right">'.price(price2num($val['avg'],'MT'),1).'</td>';*/
	print '</tr>';
	$oldyear=$year;
}

print '</table>';


print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


// Show graphs
print '<table class="border" width="100%"><tr valign="top"><td class="center">';
if ($mesg) { print $mesg; }
else {
    print $px1->show();
    print "<br>\n";
    /*print $px2->show();
    print "<br>\n";
    print $px3->show();*/
}
print '</td></tr></table>';


print '</div></div></div>';
print '<div style="clear:both"></div>';

dol_fiche_end();



// TODO USe code similar to commande/stats/index.php instead of this one.
/*
print '<table class="border" width="100%">';
print '<tr><td class="center">'.$langs->trans("Year").'</td>';
print '<td width="40%" class="center">'.$langs->trans("NbOfReceptions").'</td></tr>';

$sql = "SELECT count(*) as nb, date_format(date_reception,'%Y') as dm";
$sql.= " FROM ".MAIN_DB_PREFIX."reception";
$sql.= " WHERE fk_statut > 0";
$sql.= " AND entity = ".$conf->entity;
$sql.= " GROUP BY dm DESC";

$resql=$db->query($sql);
if ($resql)
{
    $num = $db->num_rows($resql);
    $i = 0;
    while ($i < $num)
    {
        $row = $db->fetch_row($resql);
        $nbproduct = $row[0];
        $year = $row[1];
        print "<tr>";
        print '<td class="center"><a href="month.php?year='.$year.'">'.$year.'</a></td><td class="center">'.$nbproduct.'</td></tr>';
        $i++;
    }
}
$db->free($resql);

print '</table>';
*/

print '<br>';
print '<i>'.$langs->trans("StatsOnReceptionsOnlyValidated").'</i>';

llxFooter();

$db->close();
