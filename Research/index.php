<?php
	$params = "";
	if(isset($_GET['name']) && $_GET['name'] === "login"){
		require('../db/loginCheck.php');
		require('../db/memberConnection.php');
		require('../errorReporter.php');
		$qry = "SELECT MemberNum, Verified FROM Members WHERE Username = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($_SESSION['uname']));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		while($row = sqlsrv_fetch_array($stmt)){
			$memberNum = $row['MemberNum'];
			$verified = $row['Verified'];
		}

		$qry = "SELECT Expiry FROM Membership WHERE MemberNum = ?";
		$stmt = sqlsrv_query($userConn, $qry, array($memberNum));
		if($stmt === false) errorReport(sqlsrv_errors(), __FILE__, __LINE__);
		$expiry = sqlsrv_fetch_array($stmt)['Expiry']->format("Y-m-d");

		if($expiry < date("Y-m-d") && $verified != 1){
			$_SESSION['error'] = "You must renew your account before accessing this page.";
			header("location: /myAccount/");
		}

		$params = "?name=login";
	}
?>
<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
    	<title>Manitoba Genealogical Society</title>
	    <meta name="description" content="">
	    <meta name="viewport" content="width=device-width">
	    <link rel="stylesheet" href="/css/normalize.css">
	    <link rel="stylesheet" href="/css/main.css">
	</head>
	<body>


		<div id="resultsbackground">

        <div id="container" class="home">

		<?php require('../header.php'); ?>


		<div class="content">
			<h1>MGS Research Packages</h1> Choose from either the Basic Research or the Custom Search packages below. (prices effective as of May 1, 2015) <p>
				<strong> Directions: </strong> Select the package you need by clicking on the "Start Basic Package" button or the "Start Custom Package" button. This will take you to the proper entry form. Once there, complete the form and finalize the process by clicking on the "Checkout" button at the bottom of the form. You will be presented with a page containing the details of your order so that you can confirm what you have entered. When you are ready to pay click on the "Pay Now" button to be taken to the PayPal screen where you have a choice to use your PayPal account or select to pay by Credit card without creating a PayPal account.</p>
			<p>
<h2>Research Assistance
</h2><p>
If you are seeking assistance from the Manitoba Genealogical Society (MGS), you need to identify your problems before we can help you.  Although we have resources from many countries, our main resources and collections are those about the province of Manitoba.  We can however point you in the right direction when and if your research indicates that your Manitoba roots have been exhausted and you need to move on to another province or country.

Your first course of action should be to follow all possible leads – family, home and community sources, and oral interviews with relatives just to mention a few.  Visit the MGS Getting Started section on this website.  It will introduce you to the beginnings of your family’s story.  Check also the Library and Resource Centre for holdings to assist you in your research.  Organize your material before you visit our library or contact us.  This will make it easier for our volunteers to help you.  Use only copies, not original documents when you visit us or send requests through the mail.

If you wish to have assistance from MGS volunteers your next step is to contact us by selecting the package below that you require. MGS will undertake genealogical research within our sources on a fee-for-service basis
			<p>
<h1>Basic Research Service Package</h1><p>

MGS will use the information available in its Library/Resource Centre to conduct a “one name”* search applicable to your inquiry for a prepaid fee of $75.00 for non membeers and $60.00 for members.  To help us get started, please complete the Research Form  once you select the Start Basic Package button below.  You can use Paypal on this site to make payment or to just use your credit card without joining PayPal. </p>
<form action="basicPackage.php<?= $params ?>" method="POST">
<input type="submit" value="Start Basic Package">
</form> click this button to order this package.
<h2>Sources Available</h2><p>				

