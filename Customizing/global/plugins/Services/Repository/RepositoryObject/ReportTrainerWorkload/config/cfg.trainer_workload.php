<?php

$bihourly_staged =
		"		IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL"
		."			,LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time ) )* htid.weight /720000
) *2,8)"
		."			,LEAST(CEIL( 28800* htid.weight /720000) *2,8)"
		."		)/8 ";

$this->top_orgus = array('EVG','UVG');

$this->ignore_roles = array('RTL');

$this->cats = array(
					'central' =>
							array(	'condition'	=> 	" ht.category  = 'Training' AND hc.edu_prog
ram = 'zentrales Training' AND hc.type != 'Virtuelles Training' "
									,'weight' 	=>	$bihourly_staged )
					,'noncentral' =>
							array(	'condition'	=> 	" ht.category  = 'Training' AND hc.edu_prog
ram = 'dezentrales Training' "
									,'weight'	=>	"	IF(htid.end_time IS NOT NUL
L AND htid.start_time IS NOT NULL"

													."		,IF(TIME_TO
_SEC(TIMEDIFF(htid.end_time,htid.start_time))<1800,0.25,LEAST(CEIL(GREATEST (TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time
))-1800,0) /3600* htid.weight/100)*0.25,1))"
													."		,0"
													."	) ")
					,'virtual' =>
							array(	'condition'	=> 	" ht.category  = 'Training' AND hc.edu_prog
ram != 'dezentrales Training' AND hc.type = 'Virtuelles Training' "
									,'weight' 	=> 	" 0.5 ")
					,'vacation' =>
							array(	'condition' => 	$this->gIldb->in('ht.category' ,array(	'Urlaub gen
ehmigt','Urlaub beantragt'),false,'text')
									,'weight'	=> 	" 1 ")
					,'misc'	=>
							array(	'condition' => 	$this->gIldb->in('ht.category' ,array(	'Projekt'
																	,'AD-Begleitung'
																	,'Gewerbe-Arbeitskreis'
																	,'Veranstaltung / Tagung (Zentral)'
																	,'Firmenkunden'
																	,'bAV-Arbeitskreis'
																	,'Trainer- / DBV Klausur (Zentral)'
																	,'Akquise Pilotprojekt'
																	,'FDL-Arbeitskreis'
																	,'Trainer Teammeeting'
																	,'Individuelle Unterstützung SpV/FD'
																	,'AKL-Gespräch'
																	,'Arbeitsgespräch'
																	,'Weiterbildungstage')
																	,false,'text')
									,'weight'	=> 	$bihourly_staged )
					,'od_discussion' =>
							array(	'condition' => 	$this->gIldb->in('ht.category' ,array(	'OD-Gespräch
','OD-FD Meeting'),false,'text')
									,'weight'	=> 	$bihourly_staged )
					,'fd_discussion' =>
							array(	'condition' => 	$this->gIldb->in('ht.category' ,array(	'FD-Gespräch
','FD-MA Teammeeting'),false,'text')
									,'weight'	=> 	$bihourly_staged )
					,'office' =>
							array(	'condition' => 	$this->gIldb->in('ht.category' ,array(	'Büro'),false,'text')
									,'weight'	=> 	$bihourly_staged )
					);

$this->meta_cats = array(
					'training' =>
						array(	'central'
								,'noncentral'
								,'virtual')
					,'operation' =>
						array(	'misc'
								,'od_discussion'
								,'fd_discussion')
					,'office' =>
						array(	'office')
					,'vacation' =>
						array(	'vacation')
					);