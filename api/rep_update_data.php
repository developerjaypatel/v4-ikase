<?php
include("../../eamsjetfiler/datacon.php");
include("../../eamsjetfiler/functions.php"); //TODO: is this needed?
include(__DIR__.DC.'shared'.DC.'eams_gov_parser.php');
EamsGovParser::run(EamsGovParser::REPS);
