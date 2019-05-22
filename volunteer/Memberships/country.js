function handleDocumentLoaded(e) {
	//list of countries
	var countries = ["", "Afghanistan", "Akrotiri", "Albania", "Algeria", "American Samoa", "Andorra", "Angola",
	"Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba","Ashmore and Cartier Islands",
	"Australia", "Austria", "Azerbaijan", "Bahamas, The", "Bahrain", "Bangladesh", "Barbados", "Bassas da India",
	"Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana",
	"Bouvet Island", "Brazil", "British Indian Ocean Territory", "British Virgin Islands", "Brunei", "Bulgaria",
	"Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands",
	"Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Clipperton Island",
	"Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo, Democratic Republic of the",
	"Congo, Republic of the", "Cook Islands", "Coral Sea Islands", "Costa Rica", "Cote d'Ivoire", "Croatia",
	"Cuba", "Cyprus", "Czech Republic", "Denmark", "Dhekelia", "Djibouti", "Dominica", "Dominican Republic",
	"Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Europa Island",
	"Falkland Islands (Islas Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana",
	"French Polynesia", "French Southern and Antarctic Lands", "Gabon", "Gambia, The", "Gaza Strip", "Georgia",
	"Germany", "Ghana", "Gibraltar", "Glorioso Islands", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam",
	"Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard Island and McDonald Islands",
	"Holy See (Vatican City)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran",
	"Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Jan Mayen", "Japan", "Jersey", "Jordan",
	"Juan de Nova Island", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait",
	"Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania",
	"Luxembourg", "Macau", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta",
	"Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico",
	"Micronesia, Federated States of", "Moldova", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique",
	"Namibia", "Nauru", "Navassa Island", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia",
	"New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands",
	"Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paracel Islands", "Paraguay", "Peru",
	"Philippines", "Pitcairn Islands", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania",
	"Russia", "Rwanda", "Saint Helena", "Saint Kitts and Nevis", "Saint Lucia", "Saint Pierre and Miquelon",
	"Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal",
	"Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands",
	"Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Spratly Islands",
	"Sri Lanka", "Sudan", "Suriname", "Svalbard", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan",
	"Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago",
	"Tromelin Island", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda",
	"Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu",
	"Venezuela", "Vietnam", "Virgin Islands", "Wake Island", "Wallis and Futuna", "West Bank", "Western Sahara",
	"Yemen", "Zambia", "Zimbabwe"];

	//list of Canadian provinces
	var provinces = ["", "AB", "BC", "MB", "NB", "NL", "NS", "NT", "NU", "ON", "PE", "QC", "SK", "YT"];
	
	//lst of American states
	var usStates = ["", "AL", "AK", "AZ", "AR", "CA", "CO", "CT", "DE", "FL", "GA", "HI", "ID", "IL", "IN",
	"IA", "KS", "KY", "LA", "ME", "MD", "MA", "MI", "MN", "MS", "MO", "MT", "NE", "NV", "NH", "NJ", "NM",
	"NY", "NC", "ND", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT", "VT", "VA", "WA", "WV",
	"WI", "WY", "AS", "DC", "FM", "GU", "MH", "MP", "PW", "PR", "VI"];
	
	//list of Australian states
	var ausStates = ["", "ACT", "JBT", "NSW", "NT", "QLD", "SA", "TAS", "VIC", "WA"];
	
	//list of countries in the UK
	var ukCountries = ["", "England", "Northern Ireland", "Scotland", "Wales"];

	var country = document.getElementById("nation");
	var provLabel = document.getElementById("provlabel");
	var prov = document.getElementById("prov");
	var codeLabel = document.getElementById("codelabel");
	var codeBox = document.getElementById("codebox");

	var province = document.createElement("select");
	var provBox = document.createElement("input");

	province.setAttribute("class", "searching");
	province.setAttribute("name", "province");
	province.setAttribute("id", "province");

	provBox.setAttribute("type", "text");
	provBox.setAttribute("class", "searching");
	provBox.setAttribute("name", "provBox");
	provBox.setAttribute("id", "provBox");
	provBox.setAttribute("placeholder", "Province/State/Territory");

	//add the list of countries to the country dropdown box
	for (var i=0; i<countries.length; i++) {
		var newOption = document.createElement("option");
		newOption.setAttribute("value", countries[i]);
		newOption.innerHTML = countries[i];
		country.appendChild(newOption);
	}	

	var changeHandler = function() {
		if (country.selectedIndex === 43){
			//delete any states or provinces states already in the list
			while (province.firstChild) {
			    province.removeChild(province.firstChild);
			}
			//add the correct provinces to the list
			for (var i=0; i<provinces.length; i++) {
				var newOption = document.createElement("option");
				newOption.setAttribute("value", provinces[i]);
				newOption.innerHTML = provinces[i];
				province.appendChild(newOption);
			}
			provLabel.innerHTML = "Province/Territory";
			codeLabel.innerHTML = "Postal Code";
			codeBox.setAttribute("placeholder", "Postal Code");
			provLabel.style.visibility = "visible";
			if (prov.hasChildNodes()) {
    			prov.removeChild(prov.childNodes[0]);
			}
			prov.appendChild(province);
			codeLabel.style.visibility = "visible";
			codeBox.style.visibility = "visible";

		} else if (country.selectedIndex === 244){
			//delete any states or provinces already in the list
			while (province.firstChild) {
			    province.removeChild(province.firstChild);
			}
			//add the correct states to the list
			for (var i=0; i<usStates.length; i++) {
				var newOption = document.createElement("option");
				newOption.setAttribute("value", usStates[i]);
				newOption.innerHTML = usStates[i];
				province.appendChild(newOption);
			}
			provLabel.innerHTML = "State/Territory";
			codeLabel.innerHTML = "Zip Code";
			codeBox.setAttribute("placeholder", "Zip Code");
			provLabel.style.visibility = "visible";
			if (prov.hasChildNodes()) {
    			prov.removeChild(prov.childNodes[0]);
			}
			prov.appendChild(province);
			codeLabel.style.visibility = "visible";
			codeBox.style.visibility = "visible";

		} else if (country.selectedIndex === 243){
			//delete any states or provinces states already in the list
			while (province.firstChild) {
			    province.removeChild(province.firstChild);
			}
			//add the correct countries to the list
			for (var i=0; i<ukCountries.length; i++) {
				var newOption = document.createElement("option");
				newOption.setAttribute("value", ukCountries[i]);
				newOption.innerHTML = ukCountries[i];
				province.appendChild(newOption);
			}
			provLabel.innerHTML = "Country";
			codeLabel.innerHTML = "Postcode";
			codeBox.setAttribute("placeholder", "Postcode");
			provLabel.style.visibility = "visible";
			if (prov.hasChildNodes()) {
    			prov.removeChild(prov.childNodes[0]);
			}
			prov.appendChild(province);
			codeLabel.style.visibility = "visible";
			codeBox.style.visibility = "visible";

		} else if (country.selectedIndex === 15){
			//delete any states or provinces states already in the list
			while (province.firstChild) {
			    province.removeChild(province.firstChild);
			}
			//add the correct states to the list
			for (var i=0; i<ausStates.length; i++) {
				var newOption = document.createElement("option");
				newOption.setAttribute("value", ausStates[i]);
				newOption.innerHTML = ausStates[i];
				province.appendChild(newOption);
			}
			provLabel.innerHTML = "State/Territory";
			codeLabel.innerHTML = "Postcode";
			codeBox.setAttribute("placeholder", "Postcode");
			provLabel.style.visibility = "visible";
			if (prov.hasChildNodes()) {
    			prov.removeChild(prov.childNodes[0]);
			}
			prov.appendChild(province);
			codeLabel.style.visibility = "visible";
			codeBox.style.visibility = "visible";

		} else {
			provLabel.innerHTML = "Province/State/Territory (if applicable)";
			codeLabel.innerHTML = "Postal Code (or equivalent, if applicable)";
			codeBox.setAttribute("placeholder", "Postal Code");
			provLabel.style.visibility = "visible";
			if (prov.hasChildNodes()) {
    			prov.removeChild(prov.childNodes[0]);
			}
			prov.appendChild(provBox);
			codeLabel.style.visibility = "visible";
			codeBox.style.visibility = "visible";
			
		}
	}
	//First try using addEventListener, the standard method to add a event listener:
	if(country.addEventListener)
	  country.addEventListener("change", changeHandler, false);
	//If it doesn't exist, try attachEvent, the IE way:
	else if(country.attachEvent)
	  country.attachEvent("onchange", changeHandler);
	//Just use onchange if neither exist
	else
	  country.onchange = changeHandler;
}
document.addEventListener("DOMContentLoaded", handleDocumentLoaded, false);

//Canada: 43
//US: 244
//UK: 243
//Australia: 15