<ul>
<li>Cemetery Index – transcriptions on tombstones of over 1365 cemeteries throughout Manitoba. (A list of cemeteries is available on our website.)</li>
<li>Manitoba Crown Lands Registry – index listing land description for major land grants and homesteads. (Copy of index will be enclosed with research.)</li>
<li>Early Newspaper Obituary Index – births, marriages and deaths found in selected rural Manitoba newspapers 1859 – 1887 and in Winnipeg newspapers from 1859 to mid 1890s.</li>
<li>Manitoba Local History Books – approximately 900 books that contain family histories. The relevant locality must be listed on your research request.</li>
<li>Census Records</li>
<li>1870 Red River Census – contains information about all household members including names, ages, father’s name, place of birth, religion, citizenship. 1881 Manitoba Census – lists names of household members, ages, place of birth, occupations.</li>
<li>1891 Manitoba Census – lists names of household members, ages, place of birth, religion, occupation, parents’ birthplaces.</li>
<li>1901 Manitoba Census – lists names of household members, full birthdates, year of immigration, religion, occupation.</li>
<li>1906 Manitoba Census – lists names of household members, full birthdates, year of immigration, religion, occupation.</li>
<li>1911 Manitoba Census – lists names of household members, birth month and year, place of birth, year of immigration, year of naturalization, race, nationality, religion, and occupation.</li>
<li>1916 Manitoba Census – lists names of household members, age, place of birth, year of immigration, year of naturalization, race, nationality, religion and occupation.</li>
<li>1921 Manitoba Census – lists names of household members, age, place of birth, year of immigration, year of naturalization, race, nationality, religion and occupation.</li>
<li>Henderson Directories – Manitoba addresses and residents from 1876 to 1908, and Winnipeg addresses and residents from 1908 to end of publication in 1999. (Fee includes a 5-year search span.)</li>
<li>Manitoba Telephone Directories – Various Manitoba telephone directories for both Winnipeg City and rural areas from approximately 1950 onwards. (Gaps in coverage exist)</li>
<li>Appropriate Ancestry.com searches (e.g. Canadian Voter Lists).</li>
<li>Appropriate Newspapearchives.com searches (e.g. obituaries and social events) – mainly Winnipeg.</li>
<li>Anglican Marriage, Baptism and Death Registers – over 60 church indexes from the Diocese of Rupert’s Land. The registers list parents’ names, date and place of marriage or baptism. Years range from 1813 to 1925.</li>
<li>Some Catholic Marriage Registers – 30 Manitoba registers that list parents’ names, date and place of marriage. Years range from 1834 to 1982.</li>
<li>United Church Archives Index – marriage, baptism and burial indexes for a large number of Manitoba and N.W. Ontario charges. Includes date and place, spouse, if a marriage, and parents, if a birth.</li>
<p>
In addition to the sources in our library, we have access to many internet web-sites that may be applicable to your research request.  We will advise you of any possible further research and costs that would be of use to you when we respond to your query.

<p>This search will give you a minimun of 5 hours of a volunteer researcher's time and will cover photocopying and postage up to a $5.00 maximum.</p>
<form action="basicPackage.php<?= $params ?>" method="POST">
<input type="submit" value="Start Basic Package">
</form> click this button to order this package.
<p>
			
<h1>Or a Custom Search Package</h1>
<p>We will do one name search in any of the above sources or those listed below for a fee of $10 for <u>each</u> source:</p>
<ul>
<li>Anglican Marriage and Baptism Registers - Over 60 church indexes from the Diocese of Rupertsland (ranging from 1813 to 1925). Parents' names are listed as well as date and place of marriage or baptism.</li>
<li>Catholic Marriage and Baptism Register - 30 Manitoba registers that list the parents' names, date and place of marriage. Years range from 1834 to 1982.</li>
<li>United Church Archives - marriage, baptism and burial indexes for a large number of Manitoba and N.W Ontario charges</li>
</ul>
<form action="customPackage.php<?= $params ?>" method="POST">
<input type="submit" value="Start Custom Package">
</form> click this button to order this package.
<h2>Additonal Information</h2>
<p>
In addition to sources in our library, we have access to many Internet websites(some free and some we subscribe to for a fee) that could be used where we feel they may be of help in collecting information for you. We will advise of you any possible further research and cost that would be useful to you when we respond to your query.
</p>
<h3>Payment </h3>
<p>Payment must be in advance. Please note that the time spent by our volunteers when no useful information is found is just as valuable as the time spent where we do find useful information. No refunds will be made.</p>
<p>
On this website PayPal credit card payment is accepted. Note that MGS inhouse and mailing orders will only accept personal cheques, money orders or bank drafts. Please do not send cash through the mail.
<a href="">Download the Search Request Form</a> and mail your payment to:
</p>
<p><strong>Manitoba Genealogical Society Inc.<br />
Unit E - 1045 St. James St.<br />
Winnipeg, MB<br />
Canada R3H 1B1</strong></p>
<h2>Requesting our Services</h2>	
<p>While we would like to get back to you as soon as possible, we are a volunteer-run organization and it may take several weeks for you to receive a reply.</p>
		 </div>

</div>
	</body>
</html>