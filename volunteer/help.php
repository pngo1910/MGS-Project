<?php  
require('../db/volunteerCheck.php');
?>
<!DOCTYPE HTML>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<?php header('X-UA-Compatible: IE=edge,chrome=1');?>
		<title>MGS Volunteer</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width">

		<!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <link rel="stylesheet" href="../css/normalize.css">
    <link rel="stylesheet" href="../css/main.css">
		<script src="/js/vendor/modernizr-2.6.2.min.js"></script>
	</head>
	<body>
    <div id="resultsbackground">
      <div id="container" class="home">
        <?php require('header.php'); ?>
        <div>   
	          <h2 class="volunteerSpeal">Cemetery Transcription (CT) Module</h2>
	          <p>The Manitoba Genealogical Society designed the CT Module as a handy way to edit an existing Cemetery or create a new Cemetery Transcription while maintaining standards. The volunteer transcriber will be provided with a copy of the CT Module to install on their Microsoft Windows device by the Special Projects Chair of MGS. They will also be provided with both information files on the cemetery and what we currently have in the MANI database. The volunteer transcriber will re-read the cemetery correcting any errors and adding any new or missing information that has been added since the last time the cemetery has been read.</p>	 
          <h4>MGS Cemetery Transcription Standards</h4>
         	<ul>
				<li></li>
			</ul><strong>How to use this Module:
				</br></br>RULES for data entry:</strong>
			<ul>
	<li>Last Name should be in all CAPITALS.</li>
	<li> Make a duplicate entry for maiden names, married names or alternate names. in behind first name in square brackets ""[  ]"" Add the maiden name the same way to the married name.  Each of the different last names (married and maiden) should be entered in a separate entry in the last name field with 	the alternate name being in brackets after the first name. All surnames in all CAPS</li>
	<li>Create a second entry for maiden name and add married name</li>
			</ul>
				<p class = "tab">ID,LastName,FirstName,Birth,Death,PageNumber,CemeteryID,CemID,Typecode, StatusCode</br>
																	   ,BURROW,John William,1890,1964,34,1,1,ct,NEW</br>
																	,BURROW,Eleanor [BURNS],1890,1965,34,1,1,ct,NEW</br>
																    ,BURNS,Eleanor [BURROW],1890,1965,34,1,1,ct,NEW</br></p>

									If known enter before maiden names and Mrs before married names
									</br></br>
		<img class = "tab" src ="help_pic1.png" alt="help1" height="150" /></br>
		</br><strong>Example of entries maiden names</strong>
		</br></br> Do not put a space in names beging with "MC" or "MAC" (e.g. McBEAN and MacDONALD). The Mc and Mac should be mixed case with the remainder being ALL CAPITALS for the last name.
		</br></br>
		Add all titles and nicknames in square brackets after the first name [Dr][Mrs][Billy]
			<img class = "tab" src ="help_pic2.png" alt="help2" height="130" /></br>
		</br><strong>Example of entries with alternate first names</strong>
		<ul>
			<li>Use four digit years 1999.</li>
			<li>If data is missing, leave a blank cell where data should be</li>
			<li>Date Field: The date field contains only the year (e.g. "1872"). In many cases there is no date given</li>
				<li> Location: The location field gives the location of the event itself. It is simply the historic municipality or city in Manitoba. For non-Manitoba locations, we enter what is provided. We use the name given, e.g. Red River, not Winnipeg. In many cases there is no location to use.</li>
		</ul>
		<center><strong>Special Symbols you need to avoid</strong></center>
		</br>
		Do not use any of the following characters, as they have special meanings in Excel and SQL databases, that can automatically make changes to what is entered. i.e changing dates to 5 digit numbers:
		</br></br>
		<style type="text/css">
		.tg  {border-collapse:collapse;border-spacing:0;}
		.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
		.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
		.tg .tg-yw4l{vertical-align:top}
		</style>
		<table class="tg">
		  <tr>
		    <th class="tg-031e"></th>
		    <th class="tg-yw4l">Symbol</th>
		    <th class="tg-yw4l">Remove</th>
		    <th class="tg-yw4l">Replace With</th>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Quotes</td>
		    <td class="tg-yw4l">"</td>
		    <td class="tg-yw4l">Yes</td>
		    <td class="tg-yw4l">Replace with square brackets [ ]</td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Dittos which means <br>"Same as above"<br>- to represent <br>repeated text</td>
		    <td class="tg-yw4l"></td>
		    <td class="tg-yw4l">Replace</td>
		    <td class="tg-yw4l">Each field has to have the content added to it</td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Single Quotes</td>
		    <td class="tg-yw4l">'</td>
		    <td class="tg-yw4l"></td>
		    <td class="tg-yw4l"></td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Commas</td>
		    <td class="tg-yw4l">,</td>
		    <td class="tg-yw4l">Yes</td>
		    <td class="tg-yw4l">A back slash \ in dates</td>SSS
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Asterisk</td>
		    <td class="tg-yw4l">*</td>
		    <td class="tg-yw4l">Remove</td>
		    <td class="tg-yw4l">Leave a blank</td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Periods</td>
		    <td class="tg-yw4l">.</td>
		    <td class="tg-yw4l">Remove</td>
		    <td class="tg-yw4l">Remove decimal in numbers by rounding<br>numbers. For example, round 23.45 to 23.<br>Round ages to the lower number. For <br>example, the age of 30.75 should be rounded<br>to 30.</td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Percent</td>
		    <td class="tg-yw4l">%</td>
		    <td class="tg-yw4l">Remove</td>
		    <td class="tg-yw4l"></td>
		  </tr>
		  <tr>
		    <td class="tg-yw4l">Pipe (a vertical line) </td>
		    <td class="tg-yw4l"></td>
		    <td class="tg-yw4l">Remove</td>
		    <td class="tg-yw4l"></td>
		  </tr>
		</table>
	   </div>
    </div>
  </body>
</html>