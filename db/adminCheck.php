<?php

	require('memberCheck.php');

	if($_SESSION['SpecialProject'] == 1)
		{}

	elseif(!in_array($_SESSION['access'], [5, 3, 4]))
		header('location: /member/');


